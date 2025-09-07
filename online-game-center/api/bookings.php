<?php
require_once __DIR__ . '/config.php';
$action = $_GET['action'] ?? null;

function overlap_exists($pdo, $game_id, $start, $end){
  $st=$pdo->prepare("SELECT 1 FROM bookings
    WHERE game_id=? AND status='booked'
      AND NOT (end_time <= ? OR start_time >= ?)
    LIMIT 1");
  $st->execute([$game_id, $start, $end]);
  return (bool)$st->fetch();
}

if ($action==='create' && $_SERVER['REQUEST_METHOD']==='POST') {
  require_login(); $uid=current_user_id();
  $gid=(int)($_POST['game_id']??0); $start=$_POST['start_time']??''; $end=$_POST['end_time']??'';
  if(!$gid || !$start || !$end) respond(['error'=>'game_id, start_time, end_time required'],400);
  if (overlap_exists($pdo,$gid,$start,$end)) respond(['error'=>'Slot overlaps with existing booking'],409);
  $pdo->prepare("INSERT INTO bookings (user_id, game_id, start_time, end_time) VALUES (?,?,?,?)")
      ->execute([$uid,$gid,$start,$end]);
  respond(['message'=>'Booking created']);
}

if ($action==='cancel' && $_SERVER['REQUEST_METHOD']==='POST') {
  require_login(); $uid=current_user_id();
  $id=(int)($_POST['booking_id']??0);

  // Ensure booking exists and is owned by the current user
  $st = $pdo->prepare("SELECT status FROM bookings WHERE id = ? AND user_id = ?");
  $st->execute([$id, $uid]);
  $booking = $st->fetch();

  // If booking is already cancelled, return an error
  if ($booking && $booking['status'] === 'cancelled') {
    respond(['error' => 'Booking is already cancelled'], 400);
  }

  // Update booking status to 'cancelled'
  $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=?")
      ->execute([$id, $uid]);

  respond(['message' => 'Booking cancelled']);
}

if ($action==='my') {
  require_login(); $uid=current_user_id();
  $st=$pdo->prepare("SELECT b.id, g.title AS game, b.start_time, b.end_time, b.status
                     FROM bookings b JOIN games g ON g.id=b.game_id
                     WHERE b.user_id=? ORDER BY b.start_time DESC");
  $st->execute([$uid]);
  respond(['bookings'=>$st->fetchAll()]);
}

respond(['error'=>'Not found'],404);
