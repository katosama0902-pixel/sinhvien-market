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
  <!-- Open Graph Meta Tags -->
  <meta property="og:title" content="<?= htmlspecialchars($og['title'] ?? $title, ENT_QUOTES) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($og['description'] ?? 'Trang thương mại điện tử sinh viên, trao đổi và thanh lý đồ dùng nội trú nhanh chóng.', ENT_QUOTES) ?>">
  <meta property="og:image" content="<?= htmlspecialchars($og['image'] ?? ($appUrl . '/public/assets/img/og-fallback.png'), ENT_QUOTES) ?>">
  <meta property="og:url" content="<?= htmlspecialchars($og['url'] ?? $appUrl, ENT_QUOTES) ?>">
  <meta property="og:type" content="<?= htmlspecialchars($og['type'] ?? 'website', ENT_QUOTES) ?>">
  <title><?= $title ?> — SinhVienMarket</title>

  <!-- Bootstrap Icons (giữ lại icon set) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Tailwind CSS + Custom Design System -->
  <link href="<?= $appUrl ?>/public/css/app.css" rel="stylesheet">
  <!-- Legacy CSS (animations, countdown, skeleton) -->
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
  <script>
    // Dark mode: thêm class 'dark' vào <html> trước khi render để tránh FOUC
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.classList.add('dark');
    }
  </script>
</head>
<body class="bg-light-bg text-light-text dark:bg-dark-bg dark:text-dark-text min-h-screen flex flex-col font-sans antialiased">

