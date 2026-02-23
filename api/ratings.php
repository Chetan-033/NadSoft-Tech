<?php
/**
 * Rating API
 * list: Fetch ratings for a business
 * submit: Submit rating (update if email/phone exists, else insert)
 */

// Purpose: Set appropriate HTTP headers for JSON response and allow CORS for API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$objPdo = getConnection();
$strMethod = $_SERVER['REQUEST_METHOD'];

function sendJsonResponse($boolSuccess, $mixedData = null, $strMessage = '') {
    echo json_encode([
        'success' => $boolSuccess,
        'data' => $mixedData,
        'message' => $strMessage
    ]);
    exit;
}

// GET: list ratings for a business
if ($strMethod === 'GET') {
    $intBusinessId = (int)($_GET['business_id'] ?? 0);
    if ($intBusinessId <= 0) {
        sendJsonResponse(false, null, 'Valid business_id required');
    }
    $objStmt = $objPdo->prepare("SELECT id, name, email, phone, rating, created_at FROM ratings WHERE business_id = ? ORDER BY created_at DESC");
    $objStmt->execute([$intBusinessId]);
    $arrRatings = $objStmt->fetchAll(PDO::FETCH_ASSOC);
    sendJsonResponse(true, $arrRatings);
}

// POST: submit rating
if ($strMethod !== 'POST') {
    http_response_code(405);
    sendJsonResponse(false, null, 'Method not allowed');
}

$strContentType = $_SERVER['CONTENT_TYPE'] ?? '';
$arrInput = strpos($strContentType, 'application/json') !== false
    ? (json_decode(file_get_contents('php://input'), true) ?? [])
    : $_POST;

$intBusinessId = (int)($arrInput['business_id'] ?? 0);
$strName = trim($arrInput['name'] ?? '');
$strEmail = trim($arrInput['email'] ?? '');
$strPhone = trim($arrInput['phone'] ?? '');
$floatRating = isset($arrInput['rating']) ? (float)$arrInput['rating'] : null;

// Validation
if ($intBusinessId <= 0 || empty($strName) || empty($strEmail) || $floatRating === null) {
    sendJsonResponse(false, null, 'business_id, name, email and rating are required');
}

if ($floatRating < 0 || $floatRating > 5) {
    sendJsonResponse(false, null, 'Rating must be between 0 and 5');
}

try {
    // Check if rating exists by email OR phone for this business
    $arrExisting = null;
    if (!empty($strEmail) || !empty($strPhone)) {
        $arrConditions = [];
        $arrParams = [$intBusinessId];
        if (!empty($strEmail)) {
            $arrConditions[] = 'email = ?';
            $arrParams[] = $strEmail;
        }
        if (!empty($strPhone)) {
            $arrConditions[] = 'phone = ?';
            $arrParams[] = $strPhone;
        }
        $strSql = "SELECT id FROM ratings WHERE business_id = ? AND (" . implode(' OR ', $arrConditions) . ") LIMIT 1";
        $objStmt = $objPdo->prepare($strSql);
        $objStmt->execute($arrParams);
        $arrExisting = $objStmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($arrExisting) {
        // Rule 1: Update existing rating
        $objStmt = $objPdo->prepare("UPDATE ratings SET name=?, email=?, phone=?, rating=? WHERE id=?");
        $objStmt->execute([$strName, $strEmail, $strPhone, $floatRating, $arrExisting['id']]);
        $strMessage = 'Rating updated successfully';
    } else {
        // Rule 2: Insert new rating
        $objStmt = $objPdo->prepare("INSERT INTO ratings (business_id, name, email, phone, rating) VALUES (?, ?, ?, ?, ?)");
        $objStmt->execute([$intBusinessId, $strName, $strEmail, $strPhone, $floatRating]);
        $strMessage = 'Rating submitted successfully';
    }

    // Recalculate average rating for the business
    $objStmt = $objPdo->prepare("SELECT COALESCE(ROUND(AVG(rating), 1), 0) as avg_rating FROM ratings WHERE business_id = ?");
    $objStmt->execute([$intBusinessId]);
    $arrRow = $objStmt->fetch(PDO::FETCH_ASSOC);
    $floatAvgRating = (float)($arrRow['avg_rating'] ?? 0);

    sendJsonResponse(true, [
        'business_id' => $intBusinessId,
        'avg_rating' => $floatAvgRating
    ], $strMessage);

} catch (PDOException $objEx) {
    sendJsonResponse(false, null, 'Database error: ' . $objEx->getMessage());
}
