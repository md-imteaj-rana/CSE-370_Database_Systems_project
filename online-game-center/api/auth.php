<?php
require_once __DIR__ . '/config.php';
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Register user
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'] ?? '';
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  
  if (!$email || !$username || strlen($password) < 6) respond(['error' => 'Invalid inputs'], 400);
  $hash = password_hash($password, PASSWORD_BCRYPT);
  
  $pdo->prepare("INSERT INTO users (email, username, password_hash) VALUES (?,?,?)")
      ->execute([$email, $username, $hash]);
  
  $uid = $pdo->lastInsertId();
  $pdo->prepare("INSERT INTO user_roles (user_id, role) VALUES (?, 'player')")->execute([$uid]);
  $pdo->prepare("INSERT INTO reward_wallets (user_id, points) VALUES (?, 0)")->execute([$uid]);
  
  $_SESSION['user_id'] = $uid;
  respond(['message' => 'Registered', 'user_id' => $uid]);
}

// Login user
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = $_POST['username'] ?? '';
  $pass = $_POST['password'] ?? '';
  
  $st = $pdo->prepare("SELECT id, password_hash FROM users WHERE username=? OR email=? LIMIT 1");
  $st->execute([$user, $user]);
  $row = $st->fetch();
  
  if (!$row || !password_verify($pass, $row['password_hash'])) respond(['error' => 'Invalid'], 401);
  
  $_SESSION['user_id'] = $row['id'];
  respond(['message' => 'Logged in', 'user_id' => $row['id']]);
}

// Logout user
if ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  session_destroy();
  respond(['message' => 'Logged out']);
}

// Get current user data
if ($action === 'me') {
  $uid = current_user_id();
  
  if (!$uid) respond(['user' => null]);
  
  $st = $pdo->prepare("SELECT u.id, u.username, u.email, GROUP_CONCAT(r.role) AS roles
                       FROM users u LEFT JOIN user_roles r ON u.id = r.user_id
                       WHERE u.id = ? GROUP BY u.id");
  $st->execute([$uid]);
  respond(['user' => $st->fetch()]);
}

// Change user password
if ($action === 'change-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login(); // Ensure user is logged in
  $uid = current_user_id(); // Get user ID
  
  // Get current, new and confirm passwords
  $current_password = $_POST['current_password'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  // Validate passwords
  if (!$current_password || !$new_password || !$confirm_password) {
    respond(['error' => 'All fields are required'], 400);
  }

  if ($new_password !== $confirm_password) {
    respond(['error' => 'New password and confirm password do not match'], 400);
  }

  // Fetch current password from the database
  $st = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
  $st->execute([$uid]);
  $user = $st->fetch();

  if (!$user || !password_verify($current_password, $user['password_hash'])) {
    respond(['error' => 'Current password is incorrect'], 400);
  }

  // Hash and update the password
  $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);
  $st = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
  $st->execute([$new_password_hashed, $uid]);

  respond(['message' => 'Password changed successfully']);
}

// Delete user account
if ($action === 'delete-account' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login(); // Ensure user is logged in
  $uid = current_user_id(); // Get user ID
  
  // Delete user account
  $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$uid]);
  
  // Optionally, delete associated data (e.g., bookings, tournaments)
  $pdo->prepare("DELETE FROM bookings WHERE user_id = ?")->execute([$uid]);
  $pdo->prepare("DELETE FROM tournament_registrations WHERE user_id = ?")->execute([$uid]);

  // Log out user after deletion
  session_destroy();
  respond(['message' => 'Account deleted successfully']);
}

respond(['error' => 'Not found'], 404);
