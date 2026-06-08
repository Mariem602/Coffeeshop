<?php
require 'db.php';

header('Content-Type: application/json');

$stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($reviews);
