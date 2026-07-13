<?php
require_once __DIR__ . '/db.php';

try {
    $stmt = $db->prepare("UPDATE users SET email = 'o.piechocka@bluerank.com' WHERE email = 'a.piechocka@bluerank.com'");
    $stmt->execute();
    $count = $stmt->rowCount();
    
    if ($count > 0) {
        echo "<h1>SUKCES</h1><p>Pomyślnie zaktualizowano adres email w bazie na serwerze! Możesz teraz bezpiecznie usunąć ten plik.</p>";
    } else {
        echo "<h1>UWAGA</h1><p>Zaktualizowano 0 wierszy. Możliwe, że email został już zmieniony wcześniej lub konto o takim adresie nie istnieje.</p>";
    }
} catch (PDOException $e) {
    echo "<h1>BŁĄD</h1><p>" . $e->getMessage() . "</p>";
}
