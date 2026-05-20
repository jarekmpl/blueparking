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

$stmt = $db->prepare("DELETE FROM bookings WHERE user_id = ? AND date = ?");
$stmt->execute([$_SESSION['user_id'], $date]);

if ($stmt->rowCount() > 0) {
    jsonResponse(['success' => true]);
} else {
    jsonResponse(['error' => 'Booking not found'], 404);
}
