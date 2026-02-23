<?php
/**
 * Database Configuration
 * Business Listing & Rating System
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'nadsoft_business');
define('DB_USER', 'root');
define('DB_PASS', '');

function getConnection() {
    static $objPdo = null;
    if ($objPdo === null) {
        try {
            $strDsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $objPdo = new PDO($strDsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $objEx) {
            die('Database connection failed: ' . $objEx->getMessage());
        }
    }
    return $objPdo;
}
