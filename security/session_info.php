<?php
// Lightweight session info for client-side nav rendering
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
$logged_in = isset($_SESSION['user_id']);
echo json_encode([
  'success' => true,
  'logged_in' => $logged_in,
  'user_id' => $logged_in ? intval($_SESSION['user_id']) : null,
  'username' => $logged_in ? ($_SESSION['username'] ?? null) : null
]);
