<?php

/**
 * Demo data for the dormitory (KTX) side, on top of seed-test-marketplace.php:
 * staff accounts with RBAC roles, fee categories, November invoices in every
 * payment state, repair tickets at every stage, rental kiosks (let / open /
 * pending application), pending residence applications with free beds to
 * assign, and announcements. Everything goes through the domain models, so
 * the data obeys the same rules as the application.
 *
 *   TEST_ACCOUNTS_PASSWORD=... php scripts/seed-demo-ktx.php --database=NAME --confirm=NAME
 *
 * Refuses production, an unconfirmed database, a database without the test
 * marketplace accounts, and a second run.
 */

declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP_PATH', ROOT . '/app');
define('CORE_PATH', ROOT . '/core');
define('CONFIG_PATH', ROOT . '/config');
spl_autoload_register(static function (string $class): void {
    $map = ['App\\Models\\' => APP_PATH . '/models/', 'App\\Services\\' => APP_PATH . '/services/',
        'Core\\' => CORE_PATH . '/', 'Config\\' => CONFIG_PATH . '/'];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require $file;
            }
        }
    }
});

use App\Models\AccessControl;
use App\Models\ResidenceApplication;
use App\Models\ResidenceFinance;
use App\Models\ResidenceNotice;
use App\Models\ResidenceOperations;
use Config\Database;

$opts = getopt('', ['database:', 'confirm:']);
$target = (string)($opts['database'] ?? '');
if ($target === '' || ($opts['confirm'] ?? '') !== $target) {
    fwrite(STDERR, "Usage: php scripts/seed-demo-ktx.php --database=NAME --confirm=NAME\n");
    exit(2);
}
if ((getenv('APP_ENV') ?: '') === 'production') {
    fwrite(STDERR, "Refusing to seed demo data in production.\n");
    exit(2);
}
$pdo = Database::getInstance();
if ((string)$pdo->query('SELECT DATABASE()')->fetchColumn() !== $target) {
    fwrite(STDERR, "Connected database does not match --database=$target. Check DB_NAME.\n");
    exit(2);
}
$scalar = static function (string $sql, array $params = []) use ($pdo) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
};
if ((int)$scalar("SELECT COUNT(*) FROM users WHERE email LIKE '%.example.edu' OR email LIKE '%@example.com'") < 20) {
    fwrite(STDERR, "Run scripts/seed-test-marketplace.php first (test accounts not found).\n");
    exit(2);
}
if ((int)$scalar("SELECT COUNT(*) FROM users WHERE email = 'quanly.ktx@example.com'") > 0) {
    fwrite(STDERR, "KTX demo data already present. Refusing to duplicate.\n");
    exit(2);
}

$password = getenv('TEST_ACCOUNTS_PASSWORD') ?: ('Svm-' . bin2hex(random_bytes(6)) . '!');
$adminId = (int)$scalar("SELECT id FROM users WHERE role = 'admin' ORDER BY id LIMIT 1");
$userId = static fn(string $email): int => (int)$scalar('SELECT id FROM users WHERE email = ?', [$email]);
$finance = new ResidenceFinance();
$ops = new ResidenceOperations();
$access = new AccessControl();

