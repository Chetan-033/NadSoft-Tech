<?php
/**
 * Business CRUD API
 * Handles: list, create, update, delete
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$objPdo = getConnection();
$strMethod = $_SERVER['REQUEST_METHOD'];

// Get action from request
$strAction = $_GET['action'] ?? $_POST['action'] ?? '';

// Parse JSON body for PUT
$arrInput = [];
if ($strMethod === 'PUT' || $strMethod === 'POST') {
    $strContentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($strContentType, 'application/json') !== false) {
        $arrInput = json_decode(file_get_contents('php://input'), true) ?? [];
    } else {
        $arrInput = $_POST;
    }
}

function sendJsonResponse($boolSuccess, $mixedData = null, $strMessage = '') {
    echo json_encode([
        'success' => $boolSuccess,
        'data' => $mixedData,
        'message' => $strMessage
    ]);
    exit;
}

function fetchBusinessesWithAvgRating($objPdo) {
    $objStmt = $objPdo->query("
        SELECT b.*, 
               COALESCE(ROUND(AVG(r.rating), 1), 0) as avg_rating,
               COUNT(r.id) as rating_count
        FROM businesses b
        LEFT JOIN ratings r ON b.id = r.business_id
        GROUP BY b.id
        ORDER BY b.id
    ");
    return $objStmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
    switch ($strAction) {
        case 'list':
        case '':
            $arrBusinesses = fetchBusinessesWithAvgRating($objPdo);
            sendJsonResponse(true, $arrBusinesses);
            break;

        case 'create':
            $strName = trim($arrInput['name'] ?? '');
            $strAddress = trim($arrInput['address'] ?? '');
            $strPhone = trim($arrInput['phone'] ?? '');
            $strEmail = trim($arrInput['email'] ?? '');

            if (empty($strName)) {
                sendJsonResponse(false, null, 'Business name is required');
            }

            $objStmt = $objPdo->prepare("INSERT INTO businesses (name, address, phone, email) VALUES (?, ?, ?, ?)");
            $objStmt->execute([$strName, $strAddress, $strPhone, $strEmail]);
            $intId = (int)$objPdo->lastInsertId();

            $arrBusinesses = fetchBusinessesWithAvgRating($objPdo);
            $arrNewBusiness = array_filter($arrBusinesses, fn($objB) => (int)$objB['id'] === $intId);
            sendJsonResponse(true, reset($arrNewBusiness) ?: ['id' => $intId, 'name' => $strName, 'address' => $strAddress, 'phone' => $strPhone, 'email' => $strEmail, 'avg_rating' => 0, 'rating_count' => 0], 'Business added successfully');
            break;

        case 'update':
            $intId = (int)($arrInput['id'] ?? $_GET['id'] ?? 0);
            $strName = trim($arrInput['name'] ?? '');
            $strAddress = trim($arrInput['address'] ?? '');
            $strPhone = trim($arrInput['phone'] ?? '');
            $strEmail = trim($arrInput['email'] ?? '');

            if ($intId <= 0 || empty($strName)) {
                sendJsonResponse(false, null, 'Valid ID and business name required');
            }

            $objStmt = $objPdo->prepare("UPDATE businesses SET name=?, address=?, phone=?, email=? WHERE id=?");
            $objStmt->execute([$strName, $strAddress, $strPhone, $strEmail, $intId]);

            $arrBusinesses = fetchBusinessesWithAvgRating($objPdo);
            $arrUpdated = array_filter($arrBusinesses, fn($objB) => (int)$objB['id'] === $intId);
            sendJsonResponse(true, reset($arrUpdated), 'Business updated successfully');
            break;

        case 'delete':
            $intId = (int)($_GET['id'] ?? $arrInput['id'] ?? 0);
            if ($intId <= 0) {
                sendJsonResponse(false, null, 'Valid ID required');
            }
            $objStmt = $objPdo->prepare("DELETE FROM businesses WHERE id=?");
            $objStmt->execute([$intId]);
            sendJsonResponse(true, ['id' => $intId], 'Business deleted successfully');
            break;

        case 'get':
            $intId = (int)($_GET['id'] ?? 0);
            if ($intId <= 0) {
                sendJsonResponse(false, null, 'Valid ID required');
            }
            $objStmt = $objPdo->prepare("SELECT * FROM businesses WHERE id = ?");
            $objStmt->execute([$intId]);
            $arrBusiness = $objStmt->fetch(PDO::FETCH_ASSOC);
            if (!$arrBusiness) {
                sendJsonResponse(false, null, 'Business not found');
            }
            sendJsonResponse(true, $arrBusiness);
            break;

        default:
            sendJsonResponse(false, null, 'Invalid action');
    }
} catch (PDOException $objEx) {
    sendJsonResponse(false, null, 'Database error: ' . $objEx->getMessage());
}