<!-- ─── Navbar ─────────────────────────────────────── -->
<nav class="navbar-main" id="mainNavbar" x-data="{ mobileOpen: false }">
  <div class="container mx-auto px-4">
    <div class="flex items-center justify-between py-3">

      <!-- Brand -->
      <a class="flex items-center gap-2 no-underline" href="<?= $appUrl ?>/">
        <div class="w-9 h-9 flex items-center justify-center rounded-[10px] text-white shadow-[0_4px_12px_rgba(99,102,241,.5)]" style="background:linear-gradient(135deg,#6366f1,#8b5cf6,#ec4899)">
          <i class="bi bi-shop-window text-sm"></i>
        </div>
        <div class="text-white font-black text-xl tracking-tight">SinhVien<span class="text-indigo-300">Market</span></div>
      </a>

      <!-- Mobile toggle -->
      <button @click="mobileOpen = !mobileOpen" class="lg:hidden text-white text-2xl p-1 bg-transparent border-0 cursor-pointer">
        <i class="bi bi-list"></i>
      </button>

      <!-- Desktop Nav -->
      <div class="hidden lg:flex items-center flex-1 gap-3 ml-4">

        <!-- Search -->
        <form class="relative flex-1 max-w-sm" action="<?= $appUrl ?>/products" method="GET" id="mainSearchForm">
          <div class="flex rounded-full overflow-hidden">
            <input type="text" name="q" id="mainSearchInput"
                   class="flex-1 px-4 py-2 text-sm border-0 outline-none min-w-0"
                   style="background:rgba(255,255,255,.15);color:#fff;backdrop-filter:blur(8px)"
                   placeholder="Tìm sách, đồ dùng..." value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES) ?>" autocomplete="off">
            <button type="submit" class="px-3 border-0 cursor-pointer flex-shrink-0" style="background:rgba(255,255,255,.2);color:#fff">
              <i class="bi bi-search"></i>
            </button>
          </div>
          <!-- Dropdown Gợi Ý -->
          <div id="searchDropdown" class="hidden absolute top-full left-0 w-full mt-2 z-[9999] rounded-xl overflow-hidden shadow-xl bg-white dark:bg-dark-card">
            <div id="searchDropdownContent"></div>
          </div>
        </form>

        <!-- Nav Links -->
        <ul class="flex items-center gap-1 ml-auto list-none p-0 m-0">
          <li>
            <a class="flex items-center gap-1 px-3 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10 text-sm font-semibold transition-all no-underline" href="<?= $appUrl ?>/products">
              <i class="bi bi-grid"></i> Sản phẩm
            </a>
          </li>
          <li>
            <a class="flex items-center gap-1 px-3 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10 text-sm font-semibold transition-all no-underline" href="<?= $appUrl ?>/leaderboard">
              <i class="bi bi-trophy"></i> Bảng XH
            </a>
          </li>

          <?php if ($user): ?>
            <?php if (($user['role'] ?? '') !== 'admin'): ?>
            <li>
              <a class="flex items-center gap-1 px-3 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10 text-sm font-semibold transition-all no-underline" href="<?= $appUrl ?>/products/create">
                <i class="bi bi-plus-circle"></i> Đăng bán
              </a>
            </li>
            <?php endif; ?>

            <?php if (($user['role'] ?? '') === 'admin'): ?>
            <li>
              <a href="<?= $appUrl ?>/admin" class="flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold no-underline transition-all" style="background:rgba(239,68,68,.18);color:#fca5a5;border:1px solid rgba(239,68,68,.35)">
                <i class="bi bi-shield-lock-fill"></i> Admin Panel
              </a>
            </li>
            <?php endif; ?>

            <?php if (($user['role'] ?? '') !== 'admin'): ?>
            <!-- Chat -->
            <li class="relative">
              <a class="relative flex items-center justify-center w-9 h-9 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-all no-underline" href="<?= $appUrl ?>/chat" title="Tin nhắn">
                <i class="bi bi-chat-dots text-xl"></i>
                <span id="chatBadge" class="hidden absolute -top-0.5 -right-0.5 min-w-[1rem] h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1 leading-none"></span>
              </a>
            </li>
            <?php endif; ?>

            <?php if (($user['role'] ?? '') !== 'admin'): ?>
            <!-- Coins -->
            <li>
              <a href="<?= $appUrl ?>/rewards" class="flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold no-underline transition-all" style="background:rgba(255,220,80,.18);color:#fbbf24;border:1px solid rgba(255,220,80,.35)" title="Điểm Danh Bỏ Túi Xu">
                🪙 <?= (int)($user['coins'] ?? 0) ?> xu
              </a>
            </li>
            <?php endif; ?>

            <!-- Notification Bell -->
            <li x-data="{ notifOpen: false }" class="relative">
              <button @click="notifOpen = !notifOpen" class="relative flex items-center justify-center w-9 h-9 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-all border-0 bg-transparent cursor-pointer" title="Thông báo">
                <i class="bi bi-bell text-xl"></i>
                <span id="notifBadge" class="hidden absolute -top-0.5 -right-0.5 min-w-[1rem] h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1 leading-none"></span>
              </button>
              <div x-show="notifOpen" @click.outside="notifOpen = false" x-transition
                   class="absolute right-0 mt-2 w-80 max-h-96 overflow-y-auto bg-white dark:bg-dark-card rounded-2xl shadow-xl border border-gray-100 dark:border-dark-border z-50 py-2">
                <div class="px-3 py-1 text-xs font-bold text-gray-400 uppercase tracking-wider">Thông báo gần đây</div>
                <div id="notifList"><div class="px-4 py-2 text-xs text-gray-400">Đang tải...</div></div>
                <hr class="my-1 border-gray-100 dark:border-dark-border">
                <a class="block px-4 py-2 text-center text-xs font-semibold text-primary hover:bg-gray-50 dark:hover:bg-dark-2 no-underline" href="<?= $appUrl ?>/notifications">Xem tất cả thông báo</a>
              </div>
            </li>

            <!-- User Dropdown -->
            <li x-data="{ userOpen: false }" class="relative">
              <button @click="userOpen = !userOpen" class="flex items-center gap-2 px-2 py-1 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-all text-sm font-semibold border-0 bg-transparent cursor-pointer">
                <div class="nav-avatar">
                  <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= $appUrl ?>/users/avatar?id=<?= (int)($user['id'] ?? 0) ?>" alt="Avatar">
                  <?php elseif (!empty($user['avatar_url'])): ?>
                    <img src="<?= htmlspecialchars($user['avatar_url'], ENT_QUOTES) ?>" alt="Avatar">
                  <?php else: ?>
                    <?= mb_strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
                  <?php endif; ?>
                </div>
                <span><?= htmlspecialchars($user['name'] ?? 'Tài khoản', ENT_QUOTES) ?></span>
                <i class="bi bi-chevron-down text-xs opacity-70"></i>
              </button>
              <div x-show="userOpen" @click.outside="userOpen = false" x-transition
                   class="absolute right-0 mt-2 w-56 bg-white dark:bg-dark-card rounded-2xl shadow-xl border border-gray-100 dark:border-dark-border z-50 py-2">
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                  <div class="px-4 py-1 text-[11px] text-gray-400 font-bold uppercase tracking-widest">Quản trị viên</div>
                  <a class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-dark-text hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors no-underline" href="<?= $appUrl ?>/profile">
                    <i class="bi bi-person-circle text-primary"></i> Hồ sơ của tôi
                  </a>
                  <hr class="my-1 border-gray-100 dark:border-dark-border">
                  <a class="flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-indigo-500 hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors no-underline" href="<?= $appUrl ?>/admin">
                    <i class="bi bi-shield-lock-fill"></i> Admin Panel
                  </a>
                <?php else: ?>
                  <a class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-dark-text hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors no-underline" href="<?= $appUrl ?>/dashboard">
                    <i class="bi bi-speedometer2 text-primary"></i> Dashboard của tôi
                  </a>
                  <a class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-dark-text hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors no-underline" href="<?= $appUrl ?>/profile">
                    <i class="bi bi-person-circle text-primary"></i> Hồ sơ của tôi
                  </a>
                  <a class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-dark-text hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors no-underline" href="<?= $appUrl ?>/products/my">
                    <i class="bi bi-box-seam text-primary"></i> Sản phẩm của tôi
                  </a>
                  <a class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-dark-text hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors no-underline" href="<?= $appUrl ?>/transactions/history">
                    <i class="bi bi-receipt text-primary"></i> Lịch sử giao dịch
                  </a>
                  <a class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-dark-text hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors no-underline" href="<?= $appUrl ?>/chat">
                    <i class="bi bi-chat-dots text-primary"></i> Tin nhắn
                  </a>
                  <a class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors no-underline" href="<?= $appUrl ?>/#giveaway" style="color:var(--giveaway)">
                    <i class="bi bi-gift-fill" style="color:var(--giveaway)"></i> Sự kiện Giveaway
                  </a>
                <?php endif; ?>
                <hr class="my-1 border-gray-100 dark:border-dark-border">
                <button id="themeToggleBtn" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-dark-text hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors bg-transparent border-0 cursor-pointer text-left">
                  <i class="bi bi-moon-stars text-yellow-400"></i> Giao diện Tối
                </button>
                <hr class="my-1 border-gray-100 dark:border-dark-border">
                <a class="flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-red-500 hover:bg-red-50 dark:hover:bg-dark-2 transition-colors no-underline" href="<?= $appUrl ?>/logout">
                  <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </a>
              </div>
            </li>

          <?php else: ?>
            <li>
              <a class="flex items-center gap-1 px-3 py-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10 text-sm font-semibold transition-all no-underline" href="<?= $appUrl ?>/login-role">Đăng nhập</a>
            </li>
            <li>
              <a class="flex items-center gap-1 px-4 py-2 rounded-full text-white font-bold text-sm shadow-[0_4px_14px_rgba(99,102,241,.4)] hover:-translate-y-0.5 hover:brightness-110 transition-all no-underline" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)" href="<?= $appUrl ?>/register">
                <i class="bi bi-person-plus"></i> Đăng ký
              </a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <!-- Mobile Nav -->
    <div x-show="mobileOpen" x-transition class="lg:hidden pb-3 border-t border-white/10 mt-1">
      <form class="mt-3 mb-2" action="<?= $appUrl ?>/products" method="GET">
        <div class="flex rounded-full overflow-hidden">
          <input type="text" name="q" class="flex-1 px-4 py-2 text-sm border-0 outline-none" style="background:rgba(255,255,255,.15);color:#fff" placeholder="Tìm sản phẩm..." value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES) ?>">
          <button type="submit" class="px-3 border-0 flex-shrink-0" style="background:rgba(255,255,255,.2);color:#fff"><i class="bi bi-search"></i></button>
        </div>
      </form>
      <div class="flex flex-col gap-0.5">
        <a class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-white/80 hover:bg-white/10 text-sm font-semibold no-underline" href="<?= $appUrl ?>/products"><i class="bi bi-grid"></i> Sản phẩm</a>
        <a class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-white/80 hover:bg-white/10 text-sm font-semibold no-underline" href="<?= $appUrl ?>/leaderboard"><i class="bi bi-trophy"></i> Bảng Xếp Hạng</a>
        <?php if ($user): ?>
          <?php if (($user['role'] ?? '') !== 'admin'): ?>
          <a class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-white/80 hover:bg-white/10 text-sm font-semibold no-underline" href="<?= $appUrl ?>/products/create"><i class="bi bi-plus-circle"></i> Đăng bán</a>
          <a class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-white/80 hover:bg-white/10 text-sm font-semibold no-underline" href="<?= $appUrl ?>/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
          <a class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-white/80 hover:bg-white/10 text-sm font-semibold no-underline" href="<?= $appUrl ?>/chat"><i class="bi bi-chat-dots"></i> Tin nhắn</a>
          <?php endif; ?>
          <a class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-white/80 hover:bg-white/10 text-sm font-semibold no-underline" href="<?= $appUrl ?>/profile"><i class="bi bi-person-circle"></i> Hồ sơ</a>
          <a class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-red-300 hover:bg-white/10 text-sm font-semibold no-underline" href="<?= $appUrl ?>/logout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
        <?php else: ?>
          <a class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-white/80 hover:bg-white/10 text-sm font-semibold no-underline" href="<?= $appUrl ?>/login-role"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a>
          <a class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-white/80 hover:bg-white/10 text-sm font-semibold no-underline" href="<?= $appUrl ?>/register"><i class="bi bi-person-plus"></i> Đăng ký</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>


