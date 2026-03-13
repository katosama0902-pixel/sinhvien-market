<?php
/**
 * Main Layout - dùng cho trang sinh viên
 * $content được inject từ Controller::render()
 * $title  được truyền từ view data
 */
use Core\Flash;

$appUrl  = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
$title   = htmlspecialchars($title ?? 'SinhVienMarket', ENT_QUOTES, 'UTF-8');
$user    = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Marketplace mua bán, trao đổi và đấu giá ngược đồ dùng sinh viên KTX">
  <title><?= $title ?> — SinhVienMarket</title>

  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
</head>
<body>

<!-- ─── Navbar ─────────────────────────────────────── -->
<nav class="navbar navbar-main navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="<?= $appUrl ?>/products">
      <i class="bi bi-shop-window me-1"></i>SinhVien<span>Market</span>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <i class="bi bi-list text-white fs-4"></i>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <!-- Search form -->
      <form class="d-flex mx-auto my-2 my-lg-0" action="<?= $appUrl ?>/products" method="GET" style="max-width:380px;width:100%">
        <div class="input-group">
          <input type="text" name="q" class="form-control border-0 rounded-start-3"
                 placeholder="Tìm sách, đồ dùng..." value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES) ?>">
          <button class="btn btn-light px-3 rounded-end-3" type="submit" title="Tìm kiếm">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </form>

      <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
        <li class="nav-item">
          <a class="nav-link" href="<?= $appUrl ?>/products">
            <i class="bi bi-grid me-1"></i>Sản phẩm
          </a>
        </li>

        <?php if ($user): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= $appUrl ?>/products/create">
              <i class="bi bi-plus-circle me-1"></i>Đăng bán
            </a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($user['name'], ENT_QUOTES) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-1">
              <li><a class="dropdown-item" href="<?= $appUrl ?>/products/my">
                <i class="bi bi-box-seam me-2 text-primary"></i>Sản phẩm của tôi</a></li>
              <li><a class="dropdown-item" href="<?= $appUrl ?>/transactions/history">
                <i class="bi bi-receipt me-2 text-primary"></i>Lịch sử giao dịch</a></li>
              <?php if ($user['role'] === 'admin'): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= $appUrl ?>/admin">
                  <i class="bi bi-shield-lock me-2"></i>Admin Panel</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= $appUrl ?>/logout">
                <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= $appUrl ?>/login">Đăng nhập</a>
          </li>
          <li class="nav-item">
            <a class="nav-link btn-nav-cta" href="<?= $appUrl ?>/register">
              <i class="bi bi-person-plus me-1"></i>Đăng ký
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- ─── Flash Message ──────────────────────────────── -->
<div class="flash-banner">
  <?= Flash::render() ?>
</div>

<!-- ─── Main Content ───────────────────────────────── -->
<main>
  <?= $content ?>
</main>

<!-- ─── Footer ─────────────────────────────────────── -->
<footer class="site-footer">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6">
        <strong class="text-white">SinhVienMarket</strong> — Nền tảng mua bán, trao đổi đồ dùng sinh viên KTX Khu B
      </div>
      <div class="col-md-6 text-md-end mt-2 mt-md-0">
        <span>© <?= date('Y') ?> Đồ án cơ sở ngành Công nghệ thông tin</span>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Global JS -->
<script>
  // Auto-dismiss alerts sau 5 giây
  document.querySelectorAll('.alert.fade.show').forEach(function(el) {
    setTimeout(function() {
      var alert = bootstrap.Alert.getOrCreateInstance(el);
      if (alert) alert.close();
    }, 5000);
  });
</script>
<?php if (isset($extraJs)): ?>
  <?= $extraJs ?>
<?php endif; ?>
</body>
</html>
