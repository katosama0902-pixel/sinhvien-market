<?php
/**
 * Admin Layout - sidebar + topbar cho panel quản trị
 * $content được inject từ Controller::render()
 */
use Core\Flash;

$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
$title  = htmlspecialchars($title ?? 'Admin', ENT_QUOTES, 'UTF-8');
$user   = $_SESSION['user'] ?? [];

// Xác định trang hiện tại để active sidebar
$currentUrl = $_SERVER['REQUEST_URI'] ?? '';
function isActive(string $keyword, string $current): string {
    return str_contains($current, $keyword) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $title ?> — Admin | SinhVienMarket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
  <style>
    body { background: #f1f3f9; }

    .admin-wrapper { display: flex; min-height: 100vh; }

    /* Sidebar */
    .admin-sidebar {
      width: 250px; min-width: 250px;
      background: #1a1d2e;
      display: flex; flex-direction: column;
      position: fixed; top: 0; left: 0; bottom: 0;
      z-index: 100; overflow-y: auto;
    }
    .sidebar-brand {
      padding: 1.25rem 1.5rem;
      color: #fff; font-weight: 800; font-size: 1.15rem;
      border-bottom: 1px solid rgba(255,255,255,.1);
      text-decoration: none;
    }
    .sidebar-brand span { color: #818cf8; }

    .sidebar-section {
      padding: .5rem 1rem .25rem;
      color: rgba(255,255,255,.35);
      font-size: .7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-top: .5rem;
    }

    .sidebar-nav { padding: .5rem; flex: 1; }
    .sidebar-nav a {
      display: flex; align-items: center; gap: .75rem;
      padding: .6rem 1rem; border-radius: 8px;
      color: rgba(255,255,255,.65);
      font-size: .9rem; font-weight: 500;
      text-decoration: none;
      transition: all .18s;
      margin-bottom: 2px;
    }
    .sidebar-nav a i { font-size: 1.1rem; width: 20px; }
    .sidebar-nav a:hover  { background: rgba(255,255,255,.08); color: #fff; }
    .sidebar-nav a.active { background: #3b5bdb;  color: #fff; box-shadow: 0 2px 8px rgba(59,91,219,.4); }

    .sidebar-footer {
      padding: 1rem;
      border-top: 1px solid rgba(255,255,255,.1);
    }
    .sidebar-footer a {
      display: flex; align-items: center; gap: .5rem;
      color: rgba(255,255,255,.5); font-size: .85rem;
      text-decoration: none; transition: color .18s;
    }
    .sidebar-footer a:hover { color: #fff; }

    /* Main panel */
    .admin-main {
      flex: 1;
      margin-left: 250px;
      display: flex;
      flex-direction: column;
    }

    /* Topbar */
    .admin-topbar {
      background: #fff;
      border-bottom: 1px solid #dee2e6;
      padding: .75rem 1.5rem;
      display: flex; align-items: center; justify-content: space-between;
      position: sticky; top: 0; z-index: 90;
      box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }
    .admin-topbar h5 { font-weight: 700; margin: 0; color: #212529; }

    /* Content area */
    .admin-content { padding: 1.5rem; flex: 1; }

    /* Responsive */
    @media (max-width: 768px) {
      .admin-sidebar { display: none; }
      .admin-main { margin-left: 0; }
    }
  </style>
</head>
<body>
<div class="admin-wrapper">

  <!-- ─── Sidebar ───────────────────────────────── -->
  <aside class="admin-sidebar">
    <a class="sidebar-brand" href="<?= $appUrl ?>/admin">
      <i class="bi bi-shield-lock me-2"></i>Admin<span>Panel</span>
    </a>

    <nav class="sidebar-nav">
      <div class="sidebar-section">Tổng quan</div>
      <a href="<?= $appUrl ?>/admin" class="<?= isActive('/admin', $currentUrl) && !str_contains($currentUrl, 'users') && !str_contains($currentUrl, 'products') && !str_contains($currentUrl, 'categories') && !str_contains($currentUrl, 'reports') && !str_contains($currentUrl, 'audit') ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>

      <div class="sidebar-section">Quản lý</div>
      <a href="<?= $appUrl ?>/admin/users" class="<?= isActive('admin/users', $currentUrl) ?>">
        <i class="bi bi-people"></i> Người dùng
      </a>
      <a href="<?= $appUrl ?>/admin/products" class="<?= isActive('admin/products', $currentUrl) ?>">
        <i class="bi bi-card-checklist"></i> Kiểm duyệt bài
      </a>
      <a href="<?= $appUrl ?>/admin/categories" class="<?= isActive('admin/categories', $currentUrl) ?>">
        <i class="bi bi-tags"></i> Danh mục
      </a>

      <div class="sidebar-section">Báo cáo</div>
      <a href="<?= $appUrl ?>/admin/reports" class="<?= isActive('admin/reports', $currentUrl) ?>">
        <i class="bi bi-bar-chart-line"></i> Giao dịch
      </a>
      <a href="<?= $appUrl ?>/admin/audit-log" class="<?= isActive('audit-log', $currentUrl) ?>">
        <i class="bi bi-journal-text"></i> Nhật ký Admin
      </a>
    </nav>

    <div class="sidebar-footer">
      <a href="<?= $appUrl ?>/products" target="_blank">
        <i class="bi bi-box-arrow-up-right"></i> Xem trang web
      </a>
    </div>
  </aside>

  <!-- ─── Main ──────────────────────────────────── -->
  <div class="admin-main">
    <div class="admin-topbar">
      <h5><?= $title ?></h5>
      <div class="d-flex align-items-center gap-3">
        <span class="text-muted small"><i class="bi bi-person-circle me-1"></i>
          <?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES) ?></span>
        <a href="<?= $appUrl ?>/logout" class="btn btn-sm btn-outline-danger">
          <i class="bi bi-box-arrow-right me-1"></i>Đăng xuất
        </a>
      </div>
    </div>

    <!-- Flash -->
    <div class="flash-banner"><?= Flash::render() ?></div>

    <div class="admin-content">
      <?= $content ?>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
