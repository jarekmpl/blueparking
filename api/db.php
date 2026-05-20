<?php
session_start();

$dbFile = __DIR__ . '/.db_9xQ2LpZ7vM3n.sqlite';
$dbExists = file_exists($dbFile);

try {
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Automatyczne tworzenie tabeli rate-limitingu - nie wymaga ponownego init.php
    $db->exec("
        CREATE TABLE IF NOT EXISTS login_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip TEXT NOT NULL,
            attempted_at INTEGER NOT NULL
        )
    ");
    
    // Automatyczne dodanie kolumny harmonogramu do istniejącej tabeli
    try {
        $db->exec("ALTER TABLE users ADD COLUMN schedule_days TEXT DEFAULT '1,2,3,4,5'");
    } catch (PDOException $e) {
        // Ignoruj błąd jeśli kolumna już istnieje (kod 'HY000' lub message zawierający 'duplicate column')
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Ensure JSON response for all API calls
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Function to check if user is logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }
}

// Function to check if user is admin
function requireAdmin($db) {
    requireLogin();
    $stmt = $db->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || !$user['is_admin']) {
        jsonResponse(['error' => 'Forbidden: Admins only'], 403);
    }
}
