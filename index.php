<?php
/**
 * Front Controller - Điểm vào duy nhất của ứng dụng
 * Mọi request đều được .htaccess chuyển về đây
 */

define('ROOT', __DIR__);
define('APP_PATH', ROOT . '/app');
define('CORE_PATH', ROOT . '/core');
define('CONFIG_PATH', ROOT . '/config');

// Khởi động session
session_start();

// ─── Load .env sớm để APP_URL có sẵn cho mọi view ────────────────────────────
$_envFile = __DIR__ . '/.env';
if (file_exists($_envFile)) {
    foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        if (str_starts_with(trim($_line), '#') || !str_contains($_line, '=')) continue;
        [$_k, $_v] = explode('=', $_line, 2);
        $_k = trim($_k); $_v = trim(trim($_v), '"\'');
        if (!isset($_ENV[$_k])) { $_ENV[$_k] = $_v; putenv("{$_k}={$_v}"); }
    }
}
unset($_envFile, $_line, $_k, $_v);

// Autoloader đơn giản theo PSR-4 style
spl_autoload_register(function (string $class): void {
    // Map các namespace → thư mục
    $prefixes = [
        'App\\Controllers\\' => APP_PATH . '/controllers/',
        'App\\Models\\'      => APP_PATH . '/models/',
        'Core\\'             => CORE_PATH . '/',
        'Config\\'           => CONFIG_PATH . '/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
            $file = $baseDir . $relative . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// Load Router và khởi chạy ứng dụng
use Core\Router;

$router = new Router();

// ─── Định nghĩa tất cả routes ────────────────────────────────────────────────

// Auth
$router->get('',              'Auth', 'index');
$router->get('login',         'Auth', 'loginForm');
$router->post('login',        'Auth', 'login');
$router->get('register',      'Auth', 'registerForm');
$router->post('register',     'Auth', 'register');
$router->get('logout',        'Auth', 'logout');

// Products
$router->get('products',             'Product', 'index');
$router->get('products/create',      'Product', 'createForm');
$router->post('products/create',     'Product', 'create');
$router->get('products/show',        'Product', 'show');    // ?id=
$router->get('products/my',          'Product', 'myProducts');
$router->post('products/delete',     'Product', 'delete');

// Auction / Transaction
$router->post('auction/buy',         'Auction', 'buy');
$router->get('transactions/history', 'Transaction', 'history');

// API (JSON responses cho polling realtime)
$router->get('api/auction/price',    'Auction', 'apiPrice');

// Admin
$router->get('admin',                'Admin', 'dashboard');
$router->get('admin/users',          'Admin', 'users');
$router->post('admin/users/toggle',  'Admin', 'toggleUser');
$router->get('admin/products',       'Admin', 'products');
$router->post('admin/products/approve',  'Admin', 'approveProduct');
$router->post('admin/products/reject',   'Admin', 'rejectProduct');
$router->post('admin/products/delete',   'Admin', 'deleteProduct');
$router->get('admin/categories',     'Admin', 'categories');
$router->post('admin/categories/store',  'Admin', 'storeCategory');
$router->post('admin/categories/update', 'Admin', 'updateCategory');
$router->post('admin/categories/delete', 'Admin', 'deleteCategory');
$router->get('admin/reports',        'Admin', 'reports');
$router->get('admin/audit-log',      'Admin', 'auditLog');

// ─── Dispatch ────────────────────────────────────────────────────────────────
$router->dispatch();
