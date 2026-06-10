<?php
/**
 * Front Controller - Điểm vào duy nhất của ứng dụng
 * Mọi request đều được .htaccess chuyển về đây
 */

define('ROOT', __DIR__);
define('APP_PATH', ROOT . '/app');
define('CORE_PATH', ROOT . '/core');
define('CONFIG_PATH', ROOT . '/config');

// ─── HTTP Security Headers ─────────────────────────────────────────────────
header('X-Frame-Options: SAMEORIGIN');                          // Chống Clickjacking
header('X-Content-Type-Options: nosniff');                      // Chống MIME-type sniffing
header('Referrer-Policy: strict-origin-when-cross-origin');     // Giới hạn thông tin Referrer
header('X-XSS-Protection: 1; mode=block');                      // Legacy XSS filter (trình duyệt cũ)
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()'); // Tắt API nhạy cảm

// ─── Content Security Policy (CSP) ─────────────────────────────────────────
// Chặn XSS, data injection và tài nguyên không được ủy quyền
// 'unsafe-inline' & 'unsafe-eval' được giữ lại tạm thời cho Alpine.js + inline scripts
// TODO: Giai đoạn sau có thể thắt chặt bằng nonce-based CSP
header(implode('', [
    "Content-Security-Policy: ",
    "default-src 'self'; ",
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' ",
        "https://cdn.jsdelivr.net ",
        "https://cdn.tailwindcss.com ",
        "https://js.pusher.com ",
        "https://unpkg.com; ",
    "style-src 'self' 'unsafe-inline' ",
        "https://cdn.jsdelivr.net ",
        "https://fonts.googleapis.com; ",
    "font-src 'self' ",
        "https://cdn.jsdelivr.net ",
        "https://fonts.gstatic.com; ",
    "img-src 'self' data: blob: ",
        "https://ui-avatars.com ",
        "https://lh3.googleusercontent.com ",
        "https://api.qrserver.com ",
        "https://img.vietqr.io ",
        "https://cdn.jsdelivr.net; ",
    "connect-src 'self' ",
        "wss://ws-ap1.pusher.com ",
        "ws://ws-ap1.pusher.com ",
        "https://sockjs-ap1.pusher.com; ",
    "frame-src 'none'; ",
    "object-src 'none'; ",
    "base-uri 'self'; ",
    "form-action 'self';",
]));

// ─── HSTS (Strict-Transport-Security) ─────────────────────────────────────
// Ép trình duyệt luôn dùng HTTPS; chỉ kích hoạt khi đang chạy trên HTTPS
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// ─── Secure Session Configuration ──────────────────────────────────────────
ini_set('session.cookie_httponly', '1');   // Ngăn JS đọc session cookie
ini_set('session.use_strict_mode', '1');   // Từ chối session ID không do server cấp
ini_set('session.cookie_samesite', 'Lax'); // Chống CSRF qua cross-site requests
// Bật Secure flag khi chạy trên HTTPS thực tế
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
    ini_set('session.cookie_secure', '1');
}
// Tăng entropy của session ID
ini_set('session.sid_length', '48');
ini_set('session.sid_bits_per_character', '6');

// Khởi động session
session_start();

