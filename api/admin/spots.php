<?php
require_once __DIR__ . '/../db.php';
requireAdmin($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query("SELECT number FROM spots ORDER BY number ASC");
    jsonResponse(['spots' => $stmt->fetchAll()]);
} 
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $number = isset($data['number']) ? (int)$data['number'] : 0;

    if ($number <= 0) {
        jsonResponse(['error' => 'Invalid spot number'], 400);
    }

    try {
        $stmt = $db->prepare("INSERT INTO spots (number) VALUES (?)");
        $stmt->execute([$number]);
        jsonResponse(['success' => true]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(['error' => 'Spot with this number already exists'], 400);
        }
        jsonResponse(['error' => 'Database error'], 500);
    }
}
else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
