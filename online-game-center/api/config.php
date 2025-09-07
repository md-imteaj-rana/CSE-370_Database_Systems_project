<?php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'ogc';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP default

try {
  $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER, $DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
  http_response_code(500);
  header('Content-Type: application/json');
  echo json_encode(['error' => 'DB connection failed', 'detail' => $e->getMessage()]);
  exit;
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function respond($data, $status=200) {
  http_response_code($status);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}
function current_user_id() { return $_SESSION['user_id'] ?? null; }
function require_login() { if (!current_user_id()) respond(['error'=>'Login required'],401); }
