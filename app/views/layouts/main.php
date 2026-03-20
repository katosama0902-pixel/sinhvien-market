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

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <!-- Bootstrap 5.3 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
</head>
<body>

<!-- ─── Navbar ─────────────────────────────────────── -->
<nav class="navbar navbar-main navbar-expand-lg" id="mainNavbar">
  <div class="container">
    <a class="navbar-brand" href="<?= $appUrl ?>/">
      <div class="navbar-brand-icon">
        <i class="bi bi-shop-window text-white" style="font-size:1rem"></i>
      </div>
      <div class="navbar-brand-text">SinhVien<span>Market</span></div>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <i class="bi bi-list text-white fs-4"></i>
    </button>

    <div class="collapse navbar-collapse" id="navMain">
      <!-- Search form -->
      <form class="d-flex mx-auto my-2 my-lg-0" action="<?= $appUrl ?>/products" method="GET" style="max-width:400px;width:100%">
        <div class="input-group" style="border-radius:50px;overflow:hidden">
          <input type="text" name="q" class="form-control border-0"
                 style="background:rgba(255,255,255,.15);color:#fff;border-radius:50px 0 0 50px!important;backdrop-filter:blur(8px)"
                 placeholder="Tìm sách, đồ dùng..." value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES) ?>">
          <button class="btn px-3" type="submit" title="Tìm kiếm"
                  style="background:rgba(255,255,255,.2);color:#fff;border:none;border-radius:0 50px 50px 0!important">
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
            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
              <div class="nav-avatar">
                <?= mb_strtoupper(mb_substr($user['name'], 0, 1)) ?>
              </div>
              <span><?= htmlspecialchars($user['name'], ENT_QUOTES) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end border-0 rounded-4 mt-1 p-2" style="min-width:220px">
              <li>
                <a class="dropdown-item rounded-3 py-2" href="<?= $appUrl ?>/dashboard">
                  <i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard của tôi
                </a>
              </li>
              <li>
                <a class="dropdown-item rounded-3 py-2" href="<?= $appUrl ?>/products/my">
                  <i class="bi bi-box-seam me-2 text-primary"></i>Sản phẩm của tôi
                </a>
              </li>
              <li>
                <a class="dropdown-item rounded-3 py-2" href="<?= $appUrl ?>/transactions/history">
                  <i class="bi bi-receipt me-2 text-primary"></i>Lịch sử giao dịch
                </a>
              </li>
              <?php if ($user['role'] === 'admin'): ?>
                <li><hr class="dropdown-divider my-2"></li>
                <li>
                  <a class="dropdown-item rounded-3 py-2 text-danger" href="<?= $appUrl ?>/admin">
                    <i class="bi bi-shield-lock me-2"></i>Admin Panel
                  </a>
                </li>
              <?php endif; ?>
              <li><hr class="dropdown-divider my-2"></li>
              <li>
                <a class="dropdown-item rounded-3 py-2 text-danger" href="<?= $appUrl ?>/logout">
                  <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                </a>
              </li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= $appUrl ?>/login-role">Đăng nhập</a>
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
    <div class="row g-4">
      <!-- Brand & About -->
      <div class="col-md-4">
        <div class="footer-brand"><i class="bi bi-shop-window me-1"></i>SinhVienMarket</div>
        <p class="mb-0" style="font-size:.875rem;line-height:1.7">
          Nền tảng mua bán, trao đổi &amp; đấu giá ngược dành riêng cho sinh viên <strong style="color:rgba(255,255,255,.7)">KTX Khu B</strong>.
          Tiết kiệm chi phí, kết nối cộng đồng.
        </p>
        <div class="mt-3 d-flex gap-2">
          <a href="#" class="btn btn-sm" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.6);border-radius:8px;width:36px;padding:0;height:36px;display:flex;align-items:center;justify-content:center" title="Facebook">
            <i class="bi bi-facebook"></i>
          </a>
          <a href="#" class="btn btn-sm" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.6);border-radius:8px;width:36px;padding:0;height:36px;display:flex;align-items:center;justify-content:center" title="Zalo">
            <i class="bi bi-chat-dots"></i>
          </a>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="col-md-2 col-6">
        <div class="footer-heading">Khám phá</div>
        <ul class="footer-links">
          <li><a href="<?= $appUrl ?>/products">Tất cả sản phẩm</a></li>
          <li><a href="<?= $appUrl ?>/products?type=auction">Đấu giá ngược</a></li>
          <li><a href="<?= $appUrl ?>/products?type=exchange">Trao đổi</a></li>
        </ul>
      </div>

      <!-- Account -->
      <div class="col-md-2 col-6">
        <div class="footer-heading">Tài khoản</div>
        <ul class="footer-links">
          <?php if ($user): ?>
            <li><a href="<?= $appUrl ?>/dashboard">Dashboard</a></li>
            <li><a href="<?= $appUrl ?>/products/create">Đăng bán</a></li>
            <li><a href="<?= $appUrl ?>/logout">Đăng xuất</a></li>
          <?php else: ?>
            <li><a href="<?= $appUrl ?>/login-role">Đăng nhập</a></li>
            <li><a href="<?= $appUrl ?>/register">Đăng ký miễn phí</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Contact -->
      <div class="col-md-4">
        <div class="footer-heading">Thông tin</div>
        <ul class="footer-links">
          <li><i class="bi bi-geo-alt me-2"></i>KTX Khu B, Đại học Quốc gia TP.HCM</li>
          <li><i class="bi bi-envelope me-2"></i>support@sinhvienmarket.edu.vn</li>
          <li><i class="bi bi-clock me-2"></i>Hỗ trợ: 8:00 - 22:00 hàng ngày</li>
        </ul>
      </div>
    </div>

    <hr class="footer-divider">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 footer-bottom">
      <span>© <?= date('Y') ?> SinhVienMarket — Đồ án cơ sở ngành Công nghệ thông tin</span>
      <span>Made with <span style="color:#ef4444">♥</span> for students</span>
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Global JS -->
<script>
  // Navbar scroll effect
  const navbar = document.getElementById('mainNavbar');
  if (navbar) {
    window.addEventListener('scroll', () => {
      navbar.classList.toggle('scrolled', window.scrollY > 40);
    });
  }

  // Auto-dismiss alerts sau 5 giây
  document.querySelectorAll('.alert.fade.show').forEach(function(el) {
    setTimeout(function() {
      var alert = bootstrap.Alert.getOrCreateInstance(el);
      if (alert) alert.close();
    }, 5000);
  });

  // Intersection Observer for fade-in-up elements not tied to page load
  if ('IntersectionObserver' in window) {
    const revealEls = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) { e.target.classList.add('fade-in-up'); io.unobserve(e.target); }
      });
    }, { threshold: .15 });
    revealEls.forEach(el => io.observe(el));
  }
</script>
<?php if (isset($extraJs)): ?>
  <?= $extraJs ?>
<?php endif; ?>
</body>
</html>
