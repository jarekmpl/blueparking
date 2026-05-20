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

    $spotsData = [
        109, 104, 106, 113, 110, 97, 98, 112, 115, 102,
        101, 114, 105, 116, 108, 111, 107, 103, 99, 100
    ];
    sort($spotsData);

    // Dodanie miejsc
    foreach ($spotsData as $spotNum) {
        $stmt = $db->prepare("INSERT INTO spots (number, name) VALUES (?, ?)");
        $stmt->execute([$spotNum, "Miejsce " . $spotNum]);
    }

    // Hasło testowe: password123
    $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
    
    $users = [
        ['name' => 'Aneta Mondry', 'email' => 'a.mondry@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 109, 'is_admin' => 0],
        ['name' => 'Monika Marszałek', 'email' => 'm.marszalek@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 104, 'is_admin' => 0],
        ['name' => 'Jarosław Miszczak', 'email' => 'j.miszczak@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 106, 'is_admin' => 1],
        ['name' => 'Jacek Tkaczuk', 'email' => 'j.tkaczuk@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 113, 'is_admin' => 0],
        ['name' => 'Tomasz Sąsiadek', 'email' => 't.sasiadek@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 110, 'is_admin' => 0],
        ['name' => 'Aleksandra Piechocka', 'email' => 'a.piechocka@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 97, 'is_admin' => 0],
        ['name' => 'Maciej Antczak', 'email' => 'm.antczak@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 98, 'is_admin' => 0],
        ['name' => 'Katarzyna Szymańska', 'email' => 'k.szymanska@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 112, 'is_admin' => 0],
        ['name' => 'Rafał Trąbski', 'email' => 'r.trabski@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 115, 'is_admin' => 0],
        ['name' => 'Piotr Kowalczyk', 'email' => 'p.kowalczyk@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 102, 'is_admin' => 0],
        ['name' => 'Daniel Smoliński', 'email' => 'd.smolinski@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 101, 'is_admin' => 0],
        ['name' => 'Mateusz Blumenfeld', 'email' => 'm.blumenfeld@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 114, 'is_admin' => 0],
        ['name' => 'Piotr Matusiak', 'email' => 'p.matusiak@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 105, 'is_admin' => 0],
        ['name' => 'Agnieszka Węglewska', 'email' => 'a.weglewska@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 116, 'is_admin' => 0],
        ['name' => 'Dariusz Sendecki', 'email' => 'd.sendecki@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 108, 'is_admin' => 0],
        ['name' => 'Weronika Węglewska', 'email' => 'w.weglewska@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 111, 'is_admin' => 0],
        ['name' => 'Justyna Burchard', 'email' => 'j.burchard@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 107, 'is_admin' => 0],
        ['name' => 'Karolina Pakulska', 'email' => 'k.pakulska@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 103, 'is_admin' => 0],
        ['name' => 'Magdalena Euejda', 'email' => 'm.euejda@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 99, 'is_admin' => 0],
        ['name' => 'Bartłomiej Majas', 'email' => 'b.majas@bluerank.com', 'password' => $passwordHash, 'assigned_spot' => 100, 'is_admin' => 0]
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
