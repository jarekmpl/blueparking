<?php
require_once __DIR__ . '/db.php';

if (isset($_SESSION['user_id'])) {
    $db->prepare("UPDATE users SET remember_token = NULL WHERE id = ?")->execute([$_SESSION['user_id']]);
}

setcookie('remember_token', '', [
    'expires' => time() - 3600,
    'path' => '/',
    'httponly' => true,
    'secure' => true,
    'samesite' => 'Strict'
]);

session_destroy();
jsonResponse(['success' => true]);
