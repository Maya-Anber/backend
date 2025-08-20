<?php
// Adds missing columns and creates/promotes an admin user.
require_once __DIR__ . '/../_inc/db.php';
header('Content-Type: text/plain');

function columnExists(mysqli $conn, string $table, string $column): bool {
	$res = $conn->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
	$exists = $res && $res->num_rows > 0;
	if ($res) { $res->free(); }
	return $exists;
}

try {
	// Ensure essential columns exist on users table
	if (!columnExists($conn, 'users', 'role')) {
		if (!$conn->query("ALTER TABLE users ADD COLUMN role ENUM('user','admin') NOT NULL DEFAULT 'user'")) {
			throw new Exception('Failed to add role column: ' . $conn->error);
		}
		echo "Added column 'role'.\n";
	} else { echo "Column 'role' exists.\n"; }

	if (!columnExists($conn, 'users', 'full_name')) {
		if (!$conn->query("ALTER TABLE users ADD COLUMN full_name VARCHAR(100) NOT NULL DEFAULT ''")) {
			throw new Exception('Failed to add full_name column: ' . $conn->error);
		}
		echo "Added column 'full_name'.\n";
	} else { echo "Column 'full_name' exists.\n"; }

	if (!columnExists($conn, 'users', 'privacy_contact_info')) {
		if (!$conn->query("ALTER TABLE users ADD COLUMN privacy_contact_info ENUM('public','private') DEFAULT 'private'")) {
			throw new Exception('Failed to add privacy_contact_info: ' . $conn->error);
		}
		echo "Added column 'privacy_contact_info'.\n";
	} else { echo "Column 'privacy_contact_info' exists.\n"; }

	if (!columnExists($conn, 'users', 'email_verified')) {
		if (!$conn->query("ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE")) {
			throw new Exception('Failed to add email_verified: ' . $conn->error);
		}
		echo "Added column 'email_verified'.\n";
	} else { echo "Column 'email_verified' exists.\n"; }

	if (!columnExists($conn, 'users', 'notification_preferences')) {
		if (!$conn->query("ALTER TABLE users ADD COLUMN notification_preferences JSON NULL")) {
			throw new Exception('Failed to add notification_preferences: ' . $conn->error);
		}
		echo "Added column 'notification_preferences'.\n";
	} else { echo "Column 'notification_preferences' exists.\n"; }

	// Ensure an admin exists (username 'admin')
	$preferred = 'admin';
	$q = $conn->prepare("SELECT user_id FROM users WHERE username=?");
	$q->bind_param('s', $preferred); $q->execute(); $q->store_result();
	if ($q->num_rows > 0) {
		$q->bind_result($uid); $q->fetch(); $q->close();
		// Promote to admin role
		$upd = $conn->prepare("UPDATE users SET role='admin' WHERE user_id=?");
		$upd->bind_param('i', $uid); $upd->execute(); $upd->close();
		echo "Promoted existing 'admin' user to role=admin.\n";
	} else {
		$q->close();
		$password = 'password';
		$hash = password_hash($password, PASSWORD_BCRYPT);
		$full_name = 'Site Administrator';
		$email = 'admin@example.com';
		$pref = json_encode(['email'=>true,'in_app'=>true]);
		$ins = $conn->prepare(
			"INSERT INTO users (username,email,password_hash,full_name,role,privacy_contact_info,email_verified,notification_preferences)
			 VALUES (?,?,?,?, 'admin','private', TRUE, ?)"
		);
		$ins->bind_param('sssss', $preferred, $email, $hash, $full_name, $pref);
		$ins->execute(); $ins->close();
		echo "Created admin user 'admin' with default password 'password'.\n";
	}

	echo "Done.\n";
} catch (Throwable $e) {
	http_response_code(500);
	echo "Migration failed: ".$e->getMessage();
}
?>

