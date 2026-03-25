<?php
/**
 * Debug login — ELIMINAR DEL SERVIDOR DESPUÉS DE USAR
 * URL: https://tudominio.com/ticketmaster/debug_login.php?key=setup2026
 */
if (($_GET['key'] ?? '') !== 'setup2026') { http_response_code(403); die('403'); }

// Leer .env
$envFile = dirname(__DIR__) . '/.env';
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
    [$k, $v] = array_map('trim', explode('=', $line, 2));
    $env[$k] = trim($v, "'\"");
}

$host   = $env['database.default.hostname'] ?? 'localhost';
$dbname = $env['database.default.database'] ?? '';
$user   = $env['database.default.username'] ?? '';
$pass   = $env['database.default.password'] ?? '';

$pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass);

// Obtener todos los usuarios
$rows = $pdo->query("SELECT id, name, email, password, role, is_active, deleted_at FROM users")->fetchAll(PDO::FETCH_ASSOC);

$testPasswords = ['Admin@1234', 'User@1234'];

echo '<pre style="font-family:monospace;font-size:14px">';
echo "=== USUARIOS EN BD ===\n\n";

foreach ($rows as $row) {
    echo "ID: {$row['id']}\n";
    echo "  name:       {$row['name']}\n";
    echo "  email:      {$row['email']}\n";
    echo "  role:       {$row['role']}\n";
    echo "  is_active:  {$row['is_active']}\n";
    echo "  deleted_at: " . ($row['deleted_at'] ?? 'NULL') . "\n";
    echo "  password hash (primeros 30 chars): " . substr($row['password'], 0, 30) . "...\n";

    foreach ($testPasswords as $testPass) {
        $ok = password_verify($testPass, $row['password']) ? '✅ OK' : '❌ FALLA';
        echo "  password_verify('{$testPass}'): {$ok}\n";
    }
    echo "\n";
}

// Simular exactamente lo que hace findActiveByEmail
echo "=== SIMULANDO findActiveByEmail('admin\@ticketmaster.lt') ===\n";
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 AND deleted_at IS NULL LIMIT 1");
$stmt->execute(['admin@ticketmaster.lt']);
$found = $stmt->fetch(PDO::FETCH_ASSOC);
if ($found) {
    echo "  Encontrado: ID={$found['id']}, email={$found['email']}\n";
    $v = password_verify('Admin@1234', $found['password']);
    echo "  password_verify('Admin@1234'): " . ($v ? '✅ OK' : '❌ FALLA') . "\n";
} else {
    echo "  ❌ No encontrado (email incorrecto, is_active=0 o deleted_at no nulo)\n";
}

echo "\n=== PHP INFO ===\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "password_hash test: " . substr(password_hash('Admin@1234', PASSWORD_BCRYPT), 0, 30) . "...\n";

echo '</pre>';
