<?php
require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? null;

if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login();
  $uid   = current_user_id();
  $gid   = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
  $score = isset($_POST['score']) ? (int)$_POST['score'] : 0;

  if (!$gid) respond(['error' => 'game_id required'], 400);

  // Points: simple rule = score/10 (floor)
  $pts = (int) floor($score / 10);

  // Record reward history
  $pdo->prepare(
    "INSERT INTO reward_history (user_id, points, reason) VALUES (?, ?, 'score')"
  )->execute([$uid, $pts]);

  // Upsert wallet balance  ✅ note the 3 parameters for VALUES and UPDATE
  $pdo->prepare(
    "INSERT INTO reward_wallets (user_id, points) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE points = points + ?"
  )->execute([$uid, $pts, $pts]);

  // ✅ NEW: log a play so Profile → Match History shows title + datetime
  $pdo->prepare(
    "INSERT INTO play_history (user_id, game_id) VALUES (?, ?)"
  )->execute([$uid, $gid]);

  respond(['message' => 'Score submitted', 'points' => $pts]);
}

respond(['error' => 'Not found'], 404);
