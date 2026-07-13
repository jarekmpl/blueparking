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

    $users = [
        ['name' => 'Aneta Mondry', 'email' => 'a.mondry@bluerank.com', 'raw_pass' => 'gT8v4Kw', 'assigned_spot' => 109, 'is_admin' => 0],
        ['name' => 'Monika Marszałek', 'email' => 'm.marszalek@bluerank.com', 'raw_pass' => '3nPq2Lz', 'assigned_spot' => 104, 'is_admin' => 0],
        ['name' => 'Jarosław Miszczak', 'email' => 'j.miszczak@bluerank.com', 'raw_pass' => 'x9Jc5Rb', 'assigned_spot' => 106, 'is_admin' => 1],
        ['name' => 'Jacek Tkaczuk', 'email' => 'j.tkaczuk@bluerank.com', 'raw_pass' => '7mFa4Vw', 'assigned_spot' => 113, 'is_admin' => 0],
        ['name' => 'Tomasz Sąsiadek', 'email' => 't.sasiadek@bluerank.com', 'raw_pass' => 'k2Hz9Pc', 'assigned_spot' => 110, 'is_admin' => 0],
        ['name' => 'Aleksandra Piechocka', 'email' => 'o.piechocka@bluerank.com', 'raw_pass' => '5bYv8Nd', 'assigned_spot' => 97, 'is_admin' => 0],
        ['name' => 'Maciej Antczak', 'email' => 'm.antczak@bluerank.com', 'raw_pass' => 't4Wq6Lm', 'assigned_spot' => 98, 'is_admin' => 0],
        ['name' => 'Katarzyna Szymańska', 'email' => 'k.szymanska@bluerank.com', 'raw_pass' => '2pRx7Js', 'assigned_spot' => 112, 'is_admin' => 0],
        ['name' => 'Rafał Trąbski', 'email' => 'r.trabski@bluerank.com', 'raw_pass' => 'v8Nd3Bw', 'assigned_spot' => 115, 'is_admin' => 0],
        ['name' => 'Piotr Kowalczyk', 'email' => 'p.kowalczyk@bluerank.com', 'raw_pass' => '9cKf5Lp', 'assigned_spot' => 102, 'is_admin' => 0],
        ['name' => 'Daniel Smoliński', 'email' => 'd.smolinski@bluerank.com', 'raw_pass' => 'm2Zb8Px', 'assigned_spot' => 101, 'is_admin' => 0],
        ['name' => 'Mateusz Blumenfeld', 'email' => 'm.blumenfeld@bluerank.com', 'raw_pass' => '6qVw4Jn', 'assigned_spot' => 114, 'is_admin' => 0],
        ['name' => 'Piotr Matusiak', 'email' => 'p.matusiak@bluerank.com', 'raw_pass' => 'h5Tc9Mk', 'assigned_spot' => 105, 'is_admin' => 0],
        ['name' => 'Agnieszka Węglewska', 'email' => 'a.weglewska@bluerank.com', 'raw_pass' => '4bLp2Yz', 'assigned_spot' => 116, 'is_admin' => 0],
        ['name' => 'Dariusz Sendecki', 'email' => 'd.sendecki@bluerank.com', 'raw_pass' => '8nXm6Vc', 'assigned_spot' => 108, 'is_admin' => 0],
        ['name' => 'Weronika Węglewska', 'email' => 'w.weglewska@bluerank.com', 'raw_pass' => 'z3Jw7Fd', 'assigned_spot' => 111, 'is_admin' => 0],
        ['name' => 'Justyna Burchard', 'email' => 'j.burchard@bluerank.com', 'raw_pass' => '1pRc5Lw', 'assigned_spot' => 107, 'is_admin' => 0],
        ['name' => 'Karolina Pakulska', 'email' => 'k.pakulska@bluerank.com', 'raw_pass' => 'v6Fw9Nq', 'assigned_spot' => 103, 'is_admin' => 0],
        ['name' => 'Magdalena Euejda', 'email' => 'm.euejda@bluerank.com', 'raw_pass' => '8tMz2Bc', 'assigned_spot' => 99, 'is_admin' => 0],
        ['name' => 'Bartłomiej Majas', 'email' => 'b.majas@bluerank.com', 'raw_pass' => '4yLp8Xk', 'assigned_spot' => 100, 'is_admin' => 0]
    ];

    $stmt = $db->prepare("INSERT INTO users (name, email, password, assigned_spot, is_admin) VALUES (?, ?, ?, ?, ?)");
    foreach ($users as $u) {
        $hash = password_hash($u['raw_pass'], PASSWORD_DEFAULT);
        $stmt->execute([$u['name'], $u['email'], $hash, $u['assigned_spot'], $u['is_admin']]);
    }

    echo "Inicjalizacja zakończona pomyślnie. Utworzono 20 miejsc i 20 użytkowników.\n";

} catch (PDOException $e) {
    echo "Błąd inicjalizacji: " . $e->getMessage() . "\n";
}
