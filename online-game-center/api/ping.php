<?php
require_once __DIR__ . '/config.php';
try {
  $row = $pdo->query("SELECT 1 AS ok")->fetch();
  respond(['db'=>'connected','result'=>$row['ok']]);
} catch(Exception $e) {
  respond(['db'=>'error','detail'=>$e->getMessage()],500);
}
