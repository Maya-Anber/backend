<?php
require_once __DIR__ . '/../_inc/admin_auth.php';
$admin_id = require_admin();

$sql = "SELECT m.message_id, s.username AS sender, r.username AS receiver, m.subject, m.sent_date, m.is_read
				FROM messages m
				JOIN users s ON s.user_id = m.sender_id
				JOIN users r ON r.user_id = m.receiver_id
				ORDER BY m.sent_date DESC LIMIT 200";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Admin - Messages</title>
	<link rel="stylesheet" href="/backend/assets/theme.css">
</head>
<body>
	<?php include __DIR__ . '/_nav.php'; ?>
	<div class="wrap">
		<h1 class="page-title">Messages</h1>
		<table>
			<thead><tr><th>ID</th><th>Sender</th><th>Receiver</th><th>Subject</th><th>Date</th><th>Read</th></tr></thead>
			<tbody>
				<?php while($row = $res->fetch_assoc()): ?>
					<tr>
						<td><?= esc($row['message_id']) ?></td>
						<td><?= esc($row['sender']) ?></td>
						<td><?= esc($row['receiver']) ?></td>
						<td><?= esc($row['subject']) ?></td>
						<td><?= esc($row['sent_date']) ?></td>
						<td><?= $row['is_read'] ? 'Yes' : 'No' ?></td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	</div>
</body>
</html>

