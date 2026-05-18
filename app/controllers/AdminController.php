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
        
        // Cấu hình header để tải file CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        // Mở buffer output
        $output = fopen('php://output', 'w');
        
        // Thêm BOM (Byte Order Mark) để Excel nhận diện được UTF-8 tiếng Việt
        fputs($output, "\xEF\xBB\xBF");

        // Ghi dòng thông tin bộ lọc
        if ($from || $to) {
            fputcsv($output, ['Báo cáo từ ngày:', $from ?: 'Không giới hạn', 'Đến ngày:', $to ?: 'Không giới hạn']);
            fputcsv($output, []); // Dòng trống
        }
        
        if ($type === 'users') {
            // Header cột
            fputcsv($output, ['ID', 'Họ tên', 'Email', 'Vai trò', 'Trạng thái khóa', 'Xác thực', 'Số gậy (Strikes)', 'Ngày đăng ký']);
            
            $users = $this->userModel->getAllForExport($from, $to);
            $totalUsers = 0;
            $newUsers = 0;

            foreach ($users as $u) {
                $totalUsers++;
                if ($u['role'] === 'student') $newUsers++;

                fputcsv($output, [
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

            fputcsv($output, []);
            fputcsv($output, ['--- THỐNG KÊ TỔNG HỢP ---']);
            fputcsv($output, ['Tổng số tài khoản:', $totalUsers]);
            fputcsv($output, ['Số lượng sinh viên:', $newUsers]);

        } elseif ($type === 'transactions') {
            // Header cột
            fputcsv($output, ['ID Giao Dịch', 'Tên Sản Phẩm', 'Người Mua', 'Người Bán', 'Giá Trị', 'Phương Thức', 'Trạng Thái', 'Ngày Giao Dịch']);
            
            $transactions = $this->txModel->getAll($from ?: '', $to ?: '');
            $totalAmount = 0;
            $successCount = 0;

            foreach ($transactions as $tx) {
                if (in_array($tx['order_status'] ?? '', ['delivered', 'received', 'completed'])) {
                    $totalAmount += (int)$tx['amount'];
                    $successCount++;
                }

                fputcsv($output, [
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

            fputcsv($output, []);
            fputcsv($output, ['--- THỐNG KÊ TỔNG HỢP ---']);
            fputcsv($output, ['Tổng số giao dịch:', count($transactions)]);
            fputcsv($output, ['Số giao dịch thành công:', $successCount]);
            fputcsv($output, ['Tổng doanh thu (VNĐ):', $totalAmount]);

        } elseif ($type === 'products') {
            // Header cột
            fputcsv($output, ['ID', 'Tên Sản Phẩm', 'Danh Mục', 'Người Bán', 'Loại', 'Giá (VNĐ)', 'Tình Trạng', 'Trạng Thái', 'Ngày Đăng']);
            
            $products = $this->productModel->getAllForExport($from, $to);
            $totalProducts = 0;
            $soldProducts = 0;
            
            foreach ($products as $p) {
                $totalProducts++;
                if ($p['status'] === 'sold') $soldProducts++;
                
                fputcsv($output, [
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
            
            fputcsv($output, []);
            fputcsv($output, ['--- THỐNG KÊ TỔNG HỢP ---']);
            fputcsv($output, ['Tổng số bài đăng:', $totalProducts]);
            fputcsv($output, ['Số bài đã bán:', $soldProducts]);

        } else {
            fputcsv($output, ['Loại dữ liệu không hợp lệ']);
        }
        
        fclose($output);
        exit;
    }

    // ─── Quản lý Đánh Giá & Bình Luận (Feature 4) ──────────────────────────
    
}
