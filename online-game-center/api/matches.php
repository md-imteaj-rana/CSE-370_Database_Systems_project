<?php
require_once __DIR__ . '/config.php';
$action = $_GET['action'] ?? null;

if ($action==='history') {
  require_login(); $uid = current_user_id();

  // Recent plays (mini-games and casual plays)
  $st = $pdo->prepare("
    SELECT ph.played_at AS played_at, g.title AS game, 'play' AS kind
    FROM play_history ph
    JOIN games g ON g.id = ph.game_id
    WHERE ph.user_id = ?
    ORDER BY ph.played_at DESC
    LIMIT 100
  ");
  $st->execute([$uid]);
  $plays = $st->fetchAll();

  // Optional: include real 1v1 tournament matches if you log them in `matches`
  // (kept simple: only include completed)
  $st2 = $pdo->prepare("
    SELECT m.id, m.status, m.winner_id, m.game_id, g.title AS game, m.id as match_id
    FROM matches m
    JOIN games g ON g.id = m.game_id
    WHERE (m.player1_id=? OR m.player2_id=?) AND m.status='completed'
    ORDER BY m.id DESC
    LIMIT 100
  ");
  $st2->execute([$uid,$uid]);
  $m = $st2->fetchAll();

  respond(['plays'=>$plays, 'matches'=>$m]);
}

respond(['error'=>'Not found'],404);
