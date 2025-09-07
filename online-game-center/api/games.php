<?php
require_once __DIR__ . '/config.php';
$action = $_GET['action'] ?? null;

if ($action==='list') {
  $st=$pdo->query("SELECT * FROM games ORDER BY title");
  respond(['games'=>$st->fetchAll()]);
}

if ($action==='library_add' && $_SERVER['REQUEST_METHOD']==='POST') {
  require_login(); $uid=current_user_id(); $gid=$_POST['game_id']??0;
  $pdo->prepare("INSERT INTO libraries (user_id, game_id) VALUES (?,?)
                 ON DUPLICATE KEY UPDATE game_id=VALUES(game_id)")->execute([$uid,$gid]);
  respond(['message'=>'Added']);
}

if ($action==='library_remove' && $_SERVER['REQUEST_METHOD']==='POST') {
  require_login(); $uid=current_user_id(); $gid=$_POST['game_id']??0;
  $pdo->prepare("DELETE FROM libraries WHERE user_id=? AND game_id=?")->execute([$uid,$gid]);
  respond(['message'=>'Removed']);
}

if ($action==='my_library') {
  require_login(); $uid=current_user_id();
  $st=$pdo->prepare("SELECT g.* FROM libraries l JOIN games g ON g.id=l.game_id WHERE l.user_id=?");
  $st->execute([$uid]); respond(['library'=>$st->fetchAll()]);
}

if ($action==='can_play') {
  require_login(); $uid = current_user_id(); $gid = (int)($_GET['game_id'] ?? 0);
  $st = $pdo->prepare("SELECT 1 FROM libraries WHERE user_id=? AND game_id=? LIMIT 1");
  $st->execute([$uid,$gid]);
  respond(['allowed' => (bool)$st->fetch()]);
}

if ($action==='genres') {
  $st = $pdo->query("SELECT DISTINCT genre FROM games WHERE genre IS NOT NULL AND genre<>'' ORDER BY genre");
  respond(['genres' => array_column($st->fetchAll(), 'genre')]);
}


respond(['error'=>'Not found'],404);
