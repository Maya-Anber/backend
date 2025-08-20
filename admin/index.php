<?php
require_once __DIR__ . '/../_inc/admin_auth.php';
$admin_id = require_admin();

// Load counts
$users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'] ?? 0;
$books = $conn->query("SELECT COUNT(*) AS c FROM books")->fetch_assoc()['c'] ?? 0;
$listings = $conn->query("SELECT COUNT(*) AS c FROM book_listings")->fetch_assoc()['c'] ?? 0;
$exchanges = $conn->query("SELECT COUNT(*) AS c FROM exchange_requests")->fetch_assoc()['c'] ?? 0;
$messages = $conn->query("SELECT COUNT(*) AS c FROM messages")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Admin Dashboard</title>
	<link rel="stylesheet" href="/backend/assets/theme.css">
</head>
<body>
	<?php include __DIR__ . '/_nav.php'; ?>
	<div class="wrap">
		<h1 class="page-title">Dashboard</h1>
		<div class="cards">
			<div class="card"><div>Total Users</div><strong><?= esc($users) ?></strong><a href="users.php">Manage users</a></div>
			<div class="card"><div>Total Books</div><strong><?= esc($books) ?></strong><a href="books.php">Manage books</a></div>
			<div class="card"><div>Total Listings</div><strong><?= esc($listings) ?></strong><a href="listings.php">Manage listings</a></div>
			<div class="card"><div>Total Exchanges</div><strong><?= esc($exchanges) ?></strong><a href="exchanges.php">Manage exchanges</a></div>
			<div class="card"><div>Total Messages</div><strong><?= esc($messages) ?></strong><a href="messages.php">Manage messages</a></div>
		</div>
	</div>
</body>
</html>

