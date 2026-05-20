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
    $userIdToRelease = $targetUserId;
} else {
    $userIdToRelease = $_SESSION['user_id'];
}

$stmt = $db->prepare("SELECT assigned_spot FROM users WHERE id = ?");
$stmt->execute([$userIdToRelease]);
$user = $stmt->fetch();

if (!$user || !$user['assigned_spot']) {
    jsonResponse(['error' => 'Ten użytkownik nie posiada przypisanego miejsca.'], 403);
}

$spotNumber = $user['assigned_spot'];

try {
    $stmt = $db->prepare("INSERT INTO releases (user_id, spot_number, date) VALUES (?, ?, ?)");
    $stmt->execute([$userIdToRelease, $spotNumber, $date]);
    jsonResponse(['success' => true]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // UNIQUE constraint failed
        jsonResponse(['error' => 'Spot already released for this date'], 400);
    }
    jsonResponse(['error' => 'Database error'], 500);
}