<!-- ─── Flash Message ────────────────────────────────── -->
<div class="flash-banner">
  <?= Flash::render() ?>
</div>

<!-- ─── Main Content ───────────────────────────────── -->
<main class="animate-[fadeInUp_0.4s_ease-out_both]">
  <?= $content ?>
</main>

<!-- ─── Footer ─────────────────────────────────────── -->
<footer class="site-footer">
  <div class="container mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
      <!-- Brand & About -->
      <div class="md:col-span-4">
        <div class="footer-brand"><i class="bi bi-shop-window mr-1"></i>SinhVienMarket</div>
        <p class="text-sm leading-relaxed opacity-70 mb-0">
          Nền tảng mua bán, trao đổi &amp; đấu giá ngược dành riêng cho sinh viên <strong style="color:rgba(255,255,255,.7)">KTX Đại học Quốc gia</strong>.
          Tiết kiệm chi phí, kết nối cộng đồng.
        </p>
        <div class="mt-3 flex gap-2">
          <a href="#" class="w-9 h-9 flex items-center justify-center rounded-lg transition-colors no-underline" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.6)" title="Facebook" onmouseover="this.style.background='rgba(255,255,255,.18)'" onmouseout="this.style.background='rgba(255,255,255,.08)'">
            <i class="bi bi-facebook"></i>
          </a>
          <a href="#" class="w-9 h-9 flex items-center justify-center rounded-lg transition-colors no-underline" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.6)" title="Zalo" onmouseover="this.style.background='rgba(255,255,255,.18)'" onmouseout="this.style.background='rgba(255,255,255,.08)'">
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
      <div class="md:col-span-2">
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
      <div class="md:col-span-4">
        <div class="footer-heading">Thông tin</div>
        <ul class="footer-links">
          <li><i class="bi bi-geo-alt mr-2"></i>KTX Đại học Quốc gia TP.HCM</li>
          <li><i class="bi bi-envelope mr-2"></i>support@sinhvienmarket.edu.vn</li>
          <li><i class="bi bi-clock mr-2"></i>Hỗ trợ: 8:00 - 22:00 hàng ngày</li>
        </ul>
      </div>
    </div>

    <hr class="footer-divider">
    <div class="flex justify-between items-center flex-wrap gap-2 footer-bottom">
      <span>© <?= date('Y') ?> SinhVienMarket — Đồ án cơ sở ngành Công nghệ thông tin</span>
      <span>Made with <span style="color:#ef4444">♥</span> for students</span>
    </div>
  </div>
