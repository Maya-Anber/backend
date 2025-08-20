<?php
require_once __DIR__ . '/../_inc/admin_auth.php';
$admin_id = require_admin();

// Handle status update
if (isset($_POST['listing_id'], $_POST['status'])) {
		$listing_id = intval($_POST['listing_id']);
		$status = $_POST['status'];
		if (in_array($status, ['available','pending','exchanged'], true)) {
				$stmt = $conn->prepare("UPDATE book_listings SET availability_status=? WHERE listing_id=?");
				$stmt->bind_param('si', $status, $listing_id);
				$stmt->execute();
				$stmt->close();
		}
		header('Location: listings.php');
		exit;
}

$sql = "SELECT bl.listing_id, bl.condition_rating, bl.availability_status, bl.view_count,
							 u.username, b.title, b.author
				FROM book_listings bl
				JOIN users u ON u.user_id = bl.user_id
				JOIN books b ON b.book_id = bl.book_id
				ORDER BY bl.listed_date DESC";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Admin - Listings</title>
	<link rel="stylesheet" href="/backend/assets/theme.css">
</head>
<body>
	<?php include __DIR__ . '/_nav.php'; ?>
	<div class="wrap">
		<h1 class="page-title">Listings</h1>
		<table>
			<thead><tr><th>ID</th><th>User</th><th>Book</th><th>Author</th><th>Condition</th><th>Status</th><th>Views</th><th>Actions</th></tr></thead>
			<tbody>
				<?php while($row = $res->fetch_assoc()): ?>
					<tr>
						<td><?= esc($row['listing_id']) ?></td>
						<td><?= esc($row['username']) ?></td>
						<td><?= esc($row['title']) ?></td>
						<td><?= esc($row['author']) ?></td>
						<td><?= esc($row['condition_rating']) ?></td>
						<td><?= esc($row['availability_status']) ?></td>
						<td><?= esc($row['view_count']) ?></td>
						<td>
							<form method="post" style="display:inline">
								<input type="hidden" name="listing_id" value="<?= esc($row['listing_id']) ?>" />
								<select name="status">
									<option value="available">available</option>
									<option value="pending">pending</option>
									<option value="exchanged">exchanged</option>
								</select>
								<button class="btn">Update</button>
							</form>
						</td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	</div>
</body>
</html>

