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

class AdminProductController extends Controller
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
            return;
        }

        $admin = $this->currentUser();
        $productId = (int)($_POST['product_id'] ?? 0);
        $product = $this->productModel->findWithAuction($productId);

        if (!$product) {
            Flash::set('danger', 'Sản phẩm không tồn tại.');
            $this->redirect('admin/products');
            return;
        }

        $this->productModel->updateStatus($productId, 'active');
        $this->auditModel->log($admin['id'], 'approve_product', 'product', $productId, "Duyệt: {$product['title']}");

        // Gửi thông báo cho người đăng
        $seller = $this->userModel->findById((int)$product['user_id']);
        if ($seller) {
            NotificationService::notifyProductApproved(
                (int)$seller['id'], $seller['email'], $seller['name'],
                $productId, $product['title']
            );
        }

        Flash::set('success', "Đã duyệt: {$product['title']}");
        $this->redirect('admin/products');
    }

    public function rejectProduct(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'CSRF không hợp lệ.');
            $this->redirect('admin/products');
            return;
        }

        $admin = $this->currentUser();
        $productId = (int)($_POST['product_id'] ?? 0);
        $product = $this->productModel->findWithAuction($productId);
        $reason = trim($_POST['reject_reason'] ?? '');

        if (!$product) {
            Flash::set('danger', 'Sản phẩm không tồn tại.');
            $this->redirect('admin/products');
            return;
        }

        $this->productModel->updateStatus($productId, 'cancelled');
        $this->auditModel->log($admin['id'], 'reject_product', 'product', $productId, "Từ chối: {$product['title']}");

        // Gửi thông báo cho người đăng
        $seller = $this->userModel->findById((int)$product['user_id']);
        if ($seller) {
            NotificationService::notifyProductRejected(
                (int)$seller['id'], $seller['email'], $seller['name'],
                $productId, $product['title'], $reason
            );
        }

        Flash::set('warning', "Đã từ chối: {$product['title']}");
        $this->redirect('admin/products');
    }

    public function deleteProduct(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'CSRF không hợp lệ.');
            $this->redirect('admin/products');
            return;
        }

        $admin = $this->currentUser();
        $productId = (int)($_POST['product_id'] ?? 0);
        $product = $this->productModel->findWithAuction($productId);

        if (!$product) {
            Flash::set('danger', 'Sản phẩm không tồn tại.');
            $this->redirect('admin/products');
            return;
        }

        $this->productModel->updateStatus($productId, 'cancelled');

        // BUG-014 fix: xóa file ảnh vật lý để tránh storage leak
        if (!empty($product['image'])) {
            $imgPath = ROOT . '/public/uploads/' . $product['image'];
            if (file_exists($imgPath)) {
                unlink($imgPath);
            }
        }

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
            'csrf' => $this->csrfToken()
        ], 'admin');
    }

    public function storeCategory(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'CSRF không hợp lệ.');
            $this->redirect('admin/categories');
            return;
        }

        $admin = $this->currentUser();
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-tag');

        if (mb_strlen($name) < 2) {
            Flash::set('danger', 'Tên danh mục phải có ít nhất 2 ký tự.');
            $this->redirect('admin/categories');
            return;
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
            return;
        }

        $admin = $this->currentUser();
        $id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-tag');

        if (!$id || mb_strlen($name) < 2) {
            Flash::set('danger', 'Dữ liệu không hợp lệ.');
            $this->redirect('admin/categories');
            return;
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
            return;
        }

        $admin = $this->currentUser();
        $id = (int)($_POST['category_id'] ?? 0);
        $cat = $this->categoryModel->findById($id);

        if (!$cat) {
            Flash::set('danger', 'Danh mục không tồn tại.');
            $this->redirect('admin/categories');
            return;
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

    public function banners(): void
    {
        Middleware::requireAdmin();
        $model = new \App\Models\Banner();
        $this->render('admin/banners', [
            'title'   => 'Quản lý Banner Trang chủ',
            'banners' => $model->getAllForAdmin(),
            'csrf'    => $this->csrfToken(), // BUG-006 fix: truyền token vào view
        ], 'admin');
    }

    public function storeBanner(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) { // BUG-006 fix
            Flash::set('danger', 'Phiên làm việc hết hạn.');
            $this->redirect('admin/banners'); return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($this->input('title'));
            $linkUrl = trim($this->input('link_url'));
            $displayOrder = (int)$this->input('display_order', 0);
            $isActive = (int)$this->input('is_active', 1);

            $imageUrl = '';
            if (!empty($_FILES['image']['name'])) {
                $uploadPath = __DIR__ . '/../../public/uploads/banners/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('banner_') . '.' . $ext;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath . $filename)) {
                    $imageUrl = '/public/uploads/banners/' . $filename;
                }
            }

            if (!$imageUrl) {
                \Core\Flash::set('danger', 'Vui lòng chọn ảnh Banner hợp lệ.');
                $this->redirect('admin/banners'); return; // BUG-006 fix: bỏ dấu /
            }

            $model = new \App\Models\Banner();
            $model->create($imageUrl, $linkUrl, $title, $displayOrder, $isActive);
            
            \Core\Flash::set('success', 'Đã thêm Banner mới.');
        }
        $this->redirect('admin/banners'); // BUG-006 fix: bỏ dấu /
    }

    public function updateBanner(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) { // BUG-006 fix
            Flash::set('danger', 'Phiên làm việc hết hạn.');
            $this->redirect('admin/banners'); return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$this->input('id');
            $title = trim($this->input('title'));
            $linkUrl = trim($this->input('link_url'));
            $displayOrder = (int)$this->input('display_order', 0);

            $model = new \App\Models\Banner();
            $model->update($id, $linkUrl, $title, $displayOrder);
            
            \Core\Flash::set('success', 'Đã cập nhật Banner.');
        }
        $this->redirect('admin/banners'); // BUG-006 fix: bỏ dấu /
    }

    public function toggleBanner(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) { // BUG-006 fix
            Flash::set('danger', 'Phiên làm việc hết hạn.');
            $this->redirect('admin/banners'); return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$this->input('id');
            $model = new \App\Models\Banner();
            $model->toggleStatus($id);
            \Core\Flash::set('success', 'Đã thay đổi trạng thái Banner.');
        }
        $this->redirect('admin/banners'); // BUG-006 fix: bỏ dấu /
    }

    public function deleteBanner(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) { // BUG-006 fix
            Flash::set('danger', 'Phiên làm việc hết hạn.');
            $this->redirect('admin/banners'); return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$this->input('id');
            $model = new \App\Models\Banner();
            $banner = $model->findById($id);
            
            if ($banner) {
                // Xóa file ảnh thực tế
                $filePath = __DIR__ . '/../..' . $banner['image'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $model->delete($id);
                \Core\Flash::set('success', 'Đã xóa Banner vĩnh viễn.');
            }
        }
        $this->redirect('admin/banners'); // BUG-006 fix: bỏ dấu /
    }

    // ─── Quản lý Tố Cáo & Vi phạm ───────────────────────────────────────

}