</footer>

<!-- Alpine.js (thay thế Bootstrap JS) -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
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
  document.querySelectorAll('.alert').forEach(function(el) {
    setTimeout(function() { el.style.opacity = '0'; el.style.transition = 'opacity .4s'; setTimeout(() => el.remove(), 400); }, 5000);
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

<?php if ($user): ?>
<script>
// ─── Polling Notifications & Chat Badges ────────────────────────────────────
(function() {
  const BASE = '<?= $appUrl ?>';

  function typeIcon(type) {
    const icons = {
      product_approved: '✅',
      product_rejected: '❌',
      item_sold:        '🎉',
      wishlist_drop:    '📉',
      new_message:      '💬',
    };
    return icons[type] || '🔔';
  }

  function setBadge(badge, count) {
    if (!badge) return;
    if (count > 0) {
      badge.textContent = count > 9 ? '9+' : count;
      badge.classList.remove('hidden'); // dùng class, KHÔNG dùng style.display (bị 'hidden' đè)
    } else {
      badge.classList.add('hidden');
    }
  }

  async function pollNotifications() {
    try {
      const res = await fetch(BASE + '/api/notifications/unread');
      const json = await res.json();
      const d = json.data || json; // response là {data:{count,items}}
      const count = d.count || 0;
      const items = d.items || [];
      setBadge(document.getElementById('notifBadge'), count);
      const list = document.getElementById('notifList');
      if (list && items.length > 0) {
        list.innerHTML = items.map(n =>
          `<a class="block px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-dark-2 no-underline border-b border-gray-50 dark:border-dark-border" href="${n.link || BASE + '/notifications'}" style="white-space:normal">
            <div style="font-weight:600;font-size:.8rem" class="text-gray-800 dark:text-dark-text">${typeIcon(n.type)} ${n.title}</div>
            ${n.body ? `<div style="font-size:.73rem" class="text-gray-500 dark:text-gray-400">${n.body}</div>` : ''}
            <div style="font-size:.68rem;margin-top:2px;opacity:.8" class="text-gray-400">${n.time}</div>
          </a>`
        ).join('');
      } else if (list) {
        list.innerHTML = '<div class="px-4 py-3 text-gray-400" style="font-size:.8rem">Đã đọc hết thông báo.</div>';
      }
    } catch(e) {}
  }

  async function pollChat() {
    try {
      const res = await fetch(BASE + '/api/chat/unread');
      const json = await res.json();
      const count = (json.data && json.data.count) || 0;
      setBadge(document.getElementById('chatBadge'), count);
    } catch(e) {}
  }

  pollNotifications();
  pollChat();
  setInterval(pollNotifications, 8000);
  setInterval(pollChat, 8000);
})();
</script>
<?php endif; ?>

<?php
// ─── Giveaway Popup ───────────────────────────────────────────────────────────
// Chỉ load Giveaway đang active, hiển thị popup nếu có
use App\Models\Giveaway;
$_giveawayModel   = new Giveaway();
$_activeGiveaways = $_giveawayModel->getActive();
if (!empty($_activeGiveaways)):
  $_gw = $_activeGiveaways[0]; // Hiển thị sự kiện gần nhất
?>

<!-- ═══ GIVEAWAY POPUP ═══════════════════════════════════════════════════════ -->
<div id="gwOverlay" style="
  display:none; position:fixed; inset:0; z-index:99999;
  background:rgba(10,10,30,.75); backdrop-filter:blur(8px);
  align-items:center; justify-content:center;
  animation:gwFadeIn .35s ease forwards;
">

  <!-- Confetti particles -->
  <div id="gwConfetti" style="position:absolute;inset:0;pointer-events:none;overflow:hidden"></div>

  <!-- Modal box -->
  <div id="gwBox" style="
    position:relative; z-index:2; width:100%; max-width:520px; margin:1rem;
    background:#fff; border-radius:28px; overflow:hidden;
    box-shadow:0 40px 100px rgba(0,0,0,.5);
    animation:gwSlideUp .4s cubic-bezier(.16,1,.3,1) forwards;
  ">

    <!-- Gradient header -->
    <div style="
      background:linear-gradient(135deg,#7c3aed 0%,#6d28d9 30%,#ec4899 70%,#f59e0b 100%);
      padding:2rem 1.75rem 1.5rem; text-align:center; position:relative;
    ">
      <!-- Floating emoji -->
      <div style="font-size:3.5rem; line-height:1; margin-bottom:.75rem; filter:drop-shadow(0 4px 12px rgba(0,0,0,.3))">🎁</div>
      <div style="
        display:inline-block; background:rgba(255,255,255,.2); color:#fff;
        font-size:.72rem; font-weight:800; letter-spacing:1.5px; text-transform:uppercase;
        padding:.3rem .9rem; border-radius:50px; margin-bottom:.6rem; border:1px solid rgba(255,255,255,.3);
      ">🎉 Sự kiện đặc biệt</div>
      <h2 style="color:#fff; font-size:1.45rem; font-weight:900; margin:0; line-height:1.25; text-shadow:0 2px 8px rgba(0,0,0,.25)">
        <?= htmlspecialchars($_gw['title'], ENT_QUOTES) ?>
      </h2>
      <!-- Close btn -->
      <button onclick="closeGwPopup()" style="
        position:absolute; top:12px; right:14px; background:rgba(255,255,255,.2);
        border:none; color:#fff; width:32px; height:32px; border-radius:50%;
        font-size:1rem; cursor:pointer; display:flex; align-items:center; justify-content:center;
        transition:.2s; line-height:1;
      " title="Đóng" onmouseover="this.style.background='rgba(255,255,255,.35)'" onmouseout="this.style.background='rgba(255,255,255,.2)'">✕</button>
    </div>

    <!-- Body -->
    <div style="padding:1.5rem 1.75rem">

      <!-- Description -->
      <?php if (!empty($_gw['description'])): ?>
        <p style="color:#374151; font-size:.9rem; line-height:1.6; margin-bottom:1.25rem; text-align:center">
          <?= nl2br(htmlspecialchars($_gw['description'], ENT_QUOTES)) ?>
        </p>
      <?php endif; ?>

      <!-- Countdown -->
      <div style="
        background:linear-gradient(135deg,#f5f3ff,#fdf2f8); border-radius:16px;
        padding:1rem 1.25rem; margin-bottom:1.25rem; text-align:center;
        border:1.5px solid #e9d5ff;
      ">
        <div style="font-size:.75rem; font-weight:700; color:#7c3aed; text-transform:uppercase; letter-spacing:.8px; margin-bottom:.5rem">
          ⏰ Kết thúc sau
        </div>
        <div id="gwCountdown" style="font-size:1.8rem; font-weight:900; color:#6d28d9; font-variant-numeric:tabular-nums; letter-spacing:2px">
          --:--:--
        </div>
        <div style="font-size:.73rem; color:#9ca3af; margin-top:.3rem">
          Hạn cuối: <?= date('d/m/Y H:i', strtotime($_gw['end_time'])) ?>
        </div>
      </div>

      <!-- Count participants if available -->
      <div style="display:flex; gap:.75rem; justify-content:center; margin-bottom:1.25rem">
        <div style="text-align:center">
          <div style="font-size:1.3rem; font-weight:900; color:#7c3aed"><?= count($_giveawayModel->getParticipants($_gw['id'])) ?></div>
          <div style="font-size:.72rem; color:#6b7280">Người đã tham gia</div>
        </div>
        <div style="width:1px; background:#e5e7eb"></div>
        <div style="text-align:center">
          <div style="font-size:1.3rem; font-weight:900; color:#ec4899"><?= count($_activeGiveaways) ?></div>
          <div style="font-size:.72rem; color:#6b7280">Sự kiện đang diễn ra</div>
        </div>
      </div>

      <!-- CTA Buttons -->
      <div style="display:flex; gap:.75rem; flex-direction:column">
        <a href="<?= $appUrl ?>/rewards#giveaway"
           onclick="closeGwPopup()"
           style="
             display:flex; align-items:center; justify-content:center; gap:.6rem;
             background:linear-gradient(135deg,#7c3aed,#ec4899);
             color:#fff; border-radius:14px; padding:.9rem; font-weight:800;
             font-size:1rem; text-decoration:none; transition:.25s;
             box-shadow:0 8px 24px rgba(124,58,237,.4);
           "
           onmouseover="this.style.opacity='.9';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.opacity='1';this.style.transform='none'"
        >
          <span style="font-size:1.2rem">🎰</span> Tham gia Giveaway ngay!
        </a>
        <button onclick="closeGwPopup(true)"
          style="
            background:#f9fafb; color:#6b7280; border:1.5px solid #e5e7eb;
            border-radius:14px; padding:.7rem; font-weight:600; font-size:.875rem;
            cursor:pointer; transition:.2s;
          "
          onmouseover="this.style.background='#f3f4f6'"
          onmouseout="this.style.background='#f9fafb'"
        >Nhắc lại lần sau</button>
        <button onclick="hideGwForever()"
          style="background:none; border:none; color:#9ca3af; font-size:.78rem; text-decoration:underline; cursor:pointer; padding:.25rem; margin-top:-.2rem"
          title="Ẩn sự kiện này, không hiện lại nữa">Không hiện lại sự kiện này</button>
      </div>
    </div>
  </div>
</div>

<style>
@keyframes gwFadeIn  { from{opacity:0} to{opacity:1} }
@keyframes gwSlideUp { from{transform:translateY(40px) scale(.95);opacity:0} to{transform:none;opacity:1} }
@keyframes gwFloat   { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
#gwBox .emoji-float  { animation:gwFloat 2.5s ease-in-out infinite }
</style>

<script>
(function() {
  // Key theo ID sự kiện, để khi có sự kiện mới thì popup lại
  const GW_KEY = 'gw_seen_<?= $_gw['id'] ?>_<?= date('Ymd') ?>';

  function launchConfetti() {
    const wrap = document.getElementById('gwConfetti');
    if (!wrap) return;
    const colors = ['#7c3aed','#ec4899','#f59e0b','#10b981','#fff','#f3f4f6'];
    for (let i = 0; i < 90; i++) {
      const el = document.createElement('div');
      const size = Math.random() * 10 + 6;
      el.style.cssText = `
        position:absolute;
        left:${Math.random()*100}%;
        top:-${Math.random()*120+20}px;
        width:${size}px; height:${size}px;
        background:${colors[Math.floor(Math.random()*colors.length)]};
        border-radius:${Math.random()>.5?'50%':'3px'};
        opacity:${Math.random()*.9+.1};
        animation:gwDrop ${Math.random()*2+2}s linear ${Math.random()*1.5}s forwards;
        transform:rotate(${Math.random()*360}deg);
      `;
      wrap.appendChild(el);
    }
    const style = document.createElement('style');
    style.textContent = `@keyframes gwDrop { to { top:110%; transform:rotate(${Math.random()*720}deg); } }`;
    document.head.appendChild(style);
  }

  function startCountdown() {
    const endTime = new Date('<?= date('Y-m-d\TH:i:s', strtotime($_gw['end_time'])) ?>').getTime();
    const el = document.getElementById('gwCountdown');
    if (!el) return;
    function tick() {
      const diff = endTime - Date.now();
      if (diff <= 0) { el.textContent = 'Đã kết thúc'; return; }
      const h = Math.floor(diff / 3600000);
      const m = Math.floor((diff % 3600000) / 60000);
      const s = Math.floor((diff % 60000) / 1000);
      el.textContent = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
    }
    tick();
    setInterval(tick, 1000);
  }

  function openGwPopup() {
    const overlay = document.getElementById('gwOverlay');
    if (!overlay) return;
    overlay.style.display = 'flex';
    launchConfetti();
    startCountdown();
  }

  window.closeGwPopup = function(dismissForToday = false) {
    document.getElementById('gwOverlay').style.display = 'none';
    // Nếu bấm "Nhắc lại lần sau" → lưu theo session (sessionStorage)
    // Nếu bấm "Tham gia ngay" hay X → lưu vào localStorage hết hôm nay
    if (dismissForToday) {
      sessionStorage.setItem(GW_KEY, '1');
    } else {
      localStorage.setItem(GW_KEY, '1');
    }
  };

  // Ẩn VĨNH VIỄN sự kiện này (không hiện lại kể cả ngày sau)
  window.hideGwForever = function () {
    document.getElementById('gwOverlay').style.display = 'none';
    localStorage.setItem('gw_hidden_<?= $_gw['id'] ?>', '1');
  };

  // Click nền để đóng
  document.getElementById('gwOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeGwPopup();
  });

  // Kiểm tra xem đã thấy / đã ẩn vĩnh viễn chưa
  const seenSession    = sessionStorage.getItem(GW_KEY);
  const seenLocal      = localStorage.getItem(GW_KEY);
  const hiddenForever  = localStorage.getItem('gw_hidden_<?= $_gw['id'] ?>');
  if (!seenSession && !seenLocal && !hiddenForever) {
    // Delay nhỏ cho trang render xong rồi mới popup
    setTimeout(openGwPopup, 800);
  }
})();
</script>
<?php endif; ?>

<?php
// ─── Popup: Chúc mừng TRÚNG GIVEAWAY ──────────────────────────────────────
if (isset($_SESSION['user'])):
    $_wins = (new \App\Models\Giveaway())->getWinsByUser((int)$_SESSION['user']['id']);
    if (!empty($_wins)):
        $_win = $_wins[0]; // sự kiện trúng gần nhất
?>
<div id="winOverlay" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(10,10,30,.78); backdrop-filter:blur(8px); align-items:center; justify-content:center;">
  <div style="position:relative; width:100%; max-width:460px; margin:1rem; background:#fff; border-radius:28px; overflow:hidden; box-shadow:0 40px 100px rgba(0,0,0,.5); animation:gwSlideUp .4s cubic-bezier(.16,1,.3,1) forwards;">
    <div style="background:linear-gradient(135deg,#f59e0b 0%,#f97316 45%,#ec4899 100%); padding:2rem 1.75rem 1.5rem; text-align:center; position:relative;">
      <div style="font-size:3.5rem; line-height:1; margin-bottom:.5rem; filter:drop-shadow(0 4px 12px rgba(0,0,0,.3))">🏆</div>
      <div style="display:inline-block; background:rgba(255,255,255,.2); color:#fff; font-size:.72rem; font-weight:800; letter-spacing:1.5px; text-transform:uppercase; padding:.3rem .9rem; border-radius:50px; margin-bottom:.6rem; border:1px solid rgba(255,255,255,.3)">🎉 Chúc mừng</div>
      <h2 style="color:#fff; font-size:1.4rem; font-weight:900; margin:0; text-shadow:0 2px 8px rgba(0,0,0,.25)">Bạn đã trúng giải!</h2>
      <button onclick="closeWinPopup()" style="position:absolute; top:12px; right:14px; background:rgba(255,255,255,.2); border:none; color:#fff; width:32px; height:32px; border-radius:50%; font-size:1rem; cursor:pointer; line-height:1">✕</button>
    </div>
    <div style="padding:1.75rem; text-align:center">
      <p style="color:#374151; font-size:.95rem; line-height:1.65; margin-bottom:1.25rem">
        Bạn là người may mắn trúng thưởng sự kiện<br>
        <strong style="color:#f59e0b; font-size:1.05rem"><?= htmlspecialchars($_win['title'], ENT_QUOTES) ?></strong><br>
        Vui lòng liên hệ Ban Quản Trị để nhận quà nhé! 🎁
      </p>
      <button onclick="closeWinPopup()" style="width:100%; padding:.85rem; border:none; border-radius:14px; background:linear-gradient(135deg,#f59e0b,#f97316); color:#fff; font-weight:800; font-size:.95rem; cursor:pointer">Tuyệt vời! 🎉</button>
    </div>
  </div>
</div>
<script>
(function(){
  var key = 'svm_win_<?= (int)$_win['id'] ?>';
  if (!localStorage.getItem(key)) {
    setTimeout(function(){ var o=document.getElementById('winOverlay'); if(o) o.style.display='flex'; }, 600);
  }
  window.closeWinPopup = function(){ var o=document.getElementById('winOverlay'); if(o) o.style.display='none'; localStorage.setItem(key,'1'); };
})();
</script>
<?php endif; endif; ?>

<?php
// ─── Popup: CẢNH BÁO VI PHẠM ──────────────────────────────────────────────
if (isset($_SESSION['user'])):
    $_violation = (new \App\Models\User())->getLatestViolation((int)$_SESSION['user']['id']);
    if ($_violation):
?>
<div id="violOverlay" style="display:none; position:fixed; inset:0; z-index:99998; background:rgba(20,5,5,.82); backdrop-filter:blur(8px); align-items:center; justify-content:center;">
  <div style="position:relative; width:100%; max-width:460px; margin:1rem; background:#fff; border-radius:28px; overflow:hidden; box-shadow:0 40px 100px rgba(0,0,0,.5); animation:gwSlideUp .4s cubic-bezier(.16,1,.3,1) forwards;">
    <div style="background:linear-gradient(135deg,#dc2626 0%,#b91c1c 50%,#7f1d1d 100%); padding:2rem 1.75rem 1.5rem; text-align:center; position:relative;">
      <div style="font-size:3.5rem; line-height:1; margin-bottom:.5rem; filter:drop-shadow(0 4px 12px rgba(0,0,0,.3))">⚠️</div>
      <div style="display:inline-block; background:rgba(255,255,255,.2); color:#fff; font-size:.72rem; font-weight:800; letter-spacing:1.5px; text-transform:uppercase; padding:.3rem .9rem; border-radius:50px; margin-bottom:.6rem; border:1px solid rgba(255,255,255,.3)">🚨 Cảnh báo vi phạm</div>
      <h2 style="color:#fff; font-size:1.3rem; font-weight:900; margin:0; text-shadow:0 2px 8px rgba(0,0,0,.25)">Tài khoản của bạn bị nhắc nhở</h2>
      <button onclick="closeViolPopup()" style="position:absolute; top:12px; right:14px; background:rgba(255,255,255,.2); border:none; color:#fff; width:32px; height:32px; border-radius:50%; font-size:1rem; cursor:pointer; line-height:1">✕</button>
    </div>
    <div style="padding:1.75rem; text-align:center">
      <div style="background:#fef2f2; border:1.5px solid #fecaca; border-radius:14px; padding:1rem 1.25rem; margin-bottom:1rem; text-align:left">
        <div style="font-size:.72rem; font-weight:800; color:#b91c1c; text-transform:uppercase; letter-spacing:.6px; margin-bottom:.4rem">Lý do vi phạm</div>
        <div style="color:#7f1d1d; font-size:.95rem; font-weight:600"><?= htmlspecialchars($_violation['reason'], ENT_QUOTES) ?></div>
        <div style="font-size:.78rem; color:#9ca3af; margin-top:.5rem">Lần cảnh cáo thứ <?= (int)$_violation['strike_number'] ?> · <?= date('d/m/Y H:i', strtotime($_violation['created_at'])) ?></div>
      </div>
      <p style="color:#6b7280; font-size:.85rem; line-height:1.55; margin-bottom:1.25rem">Vui lòng tuân thủ quy định cộng đồng. Vi phạm nhiều lần có thể dẫn đến <strong style="color:#b91c1c">khóa tài khoản vĩnh viễn</strong>.</p>
      <button onclick="closeViolPopup()" style="width:100%; padding:.85rem; border:none; border-radius:14px; background:linear-gradient(135deg,#dc2626,#b91c1c); color:#fff; font-weight:800; font-size:.95rem; cursor:pointer">Tôi đã hiểu</button>
    </div>
  </div>
</div>
<script>
(function(){
  var key = 'svm_viol_<?= (int)$_violation['id'] ?>';
  if (!localStorage.getItem(key)) {
    setTimeout(function(){ var o=document.getElementById('violOverlay'); if(o) o.style.display='flex'; }, 900);
  }
  window.closeViolPopup = function(){ var o=document.getElementById('violOverlay'); if(o) o.style.display='none'; localStorage.setItem(key,'1'); };
})();
</script>
<?php endif; endif; ?>

<script>
  // Dark Mode Toggle Logic
  document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('themeToggleBtn');
    if (!btn) return;

    function updateBtn() {
      const isDark = document.documentElement.classList.contains('dark');
      if (isDark) {
        btn.innerHTML = '<i class="bi bi-sun-fill mr-2 text-yellow-400"></i>Giao diện Sáng';
      } else {
        btn.innerHTML = '<i class="bi bi-moon-stars mr-2 text-yellow-400"></i>Giao diện Tối';
      }
    }
    updateBtn();

    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const isDark = document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
      updateBtn();
    });

    // Smart Search Autocomplete
    const searchInput = document.getElementById('mainSearchInput');
    const searchDropdown = document.getElementById('searchDropdown');
    const searchContent = document.getElementById('searchDropdownContent');
    let searchTimeout = null;

    if (searchInput && searchDropdown) {
      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
          searchDropdown.style.display = 'none';
          return;
        }

        searchTimeout = setTimeout(() => {
          fetch('<?= $appUrl ?>/api/products/search?q=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(data => {
              if (data.length > 0) {
                let html = '';
                data.forEach(item => {
                  const priceFmt = new Intl.NumberFormat('vi-VN').format(item.price) + 'đ';
                  const badge = item.type === 'auction' ? '<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black bg-amber-400 text-amber-900 ml-1">Đấu giá</span>' : '';
                  html += `
                    <a href="${item.url}" class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors no-underline border-b border-gray-50 dark:border-dark-border last:border-0">
                      <img src="${item.image_url}" alt="IMG" class="w-10 h-10 object-cover rounded-lg bg-gray-100 flex-shrink-0" loading="lazy">
                      <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-gray-800 dark:text-dark-text truncate">${item.title} ${badge}</div>
                        <div class="text-red-500 font-black text-xs">${priceFmt}</div>
                      </div>
                    </a>
                  `;
                });
                searchContent.innerHTML = html;
                searchDropdown.classList.remove('hidden');
              } else {
                searchContent.innerHTML = '<div class="p-4 text-center text-sm text-gray-400">Không tìm thấy sản phẩm nào phù hợp.</div>';
                searchDropdown.classList.remove('hidden');
              }
            })
            .catch(e => console.error('Search error:', e));
        }, 350); // debounce 350ms
      });

      // Đóng dropdown khi click ra ngoài
      document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
          searchDropdown.classList.add('hidden');
        }
      });
      searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 2 && searchContent.innerHTML !== '') {
          searchDropdown.classList.remove('hidden');
        }
      });
    }
  });
