<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');
requireLogin(); // Tylko zalogowani użytkownicy

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method Not Allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['current_password']) || !isset($data['new_password'])) {
    jsonResponse(['error' => 'Brak wymaganych danych.'], 400);
}

$userId = $_SESSION['user_id'];
$currentPassword = $data['current_password'];
$newPassword = $data['new_password'];

if (strlen($newPassword) < 6) {
    jsonResponse(['error' => 'Nowe hasło musi mieć co najmniej 6 znaków.'], 400);
}

// Pobranie obecnego hasła użytkownika
$stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    jsonResponse(['error' => 'Nie znaleziono użytkownika.'], 404);
}

if (!password_verify($currentPassword, $user['password'])) {
    jsonResponse(['error' => 'Obecne hasło jest nieprawidłowe.'], 400);
}

// Zapis nowego hasła
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
$updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");

if ($updateStmt->execute([$newHash, $userId])) {
    jsonResponse(['message' => 'Hasło zostało pomyślnie zmienione.']);
} else {
    jsonResponse(['error' => 'Wystąpił błąd podczas zmiany hasła.'], 500);
}
