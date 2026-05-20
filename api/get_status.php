<?php
require_once __DIR__ . '/db.php';
requireLogin();

$date = $_GET['date'] ?? date('Y-m-d');

// Fetch all spots and their owners
$spotsStmt = $db->query("
    SELECT s.number, s.name as spot_name, u.id as owner_id, u.name as owner_name 
    FROM spots s 
    LEFT JOIN users u ON s.number = u.assigned_spot
");
$spots = $spotsStmt->fetchAll();

// Fetch releases for the given date
$releasesStmt = $db->prepare("SELECT spot_number, user_id FROM releases WHERE date = ?");
$releasesStmt->execute([$date]);
$releasesData = $releasesStmt->fetchAll();
$releases = [];
foreach ($releasesData as $r) {
    $releases[$r['spot_number']] = true;
}

// Fetch bookings for the given date
$bookingsStmt = $db->prepare("
    SELECT b.spot_number, u.id as booked_by_id, u.name as booked_by_name 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    WHERE b.date = ?
");
$bookingsStmt->execute([$date]);
$bookingsData = $bookingsStmt->fetchAll();
$bookings = [];
foreach ($bookingsData as $b) {
    $bookings[$b['spot_number']] = $b;
}

$response = [];
foreach ($spots as $spot) {
    $num = $spot['number'];
    
    $isReleased = isset($releases[$num]);
    $bookingInfo = $bookings[$num] ?? null;
    
    $status = 'occupied'; // Default: if it's assigned and not released
    
    if (!$spot['owner_id']) {
        // No owner, it's a shared spot
        $status = 'available';
    } elseif ($isReleased) {
        $status = 'available';
    }
    
    if ($bookingInfo) {
        $status = 'booked';
    }

    $response[] = [
        'number' => $num,
        'spot_name' => $spot['spot_name'],
        'owner_id' => $spot['owner_id'],
        'owner_name' => $spot['owner_name'],
        'is_released' => $isReleased,
        'status' => $status,
        'booked_by_id' => $bookingInfo['booked_by_id'] ?? null,
        'booked_by_name' => $bookingInfo['booked_by_name'] ?? null
    ];
}

jsonResponse(['date' => $date, 'spots' => $response]);
