<?php
require_once __DIR__ . '/../_inc/admin_auth.php';
$admin_id = require_admin();
$res = $conn->query("SELECT book_id, title, author, isbn, genre, publication_year FROM books ORDER BY book_id DESC");
?>
	<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Admin - Books</title>
	<link rel="stylesheet" href="/backend/assets/theme.css">
	</head>
	<body>
		<?php include __DIR__ . '/_nav.php'; ?>
		<div class="wrap">
			<h1 class="page-title">Books</h1>
			<table>
				<thead><tr><th>ID</th><th>Title</th><th>Author</th><th>ISBN</th><th>Genre</th><th>Year</th></tr></thead>
				<tbody>
					<?php while($row = $res->fetch_assoc()): ?>
						<tr>
							<td><?= esc($row['book_id']) ?></td>
							<td><?= esc($row['title']) ?></td>
							<td><?= esc($row['author']) ?></td>
							<td><?= esc($row['isbn']) ?></td>
							<td><?= esc($row['genre']) ?></td>
							<td><?= esc($row['publication_year']) ?></td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
		</div>
	</body>
	</html>

