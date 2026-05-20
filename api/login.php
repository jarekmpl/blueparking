<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method Not Allowed'], 405);
}

// ---- RATE LIMITING ----
$MAX_ATTEMPTS = 10;      // Maksymalna liczba prób
$BLOCK_SECONDS = 15 * 60; // Blokada: 15 minut

// Pobierz IP klienta (obsługa proxy/Cloudflare)
$ip = $_SERVER['HTTP_CF_CONNECTING_IP']
    ?? $_SERVER['HTTP_X_FORWARDED_FOR']
    ?? $_SERVER['REMOTE_ADDR'];
$ip = trim(explode(',', $ip)[0]); // Weź tylko pierwsze IP jeśli lista

$windowStart = time() - $BLOCK_SECONDS;

// Usuń stare wpisy (starsze niż 15 minut) - auto-cleanup
$db->prepare("DELETE FROM login_attempts WHERE attempted_at < ?")->execute([$windowStart]);

// Policz próby z tego IP w oknie czasowym
$stmt = $db->prepare("SELECT COUNT(*) as cnt FROM login_attempts WHERE ip = ? AND attempted_at >= ?");
$stmt->execute([$ip, $windowStart]);
$attempts = $stmt->fetch()['cnt'];

if ($attempts >= $MAX_ATTEMPTS) {
    jsonResponse([
        'error' => 'Zbyt wiele nieudanych prób logowania. Spróbuj ponownie za 15 minut.'
    ], 429);
}
// ---- KONIEC RATE LIMITING ----

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    jsonResponse(['error' => 'Email and password are required'], 400);
}

$stmt = $db->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Sukces - wyczyść blokadę dla tego IP
    $db->prepare("DELETE FROM login_attempts WHERE ip = ?")->execute([$ip]);
    $_SESSION['user_id'] = $user['id'];
    session_regenerate_id(true); // Zabezpieczenie przed Session Fixation
    jsonResponse(['success' => true]);
} else {
    // Niepowodzenie - dodaj wpis do licznika
    $db->prepare("INSERT INTO login_attempts (ip, attempted_at) VALUES (?, ?)")->execute([$ip, time()]);
    
    $remaining = $MAX_ATTEMPTS - $attempts - 1;
    if ($remaining <= 0) {
        jsonResponse(['error' => 'Zbyt wiele nieudanych prób. Konto tymczasowo zablokowane na 15 minut.'], 429);
    }
    jsonResponse(['error' => "Nieprawidłowy email lub hasło. Pozostało prób: $remaining"], 401);
}