// ─── Tích hợp thư viện Composer (Google API, v.v) ───────────────────────────
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// ─── Load .env sớm để APP_URL có sẵn cho mọi view ────────────────────────────
$_envFile = __DIR__ . '/.env';
if (file_exists($_envFile)) {
    // Local: đọc từ file .env
    foreach (file($_envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        if (str_starts_with(trim($_line), '#') || !str_contains($_line, '='))
            continue;
        [$_k, $_v] = explode('=', $_line, 2);
        $_k = trim($_k);
        $_v = trim(trim($_v), '"\'');
        if (!isset($_ENV[$_k])) {
            $_ENV[$_k] = $_v;
            putenv("{$_k}={$_v}");
        }
    }
} else {
    // Railway: env vars được inject vào process, không có file .env
    // Sync $_SERVER → $_ENV để APP_URL, PUSHER_*, GEMINI_API_KEY... hoạt động qua $_ENV
    foreach ($_SERVER as $_k => $_v) {
        if (!isset($_ENV[$_k]) && is_string($_v)) {
            $_ENV[$_k] = $_v;
        }
    }
}
unset($_envFile, $_line, $_k, $_v);

// Autoloader đơn giản theo PSR-4 style
spl_autoload_register(function (string $class): void {
    // Map các namespace → thư mục
    $prefixes = [
        'App\\Controllers\\' => APP_PATH . '/controllers/',
        'App\\Models\\'      => APP_PATH . '/models/',
        'App\\Services\\'    => APP_PATH . '/services/',
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

// ─── Global Error Handler (Phase 13) ───────────────────────────────────────
use Core\ErrorHandler;
ErrorHandler::register();

// Load Router và khởi chạy ứng dụng
use Core\Router;

$router = new Router();

// ─── Định nghĩa tất cả routes ────────────────────────────────────────────────

// Auth
$router->get('', 'Home', 'index'); // Trang chủ
$router->get('login-role', 'Auth', 'loginRole');
$router->get('login', 'Auth', 'loginForm');
$router->post('login', 'Auth', 'login');
$router->get('admin-login', 'Auth', 'adminLoginForm');
$router->post('admin-login', 'Auth', 'adminLogin');
$router->get('register', 'Auth', 'registerForm');
$router->post('register', 'Auth', 'register');
$router->get('logout', 'Auth', 'logout');
$router->get('account-locked', 'Auth', 'accountLocked'); // Trang thông báo khóa tài khoản

// OTP & Password Reset
$router->get('verify-otp', 'Otp', 'verifyOtpForm');
$router->post('verify-otp', 'Otp', 'verifyOtp');
$router->get('resend-otp', 'Otp', 'resendOtp');
$router->get('forgot-password', 'PasswordReset', 'forgotPasswordForm');
$router->post('forgot-password', 'PasswordReset', 'forgotPassword');
$router->get('reset-password', 'PasswordReset', 'resetPasswordForm');
$router->post('reset-password', 'PasswordReset', 'resetPassword');

// Google OAuth
$router->get('auth/google',          'GoogleAuth', 'redirectToGoogle');
$router->get('auth/google/callback', 'GoogleAuth', 'callback');

// ─── Coins & Bump (Feature 2) ─────────────────────────────────────────────
$router->get('rewards',        'Coin', 'index');
$router->post('coins/checkin', 'Coin', 'checkIn');
$router->post('coins/bump',    'Coin', 'bump');

// Leaderboard (Feature v1.6.0)
$router->get('leaderboard', 'Leaderboard', 'index');


$router->get('dashboard', 'Home', 'dashboard'); // Student dashboard

// ─── Profile ──────────────────────────────────────────────────────────────────
$router->get('profile', 'Profile', 'show');
$router->get('users/avatar', 'Profile', 'avatar'); // ?id= — avatar từ DB (public)
$router->post('profile/update', 'Profile', 'update');
$router->post('profile/password', 'Profile', 'changePassword');
$router->post('profile/avatar', 'Profile', 'uploadAvatar');
$router->post('profile/bank', 'Profile', 'saveBankAccount');


// Products
$router->get('products', 'Product', 'index');
$router->get('products/create', 'Product', 'createForm');
$router->post('products/create', 'Product', 'create');
$router->post('products/suggest-price', 'Product', 'suggestPrice');
$router->get('products/show', 'Product', 'show'); // ?id=
$router->get('products/image', 'Product', 'image'); // ?id= — phục vụ ảnh từ DB
$router->get('products/my', 'Product', 'myProducts');
$router->post('products/delete', 'Product', 'delete');

// Auction / Transaction
$router->post('transactions/checkout', 'Transaction', 'checkout');
$router->get('transactions/bank', 'Transaction', 'bank');
$router->post('transactions/bank-confirm', 'Transaction', 'bankConfirm');
$router->post('transactions/bank-received', 'Transaction', 'bankReceived');
$router->get('transactions/zalopay', 'Transaction', 'zalopay');
$router->get('transactions/zalopay-return', 'Transaction', 'zalopayReturn');
$router->post('transactions/zalopay-ipn', 'Transaction', 'zalopayIpn');
// MoMo Sandbox
$router->get('transactions/momo', 'Transaction', 'momo');
$router->get('transactions/momo-return', 'Transaction', 'momoReturn');
$router->post('transactions/momo-ipn', 'Transaction', 'momoIpn');

$router->get('transactions/history', 'Transaction', 'history');
$router->get('transactions/proof', 'Transaction', 'proof'); // ?id= — biên lai từ DB (mua/bán/admin)
$router->post('transactions/update-status', 'Transaction', 'updateStatus');

// API (JSON responses cho polling realtime)
$router->get('api/products/search', 'Product', 'apiSearch');
$router->get('api/auction/price', 'Auction', 'apiPrice');

// Admin
$router->get('admin', 'Admin', 'dashboard');
$router->get('admin/users', 'AdminUser', 'users');
$router->get('admin/users/detail', 'AdminUser', 'userDetail');
$router->post('admin/users/toggle', 'AdminUser', 'toggleUser');
$router->post('admin/users/strike', 'AdminUser', 'strikeUser');
$router->get('admin/users/violations', 'AdminUser', 'userViolations');
$router->get('admin/products', 'AdminProduct', 'products');
$router->post('admin/products/approve', 'AdminProduct', 'approveProduct');
$router->post('admin/products/reject', 'AdminProduct', 'rejectProduct');
$router->post('admin/products/delete', 'AdminProduct', 'deleteProduct');
$router->get('admin/categories', 'AdminProduct', 'categories');
$router->post('admin/categories/store', 'AdminProduct', 'storeCategory');
$router->post('admin/categories/update', 'AdminProduct', 'updateCategory');
$router->post('admin/categories/delete', 'AdminProduct', 'deleteCategory');

// Admin Banners
$router->get('banners/image', 'AdminProduct', 'bannerImage'); // ?id= — phục vụ ảnh banner từ DB (public)
$router->get('admin/banners', 'AdminProduct', 'banners');
$router->post('admin/banners/store', 'AdminProduct', 'storeBanner');
$router->post('admin/banners/update', 'AdminProduct', 'updateBanner');
$router->post('admin/banners/toggle', 'AdminProduct', 'toggleBanner');
$router->post('admin/banners/delete', 'AdminProduct', 'deleteBanner');

$router->get('admin/reports', 'AdminReport', 'reports');
$router->get('admin/system-reports', 'AdminReport', 'systemReports');
$router->get('admin/reports/evidence', 'AdminReport', 'evidence'); // ?id= — ảnh tố cáo từ DB (admin)
$router->post('admin/system-reports/resolve', 'AdminReport', 'resolveReport');
$router->get('admin/audit-log', 'Admin', 'auditLog');

// Export
$router->get('admin/export', 'Admin', 'exportData');

// Admin Ratings
$router->get('admin/ratings', 'AdminReport', 'ratings');
$router->post('admin/ratings/toggle', 'AdminReport', 'toggleRating');

// Admin Giveaways Phase 11.4
$router->get('admin/giveaways', 'AdminGiveaway', 'giveaways');
$router->post('admin/giveaways/store', 'AdminGiveaway', 'storeGiveaway');
$router->post('admin/giveaways/delete', 'AdminGiveaway', 'deleteGiveaway');
$router->get('admin/giveaway_spin', 'AdminGiveaway', 'spinGiveaway');
$router->post('admin/giveaway_spin_api', 'AdminGiveaway', 'spinGiveawayApi');

// User Giveaways API
$router->post('api/giveaways/join', 'Giveaway', 'join');
$router->get('giveaways/image', 'Giveaway', 'image'); // ?id= — ảnh giveaway từ DB (public)

// ─── Chat ────────────────────────────────────────────────────────────────────
$router->get('chat', 'Chat', 'index');
$router->get('chat/show', 'Chat', 'show');
$router->post('chat/start', 'Chat', 'start');
$router->post('chat/send', 'Chat', 'send');
$router->get('chat/poll', 'Chat', 'apiPoll');
$router->get('api/chat/unread', 'Chat', 'apiUnreadCount');
// API-prefixed aliases (chuẩn hóa Phase 14)
$router->post('api/chat/send', 'Chat', 'send');
$router->get('api/chat/poll', 'Chat', 'apiPoll');
$router->post('api/chat/offer', 'Chat', 'sendOffer');
$router->post('api/chat/offer/respond', 'Chat', 'respondOffer');

// ─── Notifications ───────────────────────────────────────────────────────────
$router->get('notifications', 'Notification', 'index');
$router->get('api/notifications/unread', 'Notification', 'apiUnread');
$router->post('notifications/mark-read', 'Notification', 'markRead');

// ─── Ratings ─────────────────────────────────────────────────────────────────
$router->post('ratings/create', 'Rating', 'create');
$router->get('users/profile', 'Rating', 'profile');

// ─── Reports (Tố cáo) ────────────────────────────────────────────────────────
$router->post('reports/store', 'Report', 'store');

// ─── Wishlist ─────────────────────────────────────────────────────────────────
$router->post('wishlist/toggle', 'Wishlist', 'toggle');
$router->get('wishlist', 'Wishlist', 'index');

// ─── Dispatch ────────────────────────────────────────────────────────────────
$router->dispatch();
