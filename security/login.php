<?php
require_once __DIR__ . '/../_inc/db.php';

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    
    // Detect if the 'role' column exists to avoid SQL errors on older databases
    $hasRole = false;
    if ($result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'")) {
        $hasRole = $result->num_rows > 0;
        $result->free();
    }

    if ($hasRole) {
        $stmt = $conn->prepare("SELECT user_id, username, password_hash, role FROM users WHERE username = ?");
    } else {
        $stmt = $conn->prepare("SELECT user_id, username, password_hash FROM users WHERE username = ?");
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        if ($hasRole) {
            $stmt->bind_result($user_id, $username, $hashedPassword, $role);
        } else {
            $stmt->bind_result($user_id, $username, $hashedPassword);
            $role = 'user';
        }
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = $username;
            $_SESSION["role"] = $role;

            // update last_login
            $upd = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            if ($upd) { $upd->bind_param("i", $user_id); $upd->execute(); $upd->close(); }

            // Redirect by role
            if ($role === 'admin') {
                header("Location: /backend/admin/index.php");
            } else {
                header("Location: /backend/list/index.php");
            }
            exit();
        } else {
            echo "❌ Invalid password.";
        }
    } else {
        echo "❌ No user found with that username.";
    }
}
?>
