<?php
/**
 * TicketMaster — Instalador de base de datos
 *
 * IMPORTANTE: Elimina este archivo del servidor después de ejecutarlo.
 * URL: https://tudominio.com/ticketmaster/install.php?key=setup2026
 */

// ── Protección mínima ────────────────────────────────────────────────────
if (($_GET['key'] ?? '') !== 'setup2026') {
    http_response_code(403);
    die('<h2>403 Forbidden</h2><p>Usa <code>?key=setup2026</code></p>');
}

// ── Leer .env del proyecto ────────────────────────────────────────────────
$envFile = dirname(__DIR__) . '/.env';
$env = [];
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        $env[$k] = trim($v, "'\"");
    }
}

$host   = $env['database.default.hostname'] ?? 'localhost';
$dbname = $env['database.default.database'] ?? '';
$user   = $env['database.default.username'] ?? '';
$pass   = $env['database.default.password'] ?? '';

$output = [];
$errors = [];

// ── Conectar con PDO ──────────────────────────────────────────────────────
try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $output[] = "✅ Conexión a base de datos OK ({$dbname}).";
} catch (PDOException $e) {
    $errors[] = '❌ No se pudo conectar a la BD: ' . $e->getMessage();
    goto render;
}

// ── Crear tablas (migraciones manuales) ───────────────────────────────────
$tables = [

'migrations' => "CREATE TABLE IF NOT EXISTS `migrations` (
  `id`        INT(9) UNSIGNED NOT NULL AUTO_INCREMENT,
  `version`   VARCHAR(255) NOT NULL DEFAULT '',
  `class`     TEXT NOT NULL,
  `group`     TEXT NOT NULL,
  `namespace` TEXT NOT NULL,
  `time`      INT(11) NOT NULL,
  `batch`     INT(9) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

'users' => "CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('admin','user') NOT NULL DEFAULT 'user',
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

'quarries' => "CREATE TABLE IF NOT EXISTS `quarries` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150) NOT NULL,
  `address`    VARCHAR(255) DEFAULT NULL,
  `phone`      VARCHAR(50)  DEFAULT NULL,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

'trucks' => "CREATE TABLE IF NOT EXISTS `trucks` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `plate`      VARCHAR(20) NOT NULL,
  `carrier`    VARCHAR(150) DEFAULT NULL,
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  `deleted_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate` (`plate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

'invoices' => "CREATE TABLE IF NOT EXISTS `invoices` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_no`  VARCHAR(50)  NOT NULL,
  `quarry_id`   INT(11) UNSIGNED DEFAULT NULL,
  `issued_at`   DATE DEFAULT NULL,
  `total_tons`  DECIMAL(10,2) DEFAULT 0.00,
  `notes`       TEXT DEFAULT NULL,
  `created_by`  INT(11) UNSIGNED DEFAULT NULL,
  `created_at`  DATETIME DEFAULT NULL,
  `updated_at`  DATETIME DEFAULT NULL,
  `deleted_at`  DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

'invoice_tickets' => "CREATE TABLE IF NOT EXISTS `invoice_tickets` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id`  INT(11) UNSIGNED NOT NULL,
  `ticket_no`   VARCHAR(50) NOT NULL,
  `truck_id`    INT(11) UNSIGNED DEFAULT NULL,
  `tons`        DECIMAL(10,2) DEFAULT 0.00,
  `ticket_date` DATE DEFAULT NULL,
  `created_at`  DATETIME DEFAULT NULL,
  `updated_at`  DATETIME DEFAULT NULL,
  `deleted_at`  DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

'tasks' => "CREATE TABLE IF NOT EXISTS `tasks` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status`      ENUM('open','in_progress','closed') NOT NULL DEFAULT 'open',
  `assigned_to` INT(11) UNSIGNED DEFAULT NULL,
  `created_by`  INT(11) UNSIGNED DEFAULT NULL,
  `due_date`    DATE DEFAULT NULL,
  `created_at`  DATETIME DEFAULT NULL,
  `updated_at`  DATETIME DEFAULT NULL,
  `deleted_at`  DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

'task_tickets' => "CREATE TABLE IF NOT EXISTS `task_tickets` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id`     INT(11) UNSIGNED NOT NULL,
  `ticket_no`   VARCHAR(50) NOT NULL,
  `truck_id`    INT(11) UNSIGNED DEFAULT NULL,
  `tons`        DECIMAL(10,2) DEFAULT 0.00,
  `ticket_date` DATE DEFAULT NULL,
  `created_at`  DATETIME DEFAULT NULL,
  `updated_at`  DATETIME DEFAULT NULL,
  `deleted_at`  DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

];

foreach ($tables as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        $output[] = "✅ Tabla <code>{$tableName}</code> lista.";
    } catch (PDOException $e) {
        $errors[] = "❌ Tabla {$tableName}: " . $e->getMessage();
    }
}

