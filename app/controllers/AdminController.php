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

/**
 * AdminController — Phase 7
 * Tất cả route /admin/* đều yêu cầu role = 'admin'
 */
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

    public function users(): void
    {
        Middleware::requireAdmin();
        $users = $this->userModel->all();
        $this->render('admin/users', ['title' => 'Quản lý người dùng', 'users' => $users], 'admin');
    }

    public function toggleUser(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'CSRF không hợp lệ.');
            $this->redirect('admin/users');
        }

        $admin = $this->currentUser();
        $userId = (int)($_POST['user_id'] ?? 0);
        $user = $this->userModel->findById($userId);

        if (!$user || $user['role'] === 'admin') {
            Flash::set('danger', 'Không thể thực hiện thao tác này.');
            $this->redirect('admin/users');
        }

        $newLock = $user['is_locked'] ? 0 : 1;
        $this->userModel->toggleLock($userId, $newLock);

        $action = $newLock ? 'lock_user' : 'unlock_user';
        $note = "User: {$user['name']} ({$user['email']})";
        $this->auditModel->log($admin['id'], $action, 'user', $userId, $note);

        $msg = $newLock ? "Đã khóa tài khoản {$user['name']}." : "Đã mở khóa tài khoản {$user['name']}.";
        Flash::set('success', $msg);
        $this->redirect('admin/users');
    }

    // ─── Kiểm duyệt sản phẩm ────────────────────────────────────────────

    public function products(): void
    {
        Middleware::requireAdmin();
        $tab = $_GET['tab'] ?? 'pending';
        $products = ($tab === 'all')
            ? $this->productModel->getAllForAdmin()
            : $this->productModel->getPending();
        $this->render('admin/products', [
            'title' => 'Kiểm duyệt bài đăng',
            'products' => $products,
            'tab' => $tab,
        ], 'admin');
    }

    public function approveProduct(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'CSRF không hợp lệ.');
            $this->redirect('admin/products');
        }

        $admin = $this->currentUser();
        $productId = (int)($_POST['product_id'] ?? 0);
        $product = $this->productModel->findWithAuction($productId);

        if (!$product) {
            Flash::set('danger', 'Sản phẩm không tồn tại.');
            $this->redirect('admin/products');
        }

        $this->productModel->updateStatus($productId, 'active');
        $this->auditModel->log($admin['id'], 'approve_product', 'product', $productId, "Duyệt: {$product['title']}");
        Flash::set('success', "Đã duyệt: {$product['title']}");
        $this->redirect('admin/products');
    }

    public function rejectProduct(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'CSRF không hợp lệ.');
            $this->redirect('admin/products');
        }

        $admin = $this->currentUser();
        $productId = (int)($_POST['product_id'] ?? 0);
        $product = $this->productModel->findWithAuction($productId);

        if (!$product) {
            Flash::set('danger', 'Sản phẩm không tồn tại.');
            $this->redirect('admin/products');
        }

        $this->productModel->updateStatus($productId, 'cancelled');
        $this->auditModel->log($admin['id'], 'reject_product', 'product', $productId, "Từ chối: {$product['title']}");
        Flash::set('warning', "Đã từ chối: {$product['title']}");
        $this->redirect('admin/products');
    }

    public function deleteProduct(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'CSRF không hợp lệ.');
            $this->redirect('admin/products');
        }

        $admin = $this->currentUser();
        $productId = (int)($_POST['product_id'] ?? 0);
        $product = $this->productModel->findWithAuction($productId);

        if (!$product) {
            Flash::set('danger', 'Sản phẩm không tồn tại.');
            $this->redirect('admin/products');
        }

        $this->productModel->updateStatus($productId, 'cancelled');
        $this->auditModel->log($admin['id'], 'delete_product', 'product', $productId, "Xóa: {$product['title']} — đăng bởi {$product['seller_name']}");
        Flash::set('success', "Đã xóa bài đăng: {$product['title']}");
        $this->redirect('admin/products');
    }

    // ─── Quản lý danh mục ────────────────────────────────────────────────

    public function categories(): void
    {
        Middleware::requireAdmin();
        $categories = $this->categoryModel->all();
        $this->render('admin/categories', [
            'title' => 'Quản lý danh mục',
            'categories' => $categories,
        ], 'admin');
    }

    public function storeCategory(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'CSRF không hợp lệ.');
            $this->redirect('admin/categories');
        }

        $admin = $this->currentUser();
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-tag');

        if (mb_strlen($name) < 2) {
            Flash::set('danger', 'Tên danh mục phải có ít nhất 2 ký tự.');
            $this->redirect('admin/categories');
        }

        $slug = Category::makeSlug($name);
        $id = $this->categoryModel->create($name, $slug, $icon);
        $this->auditModel->log($admin['id'], 'create_category', 'category', $id, "Tạo: $name");
        Flash::set('success', "Đã tạo danh mục: $name");
        $this->redirect('admin/categories');
    }

    public function updateCategory(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'CSRF không hợp lệ.');
            $this->redirect('admin/categories');
        }

        $admin = $this->currentUser();
        $id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-tag');

        if (!$id || mb_strlen($name) < 2) {
            Flash::set('danger', 'Dữ liệu không hợp lệ.');
            $this->redirect('admin/categories');
        }

        $slug = Category::makeSlug($name);
        $this->categoryModel->update($id, $name, $slug, $icon);
        $this->auditModel->log($admin['id'], 'update_category', 'category', $id, "Sửa: $name");
        Flash::set('success', "Đã cập nhật danh mục: $name");
        $this->redirect('admin/categories');
    }

    public function deleteCategory(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'CSRF không hợp lệ.');
            $this->redirect('admin/categories');
        }

        $admin = $this->currentUser();
        $id = (int)($_POST['category_id'] ?? 0);
        $cat = $this->categoryModel->findById($id);

        if (!$cat) {
            Flash::set('danger', 'Danh mục không tồn tại.');
            $this->redirect('admin/categories');
        }

        try {
            $this->categoryModel->delete($id);
            $this->auditModel->log($admin['id'], 'delete_category', 'category', $id, "Xóa: {$cat['name']}");
            Flash::set('success', "Đã xóa danh mục: {$cat['name']}");
        }
        catch (\PDOException $e) {
            Flash::set('danger', 'Không thể xóa danh mục đang có sản phẩm.');
        }
        $this->redirect('admin/categories');
    }

    // ─── Báo cáo giao dịch ───────────────────────────────────────────────

    public function reports(): void
    {
        Middleware::requireAdmin();
        $fromDate = $_GET['from'] ?? date('Y-m-01');
        $toDate = $_GET['to'] ?? date('Y-m-d');

        $transactions = $this->txModel->getAll($fromDate, $toDate);
        $totalAmount = array_sum(array_column($transactions, 'amount'));

        $this->render('admin/reports', [
            'title' => 'Báo cáo giao dịch',
            'transactions' => $transactions,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'totalAmount' => $totalAmount,
        ], 'admin');
    }

    // ─── Audit Log ───────────────────────────────────────────────────────

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
    public function giveaways(): void
    {
        Middleware::requireAdmin();
        $model = new \App\Models\Giveaway();
        $items = $model->getAll();

        $this->render('admin/giveaways', [
            'title'     => 'Quản lý Giveaways',
            'giveaways' => $items,
        ], 'admin');
    }

    public function storeGiveaway(): void
    {
        Middleware::requireAdmin();
        if ($this->verifyCsrf()) {
            $data = [
                'title'       => $this->input('title'),
                'description' => $this->input('description'),
                'image'       => '',
                'end_time'    => $this->input('end_time'),
            ];

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // Upload logic
                $tmp  = $_FILES['image']['tmp_name'];
                $name = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $_FILES['image']['name']);
                $dest = APP_PATH . '/../public/uploads/' . $name;
                if (move_uploaded_file($tmp, $dest)) {
                    $data['image'] = $name;
                }
            }

            (new \App\Models\Giveaway())->create($data);
            \Core\Flash::set('success', 'Tạo Giveaway thành công!');
        }
        $this->redirect('admin/giveaways');
    }

    public function spinGiveaway(): void
    {
        Middleware::requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $model = new \App\Models\Giveaway();
        $giveaway = $model->findById($id);

        if (!$giveaway || $giveaway['status'] === 'ended') {
            \Core\Flash::set('danger', 'Sự kiện không tồn tại hoặc đã kết thúc!');
            $this->redirect('admin/giveaways');
            return;
        }

        $participants = $model->getParticipants($id);
        
        $this->render('admin/giveaway_spin', [
            'title'        => 'Vòng Quay May Mắn - ' . htmlspecialchars($giveaway['title'], ENT_QUOTES),
            'giveaway'     => $giveaway,
            'participants' => json_encode($participants, JSON_UNESCAPED_UNICODE),
        ], 'admin');
    }

    public function spinGiveawayApi(): void
    {
        Middleware::requireAdmin();
        header('Content-Type: application/json');
        
        $id = (int)$this->input('id');
        $winnerId = (int)$this->input('winner_id');

        if ($id > 0 && $winnerId > 0) {
            $model = new \App\Models\Giveaway();
            $model->setWinner($id, $winnerId);
            echo json_encode(['status' => 'success', 'msg' => 'Đã lưu người trúng giải!']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Dữ liệu không hợp lệ.']);
        }
    }
}
