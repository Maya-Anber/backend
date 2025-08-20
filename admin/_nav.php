<?php
require_once __DIR__ . '/../_inc/admin_auth.php';
?>
<nav class="admin-nav">
	<div class="nav-inner">
		<span class="brand">Admin</span>
		<a href="/backend/admin/index.php">Dashboard</a>
		<a href="/backend/admin/users.php">Users</a>
		<a href="/backend/admin/books.php">Books</a>
		<a href="/backend/admin/listings.php">Listings</a>
		<a href="/backend/admin/exchanges.php">Exchanges</a>
		<a href="/backend/admin/messages.php">Messages</a>
		<div class="spacer"></div>
		<span class="admin-meta"><?= esc($_SESSION['username'] ?? 'admin') ?></span>
		<a class="btn btn-outline" href="/backend/security/logout.php" style="margin-left:8px">Logout</a>
	</div>
</nav>

