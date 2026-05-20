<?php
require_once __DIR__ . '/db.php';

echo "Rozpoczynam inicjalizację bazy danych...\n";

try {
    // Usunięcie starych tabel, aby zaktualizować schemat
    $db->exec("DROP TABLE IF EXISTS bookings");
    $db->exec("DROP TABLE IF EXISTS releases");
    $db->exec("DROP TABLE IF EXISTS users");
    $db->exec("DROP TABLE IF EXISTS spots");

    // Utworzenie tabel
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            is_admin INTEGER DEFAULT 0,
            assigned_spot INTEGER NULL,
            FOREIGN KEY(assigned_spot) REFERENCES spots(number)
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS spots (
            number INTEGER PRIMARY KEY,
            name TEXT NOT NULL
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS releases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            spot_number INTEGER NOT NULL,
            date TEXT NOT NULL,
            FOREIGN KEY(user_id) REFERENCES users(id),
            FOREIGN KEY(spot_number) REFERENCES spots(number),
            UNIQUE(user_id, date)
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS bookings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            spot_number INTEGER NOT NULL,
            date TEXT NOT NULL,
            FOREIGN KEY(user_id) REFERENCES users(id),
            FOREIGN KEY(spot_number) REFERENCES spots(number),
            UNIQUE(user_id, date),
            UNIQUE(spot_number, date)
        )
    ");

    // Dodanie miejsc
    for ($i = 1; $i <= 5; $i++) {
        $stmt = $db->prepare("INSERT INTO spots (number, name) VALUES (?, ?)");
        $stmt->execute([$i, "Miejsce " . $i]);
    }

    // Dodanie użytkowników
    $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
    
    $users = [
        ['name' => 'Jakub Miszczak', 'email' => 'j.miszczak@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => null, 'is_admin' => 1],
        ['name' => 'Jan Kowalski', 'email' => 'jan@bluerank.pl', 'password' => $passwordHash, 'assigned_spot' => 1, 'is_admin' => 0],
        ['name' => 'Anna Nowak', 'email' => 'anna@bluerank.pl', 'password' => $passwordHash, 'assigned_spot' => 2, 'is_admin' => 0],
        ['name' => 'Piotr Wiśniewski', 'email' => 'piotr@bluerank.pl', 'password' => $passwordHash, 'assigned_spot' => 3, 'is_admin' => 0],
        ['name' => 'Kasia Wójcik', 'email' => 'kasia@bluerank.pl', 'password' => $passwordHash, 'assigned_spot' => null, 'is_admin' => 0],
        ['name' => 'Michał Kamiński', 'email' => 'michal@bluerank.pl', 'password' => $passwordHash, 'assigned_spot' => null, 'is_admin' => 0]
    ];

    $stmt = $db->prepare("INSERT INTO users (name, email, password, assigned_spot, is_admin) VALUES (?, ?, ?, ?, ?)");
    foreach ($users as $u) {
        $stmt->execute([$u['name'], $u['email'], $u['password'], $u['assigned_spot'], $u['is_admin']]);
    }

    echo "Inicjalizacja zakończona pomyślnie. Utworzono 5 miejsc i 6 użytkowników (w tym 1 admin).\n";
    echo "Hasło dla wszystkich: password123\n";

} catch (PDOException $e) {
    echo "Błąd inicjalizacji: " . $e->getMessage() . "\n";
}