// ── Insertar usuarios por defecto (solo si tabla vacía) ──────────────────
try {
    $count = (int) $pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
    if ($count === 0) {
        $now  = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare(
            "INSERT INTO `users` (name, email, password, role, is_active, created_at, updated_at)
             VALUES (:name, :email, :password, :role, 1, :ca, :ua)"
        );
        $stmt->execute([
            'name'     => 'Administrador',
            'email'    => 'admin@ticketmaster.lt',
            'password' => password_hash('Admin@1234', PASSWORD_BCRYPT),
            'role'     => 'admin',
            'ca'       => $now,
            'ua'       => $now,
        ]);
        $stmt->execute([
            'name'     => 'Usuario Demo',
            'email'    => 'user@ticketmaster.lt',
            'password' => password_hash('User@1234', PASSWORD_BCRYPT),
            'role'     => 'user',
            'ca'       => $now,
            'ua'       => $now,
        ]);
        $output[] = '✅ Usuarios creados correctamente.';
    } else {
        $output[] = "ℹ️ La tabla users ya tiene {$count} registro(s). No se insertaron usuarios.";
    }
} catch (PDOException $e) {
    $errors[] = '❌ Usuarios: ' . $e->getMessage();
}

render:
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>TicketMaster — Instalación</title>
  <style>
    body  { font-family: sans-serif; max-width: 680px; margin: 60px auto; padding: 0 20px; color: #333; }
    h1    { border-bottom: 2px solid #007bff; padding-bottom: 8px; }
    .ok   { color: #155724; background: #d4edda; padding: 6px 10px; border-radius: 4px; margin: 4px 0; }
    .err  { color: #721c24; background: #f8d7da; padding: 6px 10px; border-radius: 4px; margin: 4px 0; }
    .info { color: #0c5460; background: #d1ecf1; padding: 6px 10px; border-radius: 4px; margin: 4px 0; }
    .warn { background: #fff3cd; border: 1px solid #ffc107; padding: 14px; border-radius: 4px; margin-top: 24px; }
    code  { background: #eee; padding: 2px 5px; border-radius: 3px; font-size: .9em; }
    table { border-collapse: collapse; margin-top: 10px; }
    td,th { border: 1px solid #ccc; padding: 6px 14px; }
    th    { background: #f4f4f4; }
  </style>
</head>
<body>
  <h1>TicketMaster — Instalación</h1>

  <?php foreach ($output as $m): ?>
    <p class="<?= str_starts_with($m,'✅') ? 'ok' : 'info' ?>"><?= $m ?></p>
  <?php endforeach; ?>

  <?php foreach ($errors as $m): ?>
    <p class="err"><?= htmlspecialchars($m) ?></p>
  <?php endforeach; ?>

  <?php if (empty($errors)): ?>
    <hr>
    <h2>Credenciales de acceso</h2>
    <table>
      <tr><th>Rol</th><th>Email</th><th>Contraseña</th></tr>
      <tr><td>Admin</td><td>admin@ticketmaster.lt</td><td>Admin@1234</td></tr>
      <tr><td>User</td> <td>user@ticketmaster.lt</td> <td>User@1234</td></tr>
    </table>
    <p><a href="/ticketmaster/auth/login">→ Ir al login</a></p>
  <?php endif; ?>

  <div class="warn">
    ⚠️ <strong>Elimina este archivo del servidor una vez terminado:</strong><br>
    <code>public_html/ticketMaster/public/install.php</code>
  </div>
</body>
</html>
