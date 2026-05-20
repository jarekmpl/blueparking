<?php
require_once __DIR__ . '/../db.php';
requireAdmin($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query("SELECT number, name FROM spots ORDER BY number ASC");
    jsonResponse(['spots' => $stmt->fetchAll()]);
} 
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $number = isset($data['number']) ? (int)$data['number'] : 0;
    $name = $data['name'] ?? '';

    if ($number <= 0 || empty($name)) {
        jsonResponse(['error' => 'Invalid spot number or name missing'], 400);
    }

    try {
        $stmt = $db->prepare("INSERT INTO spots (number, name) VALUES (?, ?)");
        $stmt->execute([$number, $name]);
        jsonResponse(['success' => true]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(['error' => 'Spot with this number already exists'], 400);
        }
        jsonResponse(['error' => 'Database error'], 500);
    }
}
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $number = isset($data['number']) ? (int)$data['number'] : 0;
    $name = $data['name'] ?? '';

    if ($number <= 0 || empty($name)) {
        jsonResponse(['error' => 'Invalid spot number or name missing'], 400);
    }

    try {
        $stmt = $db->prepare("UPDATE spots SET name = ? WHERE number = ?");
        $stmt->execute([$name, $number]);
        jsonResponse(['success' => true]);
    } catch (PDOException $e) {
        jsonResponse(['error' => 'Database error'], 500);
    }
}
else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
