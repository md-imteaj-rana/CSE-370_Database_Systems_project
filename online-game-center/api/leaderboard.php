<?php
require_once __DIR__ . '/config.php';
$action=$_GET['action']??null;

if ($action==='rewards') {
  $st=$pdo->query("SELECT u.username, rw.points FROM reward_wallets rw JOIN users u ON u.id=rw.user_id ORDER BY points DESC");
  respond(['leaderboard'=>$st->fetchAll()]);
}

respond(['error'=>'Not found'],404);
