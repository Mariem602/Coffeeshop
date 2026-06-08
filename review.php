<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $rating    = (int)$_POST['rating'];
    $comment   = trim($_POST['opinion']);

    $stmt = $pdo->prepare("INSERT INTO reviews (full_name, rating, comment) VALUES (?, ?, ?)");
    $stmt->execute([$full_name, $rating, $comment]);

    header("Location: homepage.html");
    exit;
}