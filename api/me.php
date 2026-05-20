<?php
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    jsonResponse(['user' => null]);
}

$stmt = $db->prepare("SELECT id, name, email, assigned_spot, is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

jsonResponse(['user' => $user ?: null]);
