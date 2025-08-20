<?php
require_once __DIR__ . '/../_inc/admin_auth.php';
$admin_id = require_admin();

// Handle actions
if (isset($_POST['action'], $_POST['user_id'])) {
		$uid = intval($_POST['user_id']);
		if ($_POST['action'] === 'toggle_active') {
				$conn->query("UPDATE users SET is_active = NOT is_active WHERE user_id = " . $uid);
		} elseif ($_POST['action'] === 'role_user') {
				$stmt = $conn->prepare("UPDATE users SET role='user' WHERE user_id = ? AND user_id <> ?");
				$stmt->bind_param('ii', $uid, $admin_id); $stmt->execute(); $stmt->close();
		} elseif ($_POST['action'] === 'role_admin') {
				$stmt = $conn->prepare("UPDATE users SET role='admin' WHERE user_id = ?");
				$stmt->bind_param('i', $uid); $stmt->execute(); $stmt->close();
		}
		header('Location: users.php');
		exit;
}

$res = $conn->query("SELECT user_id, username, email, full_name, role, is_active, registration_date, last_login FROM users ORDER BY registration_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Admin - Users</title>
	<link rel="stylesheet" href="/backend/assets/theme.css">
</head>
<body>
	<?php include __DIR__ . '/_nav.php'; ?>
	<div class="wrap">
		<h1 class="page-title">Users</h1>
		<table>
			<thead>
				<tr>
					<th>ID</th><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Registered</th><th>Last login</th><th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php while($row = $res->fetch_assoc()): ?>
					<tr>
						<td><?= esc($row['user_id']) ?></td>
						<td><?= esc($row['username']) ?></td>
						<td><?= esc($row['full_name']) ?></td>
						<td><?= esc($row['email']) ?></td>
						<td>
							<span class="badge <?= $row['role']==='admin'?'role-admin':'role-user' ?>"><?= esc($row['role']) ?></span>
						</td>
						<td>
							<span class="badge <?= $row['is_active']?'active':'inactive' ?>"><?= $row['is_active']?'active':'inactive' ?></span>
						</td>
						<td><?= esc($row['registration_date']) ?></td>
						<td><?= esc($row['last_login'] ?: '-') ?></td>
						<td>
							<form method="post" style="display:inline"><input type="hidden" name="user_id" value="<?= esc($row['user_id']) ?>" />
								<button class="btn btn-outline" name="action" value="toggle_active">Toggle Active</button>
							</form>
							<?php if ((int)$row['user_id'] !== (int)$admin_id): ?>
							<form method="post" style="display:inline"><input type="hidden" name="user_id" value="<?= esc($row['user_id']) ?>" />
								<button class="btn" name="action" value="role_admin">Make Admin</button>
							</form>
							<form method="post" style="display:inline"><input type="hidden" name="user_id" value="<?= esc($row['user_id']) ?>" />
								<button class="btn btn-outline" name="action" value="role_user">Make User</button>
							</form>
							<?php endif; ?>
						</td>
					</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
	</div>
</body>
</html>

