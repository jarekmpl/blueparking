<?php
require_once __DIR__ . '/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method Not Allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$date = $data['date'] ?? '';

if (empty($date)) {
    jsonResponse(['error' => 'Date is required'], 400);
}

// Ensure the user actually has a spot
$stmt = $db->prepare("SELECT assigned_spot FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !$user['assigned_spot']) {
    jsonResponse(['error' => 'You do not have an assigned spot to cancel release for'], 403);
}

$spotNumber = $user['assigned_spot'];

// Check if someone has already booked it
$stmt = $db->prepare("SELECT id FROM bookings WHERE spot_number = ? AND date = ?");
$stmt->execute([$spotNumber, $date]);
if ($stmt->fetch()) {
    jsonResponse(['error' => 'Cannot cancel release: spot has already been booked by someone else'], 400);
}

$stmt = $db->prepare("DELETE FROM releases WHERE user_id = ? AND spot_number = ? AND date = ?");
$stmt->execute([$_SESSION['user_id'], $spotNumber, $date]);

if ($stmt->rowCount() > 0) {
    jsonResponse(['success' => true]);
} else {
    jsonResponse(['error' => 'Release not found'], 404);
}