</script>

<!-- ─── Toast Notification System ──────────────────────────────────────────────── -->
<div id="toastContainer" class="fixed bottom-4 right-4 z-[99999] flex flex-col gap-2.5 pointer-events-none max-w-[340px] w-full"></div>

<!-- ─── Scroll-to-top Button ────────────────────────────────────────────────── -->
<button id="scrollToTopBtn"
  onclick="window.scrollTo({top:0,behavior:'smooth'})"
  class="fixed bottom-[72px] right-4 z-[9998] w-11 h-11 rounded-full bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border shadow-lg text-gray-500 dark:text-gray-300 flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary hover:shadow-primary/30 transition-all opacity-0 translate-y-4 pointer-events-none cursor-pointer">
  <i class="bi bi-chevron-up font-bold"></i>
</button>

<script>
// ─── Global Toast System ───────────────────────────────────────────────────────
window.showToast = function(message, type = 'info', duration = 4000) {
  const icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
  const colors = {
    success: 'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-700 text-green-800 dark:text-green-300',
    error:   'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-700 text-red-800 dark:text-red-300',
    warning: 'bg-amber-50 dark:bg-amber-900/30 border-amber-200 dark:border-amber-700 text-amber-800 dark:text-amber-300',
    info:    'bg-blue-50 dark:bg-blue-900/30 border-blue-200 dark:border-blue-700 text-blue-800 dark:text-blue-300',
  };
  const iconColors = { success: 'text-green-500', error: 'text-red-500', warning: 'text-amber-500', info: 'text-blue-500' };
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = `pointer-events-auto flex items-start gap-3 px-4 py-3.5 rounded-2xl border shadow-xl backdrop-blur-sm ${colors[type] || colors.info} transform translate-y-4 opacity-0 transition-all duration-300`;
  toast.innerHTML = `
    <i class="bi ${icons[type] || icons.info} text-lg flex-shrink-0 mt-0.5 ${iconColors[type]}"></i>
    <span class="text-sm font-semibold flex-1 leading-relaxed">${message}</span>
    <button onclick="this.parentElement.remove()" class="flex-shrink-0 text-current opacity-50 hover:opacity-100 bg-transparent border-0 cursor-pointer text-lg leading-none mt-0.5">&times;</button>
  `;
  container.appendChild(toast);
  void toast.offsetWidth;
  toast.classList.remove('translate-y-4', 'opacity-0');
  setTimeout(() => {
    toast.classList.add('translate-y-4', 'opacity-0');
    setTimeout(() => toast.remove(), 300);
  }, duration);
};

