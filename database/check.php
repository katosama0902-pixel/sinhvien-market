<pre>
<?php
require __DIR__ . '/../config/Database.php';
$_envFile = __DIR__ . '/../.env';
if (file_exists($_envFile)) {
    foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        if (str_starts_with(trim($_line), '#') || !str_contains($_line, '=')) continue;
        [$_k, $_v] = explode('=', $_line, 2);
        $_k = trim($_k); $_v = trim(trim($_v), '"\'');
        if (!isset($_ENV[$_k])) { $_ENV[$_k] = $_v; putenv("{$_k}={$_v}"); }
    }
}
$pdo = \Config\Database::getInstance();
$stmt = $pdo->query("DESCRIBE users");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
