<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;

/**
 * AdminController - Panel quản trị (chỉ admin mới được truy cập)
 * Sẽ được triển khai đầy đủ trong Phase 7
 */
class AdminController extends Controller
{
    public function dashboard(): void
    {
        Middleware::requireAdmin();
        $this->render('admin/dashboard', ['title' => 'Dashboard Admin'], 'admin');
    }

    public function users(): void
    {
        Middleware::requireAdmin();
        $this->render('admin/users', ['title' => 'Quản lý người dùng', 'users' => []], 'admin');
    }

    public function toggleUser(): void
    {
        Middleware::requireAdmin();
        // TODO: Phase 7
        $this->redirect('admin/users');
    }

    public function products(): void
    {
        Middleware::requireAdmin();
        $this->render('admin/products', ['title' => 'Kiểm duyệt bài đăng', 'products' => []], 'admin');
    }

    public function approveProduct(): void
    {
        Middleware::requireAdmin();
        // TODO: Phase 7
        $this->redirect('admin/products');
    }

    public function rejectProduct(): void
    {
        Middleware::requireAdmin();
        // TODO: Phase 7
        $this->redirect('admin/products');
    }

    public function deleteProduct(): void
    {
        Middleware::requireAdmin();
        // TODO: Phase 7
        $this->redirect('admin/products');
    }

    public function categories(): void
    {
        Middleware::requireAdmin();
        $this->render('admin/categories', ['title' => 'Quản lý danh mục', 'categories' => []], 'admin');
    }

    public function storeCategory(): void
    {
        Middleware::requireAdmin();
        // TODO: Phase 7
        $this->redirect('admin/categories');
    }

    public function updateCategory(): void
    {
        Middleware::requireAdmin();
        // TODO: Phase 7
        $this->redirect('admin/categories');
    }

    public function deleteCategory(): void
    {
        Middleware::requireAdmin();
        // TODO: Phase 7
        $this->redirect('admin/categories');
    }

    public function reports(): void
    {
        Middleware::requireAdmin();
        $this->render('admin/reports', ['title' => 'Báo cáo giao dịch'], 'admin');
    }

    public function auditLog(): void
    {
        Middleware::requireAdmin();
        $this->render('admin/audit_log', ['title' => 'Nhật ký hành động Admin', 'logs' => []], 'admin');
    }
}
