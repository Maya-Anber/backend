<!-- <?php
// backend/_inc/db.php
require_once __DIR__ . '/config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME,3309);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}
if ($conn->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$conn->set_charset('utf8mb4');
?> -->
