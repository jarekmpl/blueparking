<?php
require_once __DIR__ . '/../db.php';
requireAdmin($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $db->query("SELECT id, name, email, assigned_spot, is_admin FROM users ORDER BY id DESC");
    jsonResponse(['users' => $stmt->fetchAll()]);
} 
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $is_admin = isset($data['is_admin']) ? (int)$data['is_admin'] : 0;
    $assigned_spot = $data['assigned_spot'] ?? null;

    if (empty($name) || empty($email) || empty($password)) {
        jsonResponse(['error' => 'Name, email, and password are required'], 400);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $db->prepare("INSERT INTO users (name, email, password, assigned_spot, is_admin) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hash, $assigned_spot ?: null, $is_admin]);
        jsonResponse(['success' => true]);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(['error' => 'Email already exists'], 400);
        }
        jsonResponse(['error' => 'Database error'], 500);
    }
} 
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    $name = $data['name'] ?? '';
    $assigned_spot = $data['assigned_spot'] ?? null;
    $is_admin = isset($data['is_admin']) ? (int)$data['is_admin'] : 0;

    if (!$id || empty($name)) {
        jsonResponse(['error' => 'ID and name are required'], 400);
    }

    // Protect admin from demoting themselves by accident, maybe? 
    // Just allow it, keep it simple.

    try {
        $stmt = $db->prepare("UPDATE users SET name = ?, assigned_spot = ?, is_admin = ? WHERE id = ?");
        $stmt->execute([$name, $assigned_spot ?: null, $is_admin, $id]);
        jsonResponse(['success' => true]);
    } catch (PDOException $e) {
        jsonResponse(['error' => 'Database error'], 500);
    }
}
else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
