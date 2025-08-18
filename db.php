<?php
$servername = "localhost";
$username = "root";   // change if needed
$password = "";       // your MySQL password
$dbname = "";  // db name

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME,3309);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST["username"];
    $email = $_POST["email"];
    $pass = password_hash($_POST["password"], PASSWORD_BCRYPT); // secure hashing

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user, $email, $pass);

    if ($stmt->execute()) {
        echo "Registration successful!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>