// ─── Scroll-to-top visibility ───────────────────────────────────────────────────
(function() {
  const btn = document.getElementById('scrollToTopBtn');
  if (!btn) return;
  window.addEventListener('scroll', () => {
    const show = window.scrollY > 400;
    btn.classList.toggle('opacity-100', show);
    btn.classList.toggle('translate-y-0', show);
    btn.classList.toggle('pointer-events-auto', show);
    btn.classList.toggle('opacity-0', !show);
    btn.classList.toggle('translate-y-4', !show);
    btn.classList.toggle('pointer-events-none', !show);
  }, { passive: true });
})();

// ─── Image Lazy Loading ─────────────────────────────────────────────────────────
// Add loading="lazy" to all product/content images that don't already have it
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('img:not([loading])').forEach(img => {
    // Skip avatars/icons in header area (above the fold)
    if (!img.closest('nav') && !img.closest('.navbar-main') && !img.closest('#cropModal') && !img.closest('#gwBox')) {
      img.setAttribute('loading', 'lazy');
    }
  });
});

// ─── Real-time form validation ────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  // Highlight required fields that are empty on blur
  document.querySelectorAll('input[required], textarea[required], select[required]').forEach(input => {
    input.addEventListener('blur', function() {
      if (!this.value.trim()) {
        this.classList.add('border-red-400', 'ring-2', 'ring-red-200');
        this.classList.remove('border-green-400', 'ring-green-200');
      } else {
        this.classList.remove('border-red-400', 'ring-2', 'ring-red-200');
        this.classList.add('border-green-400', 'ring-2', 'ring-green-200');
      }
    });
    input.addEventListener('input', function() {
      if (this.value.trim()) {
        this.classList.remove('border-red-400', 'ring-red-200');
      }
    });
  });

  // Form submit: scroll to first invalid field
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
      const firstInvalid = form.querySelector(':invalid');
      if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.classList.add('border-red-400', 'ring-2', 'ring-red-200');
        firstInvalid.focus();
      }
    });
  });
});
</script>
</body>
</html>
