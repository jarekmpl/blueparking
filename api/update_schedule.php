<?php
require_once __DIR__ . '/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method Not Allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$schedule = $data['schedule_days'] ?? [];

// Walidacja - czy dni są w zakresie 1-7
$validDays = [];
foreach ($schedule as $day) {
    $dayInt = (int)$day;
    if ($dayInt >= 1 && $dayInt <= 7) {
        $validDays[] = $dayInt;
    }
}
$validDays = array_unique($validDays);
sort($validDays);
$scheduleStr = implode(',', $validDays);

// Zapis
$stmt = $db->prepare("UPDATE users SET schedule_days = ? WHERE id = ?");
if ($stmt->execute([$scheduleStr, $_SESSION['user_id']])) {
    jsonResponse(['success' => true]);
} else {
    jsonResponse(['error' => 'Database error'], 500);
}
