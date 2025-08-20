<?php
require_once __DIR__ . '/../_inc/admin_auth.php';
$admin_id = require_admin();

if (isset($_POST['request_id'], $_POST['status'])) {
		$request_id = intval($_POST['request_id']);
		$status = $_POST['status'];
		if (in_array($status, ['pending','approved','rejected','completed','cancelled'], true)) {
				$stmt = $conn->prepare("UPDATE exchange_requests SET status=?, response_date = NOW() WHERE request_id=?");
				$stmt->bind_param('si', $status, $request_id);
				$stmt->execute();
				$stmt->close();
		}
		header('Location: exchanges.php');
		exit;
}

$sql = "SELECT er.request_id, er.status, er.request_date, u1.username AS requester, u2.username AS owner,
							 er.requested_listing_id, er.offered_listing_id
				FROM exchange_requests er
				JOIN users u1 ON er.requester_id = u1.user_id
				JOIN users u2 ON er.owner_id = u2.user_id
				ORDER BY er.request_date DESC";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Admin - Exchanges</title>
	<link rel="stylesheet" href="/backend/assets/theme.css">
</head>
<body>
	<?php include __DIR__ . '/_nav.php'; ?>
	<div class="wrap">
		<h1 class="page-title">Exchanges</h1>
		<table>
			<thead><tr><th>ID</th><th>Requester</th><th>Owner</th><th>Requested Listing</th><th>Offered Listing</th><th>Status</th><th>Requested</th><th>Actions</th></tr></thead>
			<tbody>
				<?php while($row = $res->fetch_assoc()): ?>
					<tr>
						<td><?= esc($row['request_id']) ?></td>
						<td><?= esc($row['requester']) ?></td>
						<td><?= esc($row['owner']) ?></td>
						<td><?= esc($row['requested_listing_id']) ?></td>
						<td><?= esc($row['offered_listing_id'] ?? '-') ?></td>
						<td><?= esc($row['status']) ?></td>
						<td><?= esc($row['request_date']) ?></td>
						<td>
							<form method="post" style="display:inline">
								<input type="hidden" name="request_id" value="<?= esc($row['request_id']) ?>" />
								<select name="status">
									<option value="pending">pending</option>
									<option value="approved">approved</option>
									<option value="rejected">rejected</option>
									<option value="completed">completed</option>
									<option value="cancelled">cancelled</option>
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

