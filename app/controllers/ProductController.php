<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;

/**
 * ProductController - Quản lý bài đăng sản phẩm
 * Sẽ được triển khai đầy đủ trong Phase 4
 */
class ProductController extends Controller
{
    public function index(): void
    {
        // TODO: implement in Phase 4
        $this->render('products/index', ['title' => 'Marketplace Sinh Viên', 'products' => []]);
    }

    public function show(): void
    {
        // TODO: implement in Phase 4
        $this->render('products/detail', ['title' => 'Chi tiết sản phẩm', 'product' => null]);
    }

    public function createForm(): void
    {
        Middleware::requireAuth();
        $this->render('products/create', ['title' => 'Đăng bán sản phẩm']);
    }

    public function create(): void
    {
        Middleware::requireAuth();
        // TODO: implement in Phase 4
        $this->redirect('products');
    }

    public function myProducts(): void
    {
        Middleware::requireAuth();
        // TODO: implement in Phase 4
        $this->render('products/my', ['title' => 'Sản phẩm của tôi', 'products' => []]);
    }

    public function delete(): void
    {
        Middleware::requireAuth();
        // TODO: implement in Phase 4
        $this->redirect('products/my');
    }
}
