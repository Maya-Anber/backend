<?php
// backend/_inc/admin_auth.php
// Middleware for admin-only pages.
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

require_once __DIR__ . '/db.php';

function require_admin() {
	if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
		// If not logged or not admin, send to login page
		header('Location: /backend/security/login.html');
		exit;
	}
	return intval($_SESSION['user_id']);
}

function esc($v) {
	return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>

