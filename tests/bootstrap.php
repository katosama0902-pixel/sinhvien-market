<?php
/**
 * PHPUnit Bootstrap File
 * Khởi tạo môi trường, load biến môi trường và thiết lập Autoloader cho các bài Test.
 */

define('ROOT', dirname(__DIR__));
define('APP_PATH', ROOT . '/app');
define('CORE_PATH', ROOT . '/core');
define('CONFIG_PATH', ROOT . '/config');

// Load Composer Autoloader
if (file_exists(ROOT . '/vendor/autoload.php')) {
    require_once ROOT . '/vendor/autoload.php';
}

// Load Environment Variables (.env)
$_envFile = ROOT . '/.env';
// Ưu tiên load .env.testing nếu có
if (file_exists(ROOT . '/.env.testing')) {
    $_envFile = ROOT . '/.env.testing';
}

if (file_exists($_envFile)) {
    foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        if (str_starts_with(trim($_line), '#') || !str_contains($_line, '=')) {
            continue;
        }
        [$_k, $_v] = explode('=', $_line, 2);
        $_k = trim($_k);
        $_v = trim(trim($_v), '"\'');
        if (!isset($_ENV[$_k])) {
            $_ENV[$_k] = $_v;
            putenv("{$_k}={$_v}");
        }
    }
}

// Custom PSR-4 Autoloader cho kiến trúc hiện tại
spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\Controllers\\' => APP_PATH . '/controllers/',
        'App\\Models\\'      => APP_PATH . '/models/',
        'App\\Services\\'    => APP_PATH . '/services/',
        'Core\\'             => CORE_PATH . '/',
        'Config\\'           => CONFIG_PATH . '/',
        'Tests\\'            => ROOT . '/tests/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        
        if (file_exists($file)) {
            require $file;
        }
    }
});

// Giả lập Session để tránh lỗi các hàm thao tác với $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    $_SESSION = [];
}
