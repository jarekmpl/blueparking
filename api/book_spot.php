<?php
require_once __DIR__ . '/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method Not Allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$date = $data['date'] ?? '';
$spotNumber = $data['spot_number'] ?? '';

if (empty($date) || empty($spotNumber)) {
    jsonResponse(['error' => 'Date and spot_number are required'], 400);
}

// Ensure the user doesn't already have an active booking or unreleased assigned spot for this date
$stmt = $db->prepare("SELECT assigned_spot FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user && $user['assigned_spot']) {
    // Check if they released their assigned spot for this date
    $stmt = $db->prepare("SELECT id FROM releases WHERE user_id = ? AND date = ?");
    $stmt->execute([$_SESSION['user_id'], $date]);
    if (!$stmt->fetch()) {
        jsonResponse(['error' => 'You already have your assigned spot for this date (not released).'], 400);
    }
}

// Check if they already have a booking
$stmt = $db->prepare("SELECT id FROM bookings WHERE user_id = ? AND date = ?");
$stmt->execute([$_SESSION['user_id'], $date]);
if ($stmt->fetch()) {
    jsonResponse(['error' => 'You already have a booking for this date.'], 400);
}

// Check if the spot is actually available
// 1. Check if spot exists
$stmt = $db->prepare("SELECT number FROM spots WHERE number = ?");
$stmt->execute([$spotNumber]);
if (!$stmt->fetch()) {
    jsonResponse(['error' => 'Spot does not exist.'], 404);
}

// 2. Check if spot is booked by someone else
$stmt = $db->prepare("SELECT id FROM bookings WHERE spot_number = ? AND date = ?");
$stmt->execute([$spotNumber, $date]);
if ($stmt->fetch()) {
    jsonResponse(['error' => 'Spot is already booked by someone else.'], 400);
}

// 3. Check if spot is owned by someone
$stmt = $db->prepare("SELECT id FROM users WHERE assigned_spot = ?");
$stmt->execute([$spotNumber]);
$owner = $stmt->fetch();

if ($owner) {
    // 4. If owned, it MUST be released for this date
    $stmt = $db->prepare("SELECT id FROM releases WHERE spot_number = ? AND date = ?");
    $stmt->execute([$spotNumber, $date]);
    if (!$stmt->fetch()) {
         jsonResponse(['error' => 'Spot is not available for booking.'], 400);
    }
}

// Insert booking
try {
    $stmt = $db->prepare("INSERT INTO bookings (user_id, spot_number, date) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $spotNumber, $date]);
    jsonResponse(['success' => true]);
} catch (PDOException $e) {
    jsonResponse(['error' => 'Database error'], 500);
}
