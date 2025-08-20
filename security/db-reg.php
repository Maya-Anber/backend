<?php
require_once __DIR__ . '/../_inc/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username   = $_POST["username"];
    $email      = $_POST["email"];
    $password   = $_POST["password"];
    $fname = $_POST["fname"];
    $lname = $_POST["lname"];
    
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare(
        "INSERT INTO users (first_name,last_name, username, email, password_hash, registration_date, is_active, privacy_contact_info, email_verified) 
         VALUES (?,?, ?, ?, ?, NOW(), 1, 'public', 1)"
    );
    $stmt->bind_param("sssss", $fname,$lname, $username, $email, $hashed);

    if ($stmt->execute()) {
        $_SESSION['registration_success'] = true;
        header("Location: login.html"); // Redirect to login.html
        exit();
    } else {
        echo "❌ Error: " . $stmt->error;
    }
}
?>

