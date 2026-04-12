<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use Core\Flash;
use App\Models\Product;
use App\Models\Category;
use App\Models\Auction;
use App\Models\User;

/**
 * ProductController — Phase 4
 * Xử lý toàn bộ chức năng liên quan đến sản phẩm:
 * danh sách, chi tiết, đăng bán, sản phẩm của tôi, xóa.
 */
class ProductController extends Controller
{
    private const UPLOAD_DIR   = ROOT . '/public/uploads/';
    private const UPLOAD_MAX   = 3 * 1024 * 1024; // 3 MB
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private const PER_PAGE     = 12;

    private Product  $productModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->productModel  = new Product();
        $this->categoryModel = new Category();
    }

    // ─── Danh sách sản phẩm ──────────────────────────────────────────────

    public function index(): void
    {
        $keyword    = trim($_GET['q']          ?? '');
        $categoryId = (int)($_GET['category']  ?? 0);
        $condition  = $_GET['condition']       ?? '';
        $priceMin   = (int)($_GET['price_min'] ?? 0);
        $priceMax   = (int)($_GET['price_max'] ?? 0);
        $page       = max(1, (int)($_GET['page'] ?? 1));
        $offset     = ($page - 1) * self::PER_PAGE;

        // Sanitize condition
        $validConditions = ['new', 'like_new', 'used', 'worn'];
        if (!in_array($condition, $validConditions, true)) {
            $condition = '';
        }

        if ($keyword !== '') {
            $products = $this->productModel->search($keyword, $categoryId, self::PER_PAGE, $offset, $condition, $priceMin, $priceMax);
        } else {
            $products = $this->productModel->getActive(self::PER_PAGE, $offset, $categoryId, $condition, $priceMin, $priceMax);
        }

        // Tính giá hiện tại cho sản phẩm đấu giá
        $appUrl  = rtrim($_ENV['APP_URL'] ?? '', '/');
        foreach ($products as &$p) {
            if ($p['type'] === 'auction' && !empty($p['started_at'])) {
                $priceData = Auction::calculateCurrentPrice($p);
                $p['current_price']  = $priceData['current_price'];
                $p['is_at_floor']    = $priceData['is_at_floor'];
            }
        }
        unset($p);

        $categories = $this->categoryModel->all();

        $this->render('products/index', [
            'title'      => 'Sản phẩm',
            'products'   => $products,
            'categories' => $categories,
            'keyword'    => $keyword,
            'categoryId' => $categoryId,
            'condition'  => $condition,
            'priceMin'   => $priceMin,
            'priceMax'   => $priceMax,
            'page'       => $page,
        ]);
    }

    // ─── Chi tiết sản phẩm ───────────────────────────────────────────────

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) { $this->notFound(); }

        $product = $this->productModel->findWithAuction($id);
        if (!$product) { $this->notFound(); }

        $auctionPrice = null;
        if ($product['type'] === 'auction' && $product['auction_status'] === 'active') {
            $auctionPrice = Auction::calculateCurrentPrice([
                'start_price'     => $product['start_price'],
                'floor_price'     => $product['floor_price'],
                'decrease_amount' => $product['decrease_amount'],
                'step_minutes'    => $product['step_minutes'],
                'started_at'      => $product['started_at'],
            ]);
        }

        $userModel = new User();
        $seller = $userModel->findById($product['user_id']);
        if (!$seller) {
            $seller = ['name' => 'Người dùng ẩn', 'is_student_verified' => 0];
            $sellerRank = ['name' => 'Tân binh', 'color' => 'secondary', 'icon' => 'person'];
        } else {
            $sellerRank = $userModel->getRankLevel($seller['id']);
        }

        $appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
        $og = [
            'title'       => $product['title'],
            'description' => mb_substr(strip_tags($product['description']), 0, 150) . '...',
            'image'       => $product['image'] ? ($appUrl . '/public/uploads/' . $product['image']) : ($appUrl . '/public/assets/img/og-fallback.png'),
            'url'         => $appUrl . '/products/show?id=' . $product['id'],
            'type'        => 'product'
        ];

        $this->render('products/detail', [
            'title'        => $product['title'],
            'product'      => $product,
            'auctionPrice' => $auctionPrice,
            'seller'       => $seller,
            'sellerRank'   => $sellerRank,
            'og'           => $og,
        ]);
    }

    // ─── Form đăng bán ───────────────────────────────────────────────────

    public function createForm(): void
    {
        Middleware::requireAuth();
        $categories = $this->categoryModel->all();
        $this->render('products/create', [
            'title'      => 'Đăng bán sản phẩm',
            'categories' => $categories,
            'errors'     => [],
            'old'        => [],
        ]);
    }

    // ─── Xử lý đăng bán ──────────────────────────────────────────────────

    public function create(): void
    {
        Middleware::requireAuth();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Phiên hết hạn, thử lại.');
            $this->redirect('products/create');
        }

        $user = $this->currentUser();
        $old  = [
            'title'           => trim($_POST['title']           ?? ''),
            'description'     => trim($_POST['description']     ?? ''),
            'category_id'     => (int)($_POST['category_id']    ?? 0),
            'type'            => $_POST['type']                 ?? 'sale',
            'price'           => trim($_POST['price']           ?? ''),
            'start_price'     => trim($_POST['start_price']     ?? ''),
            'floor_price'     => trim($_POST['floor_price']     ?? ''),
            'decrease_amount' => trim($_POST['decrease_amount'] ?? ''),
            'step_minutes'    => trim($_POST['step_minutes']    ?? '10'),
        ];
        $errors = [];
        $categories = $this->categoryModel->all();

        // ── Validate cơ bản ───────────────────────────────────────────
        if (mb_strlen($old['title']) < 5) {
            $errors['title'] = 'Tiêu đề phải có ít nhất 5 ký tự.';
        }
        if (mb_strlen($old['description']) < 10) {
            $errors['description'] = 'Mô tả phải có ít nhất 10 ký tự.';
        }
        if ($old['category_id'] <= 0) {
            $errors['category_id'] = 'Vui lòng chọn danh mục.';
        }
        if (!in_array($old['type'], ['sale', 'exchange', 'auction'], true)) {
            $errors['type'] = 'Loại sản phẩm không hợp lệ.';
        }

        // ── Validate giá theo type ─────────────────────────────────────
        // Đọc condition
        $condition = $_POST['condition'] ?? 'used';
        if (!in_array($condition, ['new', 'like_new', 'used', 'worn'], true)) {
            $condition = 'used';
        }
        $old['condition'] = $condition;

        if ($old['type'] === 'sale') {
            $price = (int)str_replace(['.', ','], '', $old['price']);
            if ($price <= 0) $errors['price'] = 'Giá bán phải lớn hơn 0.';
        }

        if ($old['type'] === 'auction') {
            $startP    = (int)str_replace(['.', ','], '', $old['start_price']);
            $floorP    = (int)str_replace(['.', ','], '', $old['floor_price']);
            $decreaseA = (int)str_replace(['.', ','], '', $old['decrease_amount']);
            $stepMin   = max(1, (int)$old['step_minutes']);

            if ($startP <= 0)       $errors['start_price']     = 'Giá khởi điểm phải lớn hơn 0.';
            if ($floorP <= 0)       $errors['floor_price']     = 'Giá sàn phải lớn hơn 0.';
            if ($floorP >= $startP) $errors['floor_price']     = 'Giá sàn phải nhỏ hơn giá khởi điểm.';
            if ($decreaseA <= 0)    $errors['decrease_amount'] = 'Mức giảm phải lớn hơn 0.';
            if ($stepMin < 1)       $errors['step_minutes']    = 'Chu kỳ phải ít nhất 1 phút.';
        }

        // ── Upload ảnh ────────────────────────────────────────────────
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            if ($file['size'] > self::UPLOAD_MAX) {
                $errors['image'] = 'Ảnh không được vượt quá 3MB.';
            } elseif (!in_array(mime_content_type($file['tmp_name']), self::ALLOWED_MIME, true)) {
                $errors['image'] = 'Chỉ hỗ trợ ảnh JPG, PNG, WEBP, GIF.';
            } else {
                $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
                $imageName = uniqid('img_', true) . '.' . strtolower($ext);
                if (!move_uploaded_file($file['tmp_name'], self::UPLOAD_DIR . $imageName)) {
                    $errors['image'] = 'Không thể lưu ảnh, thử lại.';
                    $imageName = null;
                }
            }
        }

        if (!empty($errors)) {
            $this->render('products/create', [
                'title'      => 'Đăng bán sản phẩm',
                'categories' => $categories,
                'errors'     => $errors,
                'old'        => $old,
            ]);
            return;
        }

        // ── Lưu sản phẩm ─────────────────────────────────────────────
        $productId = $this->productModel->create([
            'user_id'     => $user['id'],
            'category_id' => $old['category_id'],
            'title'       => $old['title'],
            'description' => $old['description'],
            'image'       => $imageName,
            'type'        => $old['type'],
            'price'       => ($old['type'] === 'sale') ? $price : null,
            'condition'   => $old['condition'] ?? 'used',
        ]);

        // ── Tạo auction nếu là đấu giá ngược ─────────────────────────
        if ($old['type'] === 'auction' && $productId) {
            $auctionModel = new Auction();
            $auctionModel->createAuction($productId, $startP, $floorP, $decreaseA, $stepMin);
        }

        Flash::set('success', 'Đăng bài thành công! Bài đang chờ Admin duyệt.');
        $this->redirect('products/my');
    }

    // ─── Sản phẩm của tôi ────────────────────────────────────────────────

    public function myProducts(): void
    {
        Middleware::requireAuth();
        $user     = $this->currentUser();
        $products = $this->productModel->getByUser($user['id']);

        $this->render('products/my', [
            'title'    => 'Sản phẩm của tôi',
            'products' => $products,
        ]);
    }

    // ─── Xóa / Thu hồi bài đăng ──────────────────────────────────────────

    public function delete(): void
    {
        Middleware::requireAuth();
        if (!$this->verifyCsrf()) {
            $this->json(['success' => false, 'message' => 'CSRF không hợp lệ'], 403);
        }

        $user      = $this->currentUser();
        $productId = (int)($_POST['product_id'] ?? 0);
        $product   = $this->productModel->findWithAuction($productId);

        if (!$product || (int)$product['user_id'] !== (int)$user['id']) {
            Flash::set('danger', 'Không có quyền thực hiện thao tác này.');
            $this->redirect('products/my');
        }

        if ($product['status'] === 'sold') {
            Flash::set('danger', 'Không thể xóa sản phẩm đã bán.');
            $this->redirect('products/my');
        }

        $this->productModel->updateStatus($productId, 'cancelled');
        Flash::set('success', 'Đã thu hồi bài đăng thành công.');
        $this->redirect('products/my');
    }

    // ─── API Live Search ─────────────────────────────────────────────────

    public function apiSearch(): void
    {
        $keyword = trim($_GET['q'] ?? '');
        if ($keyword === '') {
            $this->json([]);
            return;
        }

        $results = $this->productModel->searchAjax($keyword);
        
        $appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        foreach ($results as &$r) {
            $r['image_url'] = $r['image'] ? ($appUrl . '/public/uploads/' . $r['image']) : '';
            $r['url'] = $appUrl . '/products/show?id=' . $r['id'];
        }
        unset($r);

        $this->json($results);
    }

    // ─── Private helpers ─────────────────────────────────────────────────

    private function notFound(): void
    {
        http_response_code(404);
        include APP_PATH . '/views/errors/404.php';
        exit;
    }
}
