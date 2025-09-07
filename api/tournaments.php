<?php
require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? null;

// Fetch all tournaments
if ($action === 'list') {
  $st = $pdo->query("SELECT t.*, g.title AS game FROM tournaments t JOIN games g ON g.id = t.game_id ORDER BY starts_at");
  respond(['tournaments' => $st->fetchAll()]);
}

// Register for a tournament
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  require_login(); 
  $uid = current_user_id();
  $tid = $_POST['tournament_id'] ?? 0;  // Tournament ID from POST request

  if (!$tid) {
    respond(['error' => 'Tournament ID required'], 400); // Ensure the tournament ID is present
  }

  // Check if user is already registered for this tournament
  $st = $pdo->prepare("SELECT 1 FROM tournament_registrations WHERE user_id = ? AND tournament_id = ?");
  $st->execute([$uid, $tid]);
  if ($st->fetch()) {
    respond(['error' => 'Already registered for this tournament'], 400);
  }

  // Register user for tournament
  $pdo->prepare("INSERT INTO tournament_registrations (tournament_id, user_id) VALUES (?, ?)")
      ->execute([$tid, $uid]);

  respond(['message' => 'Registered for tournament successfully']);
}

// Fetch tournaments the user is registered for
if ($action === 'registered') {
  require_login(); 
  $uid = current_user_id();

  // Fetch tournaments user is registered for
  $st = $pdo->prepare("
    SELECT t.id, t.name, t.starts_at, t.ends_at 
    FROM tournament_registrations tr
    JOIN tournaments t ON t.id = tr.tournament_id
    WHERE tr.user_id = ?
    ORDER BY t.starts_at DESC
  ");
  $st->execute([$uid]);
  $tournaments = $st->fetchAll();

  respond(['tournaments' => $tournaments]);
}

respond(['error' => 'Not found'], 404);
