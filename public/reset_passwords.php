<?php
/**
 * Resetea las contraseñas de los usuarios admin y user.
 * ELIMINAR DEL SERVIDOR DESPUÉS DE USAR.
 * URL: https://tudominio.com/ticketmaster/reset_passwords.php?key=setup2026
 */
if (($_GET['key'] ?? '') !== 'setup2026') { http_response_code(403); die('403'); }

$envFile = dirname(__DIR__) . '/.env';
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
    [$k, $v] = array_map('trim', explode('=', $line, 2));
    $env[$k] = trim($v, "'\"");
}

$pdo = new PDO(
    "mysql:host={$env['database.default.hostname']};dbname={$env['database.default.database']};charset=utf8mb4",
    $env['database.default.username'],
    $env['database.default.password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$updates = [
    ['email' => 'admin@ticketmaster.lt', 'password' => 'Admin@1234'],
    ['email' => 'user@ticketmaster.lt',  'password' => 'User@1234'],
];

echo '<pre>';
$stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?");

foreach ($updates as $u) {
    $hash = password_hash($u['password'], PASSWORD_BCRYPT);
    $stmt->execute([$hash, $u['email']]);
    $rows = $stmt->rowCount();

    // Verificar inmediatamente
    $row = $pdo->query("SELECT password FROM users WHERE email = '{$u['email']}'")->fetch();
    $ok  = password_verify($u['password'], $row['password']) ? '✅' : '❌';

    echo "{$ok} {$u['email']} → contraseña actualizada a '{$u['password']}' (filas: {$rows})\n";
}
echo "\nListo. Puedes iniciar sesión con:\n";
echo "  admin@ticketmaster.lt / Admin@1234\n";
echo "  user@ticketmaster.lt  / User@1234\n";
echo '</pre>';
