<?php
require_once __DIR__ . '/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method Not Allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$date = $data['date'] ?? '';
$targetUserId = $data['user_id'] ?? null;

if (empty($date)) {
    jsonResponse(['error' => 'Date is required'], 400);
}

// Ensure the user actually has a spot
if ($targetUserId) {
    requireAdmin($db);
    $userIdToCancel = $targetUserId;
} else {
    $userIdToCancel = $_SESSION['user_id'];
}

$stmt = $db->prepare("SELECT assigned_spot FROM users WHERE id = ?");
$stmt->execute([$userIdToCancel]);
$user = $stmt->fetch();

if (!$user || !$user['assigned_spot']) {
    jsonResponse(['error' => 'Ten użytkownik nie posiada przypisanego miejsca'], 403);
}

$spotNumber = $user['assigned_spot'];

// Sprawdź, czy użytkownik ma już zarezerwowane inne miejsce z puli na ten dzień
$stmt = $db->prepare("SELECT id FROM bookings WHERE user_id = ? AND date = ?");
$stmt->execute([$userIdToCancel, $date]);
if ($stmt->fetch()) {
    jsonResponse(['error' => 'Masz już zarezerwowane inne miejsce na ten dzień. Najpierw anuluj tamtą rezerwację, aby odzyskać swoje.'], 400);
}

// Check if someone has already booked it
$stmt = $db->prepare("SELECT id FROM bookings WHERE spot_number = ? AND date = ?");
$stmt->execute([$spotNumber, $date]);
if ($stmt->fetch()) {
    jsonResponse(['error' => 'Cannot cancel release: spot has already been booked by someone else'], 400);
}

$stmt = $db->prepare("DELETE FROM releases WHERE user_id = ? AND spot_number = ? AND date = ?");
$stmt->execute([$userIdToCancel, $spotNumber, $date]);

if ($stmt->rowCount() > 0) {
    jsonResponse(['success' => true]);
} else {
    jsonResponse(['error' => 'Release not found'], 404);
}
