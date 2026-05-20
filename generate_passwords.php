<?php
$users = [
    ['Aneta Mondry', 'a.mondry@bluerank.com', 109, 0],
    ['Monika Marszałek', 'm.marszalek@bluerank.com', 104, 0],
    ['Jarosław Miszczak', 'j.miszczak@bluerank.com', 106, 1],
    ['Jacek Tkaczuk', 'j.tkaczuk@bluerank.com', 113, 0],
    ['Tomasz Sąsiadek', 't.sasiadek@bluerank.com', 110, 0],
    ['Aleksandra Piechocka', 'a.piechocka@bluerank.com', 97, 0],
    ['Maciej Antczak', 'm.antczak@bluerank.com', 98, 0],
    ['Katarzyna Szymańska', 'k.szymanska@bluerank.com', 112, 0],
    ['Rafał Trąbski', 'r.trabski@bluerank.com', 115, 0],
    ['Piotr Kowalczyk', 'p.kowalczyk@bluerank.com', 102, 0],
    ['Daniel Smoliński', 'd.smolinski@bluerank.com', 101, 0],
    ['Mateusz Blumenfeld', 'm.blumenfeld@bluerank.com', 114, 0],
    ['Piotr Matusiak', 'p.matusiak@bluerank.com', 105, 0],
    ['Agnieszka Węglewska', 'a.weglewska@bluerank.com', 116, 0],
    ['Dariusz Sendecki', 'd.sendecki@bluerank.com', 108, 0],
    ['Weronika Węglewska', 'w.weglewska@bluerank.com', 111, 0],
    ['Justyna Burchard', 'j.burchard@bluerank.com', 107, 0],
    ['Karolina Pakulska', 'k.pakulska@bluerank.com', 103, 0],
    ['Magdalena Euejda', 'm.euejda@bluerank.com', 99, 0],
    ['Bartłomiej Majas', 'b.majas@bluerank.com', 100, 0]
];

function generatePassword() {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $pass = '';
    for ($i = 0; $i < 7; $i++) {
        $pass .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $pass;
}

$outputCode = "\$users = [\n";
$markdownTable = "| Imię i Nazwisko | Email | Hasło |\n|---|---|---|\n";

foreach ($users as $u) {
    $pass = generatePassword();
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $outputCode .= "    ['name' => '{$u[0]}', 'email' => '{$u[1]}', 'password' => '{$hash}', 'assigned_spot' => {$u[2]}, 'is_admin' => {$u[3]}],\n";
    $markdownTable .= "| {$u[0]} | `{$u[1]}` | `{$pass}` |\n";
}
$outputCode .= "];";

file_put_contents('passwords_table.md', $markdownTable);
file_put_contents('users_code.txt', $outputCode);

echo "Done.";
?>
