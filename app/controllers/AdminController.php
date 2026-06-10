<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use Core\Flash;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\AuditLog;
use App\Models\Report;
use App\Models\Rating;
use App\Models\Banner;
use App\Models\Giveaway;
use App\Services\NotificationService;

class AdminController extends Controller
{
    private User $userModel;
    private Product $productModel;
    private Category $categoryModel;
    private Transaction $txModel;
    private AuditLog $auditModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->txModel = new Transaction();
        $this->auditModel = new AuditLog();
    }

    // ─── Dashboard ───────────────────────────────────────────────────────

    public function dashboard(): void
    {
        Middleware::requireAdmin();

        $stats = [
            'total_users' => $this->userModel->countAll(),
            'active_products' => $this->productModel->countActive(),
            'pending_count' => count($this->productModel->getPending()),
            'tx_today' => $this->txModel->countToday(),
            'recent_tx' => $this->txModel->getAll('', '', 5),
            'recent_products' => $this->productModel->getPending(),
        ];

        $this->render('admin/dashboard', ['title' => 'Dashboard', 'stats' => $stats], 'admin');
    }

    // ─── Quản lý người dùng ──────────────────────────────────────────────

    public function auditLog(): void
    {
        Middleware::requireAdmin();
        $logs = $this->auditModel->getAll(300);
        $this->render('admin/audit_log', [
            'title' => 'Nhật ký hành động Admin',
            'logs' => $logs,
        ], 'admin');
    }

    // ─── PHẦN 11.4: QUẢN LÝ GIVEAWAYS ─────────────────────────
    public function exportData(): void
    {
        Middleware::requireAdmin();

        // NEW-003 fix: export dùng GET → validate token qua $_GET thay vì $_POST
        // Token phải được đính kèm vào export link từ view (csrfToken())
        $token = $_GET['_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            Flash::set('danger', 'Yêu cầu export không hợp lệ. Vui lòng thử lại.');
            $this->redirect('admin/reports');
            return;
        }

        $type = $_GET['type'] ?? '';
        // NEW-003 fix: sanitize $type để tránh header injection
        $type = preg_replace('/[^a-z_]/', '', $type);

        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;

        $filename = "export_{$type}_" . date('Ymd_His') . ".csv";

        // QUAN TRỌNG: tắt hiển thị lỗi để warning/deprecated (vd fputcsv trên PHP 8.5)
        // KHÔNG bị ghi lẫn vào file CSV làm hỏng file.
        ini_set('display_errors', '0');
        while (ob_get_level() > 0) { ob_end_clean(); }

        // Cấu hình header để tải file CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        // Mở buffer output
        $output = fopen('php://output', 'w');
        
        // Thêm BOM (Byte Order Mark) để Excel nhận diện được UTF-8 tiếng Việt
        fputs($output, "\xEF\xBB\xBF");

        // Ghi dòng thông tin bộ lọc
        if ($from || $to) {
            $this->csvRow($output, ['Báo cáo từ ngày:', $from ?: 'Không giới hạn', 'Đến ngày:', $to ?: 'Không giới hạn']);
            $this->csvRow($output, []); // Dòng trống
        }
        
        if ($type === 'users') {
            // Header cột
            $this->csvRow($output, ['ID', 'Họ tên', 'Email', 'Vai trò', 'Trạng thái khóa', 'Xác thực', 'Số gậy (Strikes)', 'Ngày đăng ký']);
            
            $users = $this->userModel->getAllForExport($from, $to);
            $totalUsers = 0;
            $newUsers = 0;

            foreach ($users as $u) {
                $totalUsers++;
                if ($u['role'] === 'student') $newUsers++;

                $this->csvRow($output, [
                    $u['id'],
                    $u['name'],
                    $u['email'],
                    $u['role'] === 'admin' ? 'Admin' : 'Sinh viên',
                    $u['is_locked'] ? 'Bị khóa' : 'Hoạt động',
                    $u['is_verified'] ? 'Đã xác thực' : 'Chưa',
                    $u['strike_count'] ?? 0,
                    $u['created_at']
                ]);
            }

            $this->csvRow($output, []);
            $this->csvRow($output, ['--- THỐNG KÊ TỔNG HỢP ---']);
            $this->csvRow($output, ['Tổng số tài khoản:', $totalUsers]);
            $this->csvRow($output, ['Số lượng sinh viên:', $newUsers]);

        } elseif ($type === 'transactions') {
            // Header cột
            $this->csvRow($output, ['ID Giao Dịch', 'Tên Sản Phẩm', 'Người Mua', 'Người Bán', 'Giá Trị', 'Phương Thức', 'Trạng Thái', 'Ngày Giao Dịch']);
            
            $transactions = $this->txModel->getAll($from ?: '', $to ?: '');
            $totalAmount = 0;
            $successCount = 0;

            foreach ($transactions as $tx) {
                if (in_array($tx['order_status'] ?? '', ['delivered', 'received', 'completed'])) {
                    $totalAmount += (int)$tx['amount'];
                    $successCount++;
                }

                $this->csvRow($output, [
                    $tx['id'],
                    $tx['product_title'] ?? 'N/A',
                    $tx['buyer_name'] ?? 'N/A',
                    $tx['seller_name'] ?? 'N/A',
                    $tx['amount'],
                    $tx['payment_method'],
                    $tx['order_status'] ?? $tx['status'] ?? 'pending',
                    $tx['created_at']
                ]);
            }

            $this->csvRow($output, []);
            $this->csvRow($output, ['--- THỐNG KÊ TỔNG HỢP ---']);
            $this->csvRow($output, ['Tổng số giao dịch:', count($transactions)]);
            $this->csvRow($output, ['Số giao dịch thành công:', $successCount]);
            $this->csvRow($output, ['Tổng doanh thu (VNĐ):', $totalAmount]);

        } elseif ($type === 'products') {
            // Header cột
            $this->csvRow($output, ['ID', 'Tên Sản Phẩm', 'Danh Mục', 'Người Bán', 'Loại', 'Giá (VNĐ)', 'Tình Trạng', 'Trạng Thái', 'Ngày Đăng']);
            
            $products = $this->productModel->getAllForExport($from, $to);
            $totalProducts = 0;
            $soldProducts = 0;
            
            foreach ($products as $p) {
                $totalProducts++;
                if ($p['status'] === 'sold') $soldProducts++;
                
                $this->csvRow($output, [
                    $p['id'],
                    $p['title'],
                    $p['category_name'] ?? 'N/A',
                    $p['seller_name'] ?? 'N/A',
                    $p['type'],
                    $p['price'] ?? 0,
                    $p['condition'] ?? 'used',
                    $p['status'],
                    $p['created_at']
                ]);
            }
            
            $this->csvRow($output, []);
            $this->csvRow($output, ['--- THỐNG KÊ TỔNG HỢP ---']);
            $this->csvRow($output, ['Tổng số bài đăng:', $totalProducts]);
            $this->csvRow($output, ['Số bài đã bán:', $soldProducts]);

        } else {
            $this->csvRow($output, ['Loại dữ liệu không hợp lệ']);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Ghi 1 dòng CSV — truyền tham số $escape tường minh để tránh deprecation
     * của fputcsv() trên PHP 8.4+ (nếu không sẽ in cảnh báo lẫn vào file).
     */
    private function csvRow($handle, array $fields): void
    {
        fputcsv($handle, $fields, ',', '"', '');
    }

    // ─── Quản lý Đánh Giá & Bình Luận (Feature 4) ──────────────────────────

}
