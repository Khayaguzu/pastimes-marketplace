<?php
/**
 * Database Connection File
 * Filename: DBConn.php
 * Purpose: Establishes connection to MySQL database
 * All other files include this to access the database
 */

// ============================================
// DATABASE CONFIGURATION
// ============================================

$servername = getenv('PASTIMES_DB_HOST') ?: 'localhost';
$username = getenv('PASTIMES_DB_USER') ?: 'root';
$password = getenv('PASTIMES_DB_PASSWORD') ?: '';
$database = getenv('PASTIMES_DB_NAME') ?: 'clothingstore';

// ============================================
// CREATE CONNECTION
// ============================================

// mysqli_connect() returns a connection object or false if failed
$conn = mysqli_connect($servername, $username, $password, $database);

// ============================================
// CHECK CONNECTION
// ============================================

// If connection failed, stop execution and show error
if (!$conn) {
    error_log('Pastimes database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    exit('The application could not connect to the database.');
}

// ============================================
// SET CHARACTER ENCODING
// ============================================

// Use UTF-8 to support special characters and emojis
mysqli_set_charset($conn, "utf8mb4");

// NOTE: Do not add any output (echo/print) here as it may break 
//       header redirects in other files that include this
?>