// ---- Staff accounts (RBAC demo) ----
$staff = [];
foreach ([['Trần Văn Bảo', 'quanly.ktx@example.com', 'dorm_manager'],
    ['Lê Minh Kỹ', 'kythuat.ktx@example.com', 'service_staff']] as [$name, $email, $role]) {
    $pdo->prepare("INSERT INTO users (name, email, password, role, is_verified, created_at)
                   VALUES (?, ?, ?, 'student', 1, DATE_SUB(NOW(), INTERVAL 90 DAY))")
        ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    $staff[$role] = (int)$pdo->lastInsertId();
    $access->assignRole($staff[$role], $role, $adminId);
}
$manager = $staff['dorm_manager'];

// ---- Residents with their contract and room ----
$residents = $pdo->query(
    "SELECT u.id, u.name, c.id AS contract_id, bed.room_id, r.room_number
     FROM residence_contracts c JOIN users u ON u.id = c.user_id
     JOIN residence_bed_allocations a ON a.contract_id = c.id AND a.status = 'active'
     JOIN residence_beds bed ON bed.id = a.bed_id JOIN residence_rooms r ON r.id = bed.room_id
     WHERE c.status = 'active' ORDER BY u.id"
)->fetchAll(PDO::FETCH_ASSOC);

// ---- Fees and November invoices in every state ----
$fee = [];
foreach (['Tiền phòng' => 'Phí lưu trú theo tháng, phòng tiêu chuẩn 4 người.',
    'Điện vượt định mức' => 'Phần điện vượt 60 số/phòng/tháng, chia đều theo người.',
    'Nước vượt định mức' => 'Phần nước vượt 4 khối/phòng/tháng.',
    'Phí gửi xe' => 'Gửi xe máy tại bãi KTX theo tháng.'] as $name => $desc) {
    $fee[$name] = $finance->saveFeeCategory($name, $desc, true, $manager);
}
$states = ['paid', 'paid', 'paid', 'paid', 'paid', 'paid', 'partial', 'partial', 'pending', 'pending', 'open', 'open'];
$invoiceCount = 0;
foreach ($residents as $i => $r) {
    $electric = [32000, 45000, 18000, 60000, 27000, 51000][$i % 6];
    $lines = [
        ['feeCategoryId' => $fee['Tiền phòng'], 'description' => 'Tiền phòng tháng 11/2026', 'quantity' => 1, 'unitPrice' => 450000],
        ['feeCategoryId' => $fee['Điện vượt định mức'], 'description' => 'Điện vượt định mức tháng 10', 'quantity' => 1, 'unitPrice' => $electric],
    ];
    if ($i % 3 === 0) {
        $lines[] = ['feeCategoryId' => $fee['Phí gửi xe'], 'description' => 'Gửi xe máy tháng 11', 'quantity' => 1, 'unitPrice' => 60000];
    }
    $total = array_sum(array_map(static fn(array $l): int => $l['quantity'] * $l['unitPrice'], $lines));
    $invoiceId = $finance->createInvoice((int)$r['id'], (int)$r['contract_id'], 'Hóa đơn lưu trú tháng 11/2026',
        '2026-11-01', '2026-11-15', $lines, $manager);
    $invoiceCount++;
    $state = $states[$i] ?? 'open';
    if ($state === 'paid' || $state === 'partial') {
        $amount = $state === 'paid' ? $total : 300000;
        $pay = $finance->recordPayment($invoiceId, (int)$r['id'], $amount, 'bank_transfer', 'CK' . (1100 + $i), null, (int)$r['id']);
        $finance->reviewPayment($pay, $manager, 'confirmed');
    } elseif ($state === 'pending') {
        $finance->recordPayment($invoiceId, (int)$r['id'], $total, 'bank_transfer', 'CK' . (1100 + $i), 'Đã chuyển khoản, nhờ BQL xác nhận.', (int)$r['id']);
    }
}

// ---- Repair tickets at every stage ----
$tickets = [
    ['repair', 'Vòi nước lavabo bị rò', 'Vòi nước lavabo rò rỉ liên tục, nước chảy ra sàn.', 'high', 'submitted', null, null],
    ['repair', 'Ổ cắm gần giường không có điện', 'Ổ cắm cạnh giường số 1 mất điện từ hôm qua.', 'normal', 'assigned', 'service_staff', 'Hẹn kiểm tra chiều nay.'],
    ['repair', 'Quạt trần kêu to', 'Quạt trần phát ra tiếng kêu lớn khi bật số 3.', 'normal', 'in_progress', 'service_staff', 'Đã thay bạc đạn, đang chạy thử.'],
    ['repair', 'Khóa tủ cá nhân bị kẹt', 'Không mở được khóa tủ cá nhân.', 'low', 'resolved', 'service_staff', 'Đã thay ổ khóa mới.'],
    ['service', 'Xin thêm móc phơi đồ', 'Phòng cần thêm 2 móc phơi đồ ở ban công.', 'low', 'closed', 'service_staff', 'Đã lắp.'],
    ['feedback', 'Wifi tầng 3 yếu buổi tối', 'Sau 21h wifi tầng 3 rất chậm, khó học online.', 'normal', 'submitted', null, null],
];
foreach ($tickets as $k => [$type, $title, $desc, $priority, $status, $assignee, $note]) {
    $r = $residents[$k % count($residents)];
    $id = $ops->createServiceRequest((int)$r['id'], $type, $title, $desc, (int)$r['room_id'], $priority);
    if ($status !== 'submitted') {
        $ops->updateServiceRequest($id, $assignee ? $staff[$assignee] : null, $status, $note);
    }
}

// ---- Rental kiosks: one let, one with a pending application, one open ----
$spaces = [];
foreach ([['KT-A01', 'Ki-ốt photocopy sảnh tòa A', 'Sảnh tầng trệt tòa A', 12, 2500000, 'Gần thang máy, có ổ điện 3 pha.'],
    ['KT-S02', 'Quầy cà phê sân KTX', 'Sân trung tâm KTX', 18, 3500000, 'Có mái che, phù hợp quầy nước.'],
    ['KT-B03', 'Góc sửa xe đạp', 'Cạnh bãi xe tòa B', 8, 1200000, 'Không gian mở, gần nguồn nước.']] as [$code, $name, $loc, $area, $price, $desc]) {
    $spaces[$code] = $ops->saveResource('rental-spaces', ['space_code' => $code, 'name' => $name, 'location' => $loc,
        'area_sqm' => $area, 'monthly_price' => $price, 'description' => $desc, 'status' => 'available']);
}
$letTo = $userId('ngochan.chau@example.com');
$app = $ops->submitRentalApplication($letTo, $spaces['KT-A01'], '2026-12-01', '2027-05-31', 'Photocopy Hân', 'In ấn tài liệu cho sinh viên.');
$ops->reviewRentalApplication($app, $manager, 'approve', 'Hồ sơ đầy đủ.');
$ops->submitRentalApplication($userId('anhthu.kieu@example.com'), $spaces['KT-S02'], '2027-01-01', '2027-06-30', 'Cà phê Thư', 'Bán nước và bánh mì buổi sáng.');

// ---- Pending residence applications + free beds to assign during the demo ----
$freeBeds = 0;
foreach (['A203', 'B110'] as $room) {
    $roomId = (int)$scalar('SELECT id FROM residence_rooms WHERE room_number = ?', [$room]);
    $existing = (int)$scalar('SELECT COUNT(*) FROM residence_beds WHERE room_id = ?', [$roomId]);
    for ($n = $existing + 1; $n <= 4; $n++) {
        $ops->saveResource('beds', ['room_id' => $roomId, 'bed_number' => (string)$n, 'status' => 'available']);
        $freeBeds++;
    }
}
$applications = new ResidenceApplication();
foreach (['vanhau.do@example.com' => 'Nhà xa, muốn ở KTX gần trường.', 'tuankiet.mai@example.com' => 'Đăng ký ở ghép, ưu tiên tòa A.'] as $email => $note) {
    $applications->submit($userId($email), '2026-12-01', '2027-06-30', 'standard', $note);
}

// ---- Announcements and events ----
$notices = new ResidenceNotice();
$notices->create('announcement', 'Tạm ngưng cấp điện tòa B ngày 05/12', 'Điện lực bảo trì trạm biến áp: tòa B mất điện từ 8:00 đến 11:00 ngày 05/12/2026. Sinh viên sạc sẵn thiết bị cần thiết.', 'residents', null, null, $manager);
$notices->create('event', 'Ngày hội trao đổi đồ cũ cuối kỳ', 'Sân KTX, 17:00–21:00 ngày 20/12/2026. Mang đồ không dùng đến để trao đổi; đăng tin trước trên SinhVienMarket để được giữ bàn.', 'all', null, null, $manager);
$notices->create('announcement', 'Kiểm tra phòng cháy chữa cháy định kỳ', 'Ban quản lý kiểm tra PCCC tất cả các phòng vào thứ Bảy 06/12/2026. Sinh viên dọn lối đi và tháo các thiết bị điện không được phép.', 'residents', null, null, $manager);

printf("[%s] staff 2 (dorm_manager, service_staff), %d invoices, %d tickets, 3 kiosks (1 let, 1 pending), 2 pending residence applications, %d free beds, 3 notices.\n",
    $target, $invoiceCount, count($tickets), $freeBeds);
if (!getenv('TEST_ACCOUNTS_PASSWORD')) {
    fwrite(STDOUT, "Staff password (shown once, test databases only): $password\n");
}
