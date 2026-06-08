<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id    = $_SESSION['user_id'] ?? null;
    $item_name  = htmlspecialchars($_POST['item_name']);
    $item_price = (float)$_POST['item_price'];
    $card_name  = htmlspecialchars($_POST['card_name']);

    $stmt = $pdo->prepare(
        "INSERT INTO orders (user_id, item_name, total, card_name, status)
         VALUES (?, ?, ?, ?, 'success')"
    );
    $stmt->execute([$user_id, $item_name, $item_price, $card_name]);

    header("Location: success.html");
    exit;
}