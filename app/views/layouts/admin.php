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

  <!-- Bootstrap Icons (giữ icon set) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Tailwind CSS + Custom Design System -->
  <link href="<?= $appUrl ?>/public/css/app.css" rel="stylesheet">
  <!-- Legacy style.css (animations, CSS vars) -->
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
  <!-- Ẩn phần tử Alpine (vd sidebar mobile) cho tới khi Alpine khởi tạo xong -->
  <style>[x-cloak]{display:none!important}</style>

  <script>
    // Dark mode: thêm class 'dark' trước khi render để tránh FOUC
    if (localStorage.getItem('theme') === 'dark') {
      document.documentElement.classList.add('dark');
    }
  </script>
</head>
<body class="bg-light-bg dark:bg-dark-bg font-sans antialiased" x-data="{ sidebarOpen: false }">

<!-- ═══ Admin Wrapper ═══════════════════════════════════════════════════════ -->
<div class="flex min-h-screen">

  <!-- ─── Sidebar ─────────────────────────────────────────────────────────── -->
  <!-- Desktop sidebar (always visible ≥ lg) -->
  <aside class="hidden lg:flex flex-col fixed top-0 left-0 bottom-0 w-64 z-50 overflow-y-auto overflow-x-hidden"
         style="background:linear-gradient(180deg,#1e1b4b 0%,#0f172a 100%);border-right:1px solid rgba(255,255,255,.05);box-shadow:4px 0 24px rgba(0,0,0,.25)">

    <!-- Brand -->
    <a class="flex items-center gap-3 px-5 py-5 no-underline border-b border-white/[.07] flex-shrink-0" href="<?= $appUrl ?>/admin">
      <div class="w-10 h-10 flex items-center justify-center rounded-[10px] text-white text-lg flex-shrink-0"
           style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 12px rgba(99,102,241,.5)">
        <i class="bi bi-shield-lock"></i>
      </div>
      <div>
        <div class="text-white font-black text-base">Admin<span class="text-indigo-300">Panel</span></div>
        <div class="text-white/35 text-[11px] font-bold uppercase tracking-wider">SinhVienMarket</div>
      </div>
    </a>

    <!-- Nav -->
    <nav class="flex-1 px-3 py-3 space-y-0.5">

      <!-- Tổng quan -->
      <div class="px-3 pt-3 pb-1 text-white/30 text-[11px] font-bold uppercase tracking-widest">Tổng quan</div>
      <a href="<?= $appUrl ?>/admin"
         class="sidebar-link <?= isActive('/admin', $currentUrl) && !str_contains($currentUrl, 'users') && !str_contains($currentUrl, 'products') && !str_contains($currentUrl, 'categories') && !str_contains($currentUrl, 'reports') && !str_contains($currentUrl, 'audit') && !str_contains($currentUrl, 'giveaway') && !str_contains($currentUrl, 'banner') && !str_contains($currentUrl, 'ratings') && !str_contains($currentUrl, 'system') ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>

      <!-- Quản lý -->
      <div class="px-3 pt-4 pb-1 text-white/30 text-[11px] font-bold uppercase tracking-widest">Quản lý</div>
      <a href="<?= $appUrl ?>/admin/users" class="sidebar-link <?= isActive('admin/users', $currentUrl) ?>">
        <i class="bi bi-people"></i> Người dùng
      </a>
      <a href="<?= $appUrl ?>/admin/products" class="sidebar-link <?= isActive('admin/products', $currentUrl) ?>">
        <i class="bi bi-card-checklist"></i> Kiểm duyệt bài
      </a>
      <a href="<?= $appUrl ?>/admin/categories" class="sidebar-link <?= isActive('admin/categories', $currentUrl) ?>">
        <i class="bi bi-tags"></i> Danh mục
      </a>
      <a href="<?= $appUrl ?>/admin/giveaways" class="sidebar-link <?= isActive('admin/giveaways', $currentUrl) ?>">
        <i class="bi bi-gift"></i> Sự kiện Giveaway
      </a>
      <a href="<?= $appUrl ?>/admin/banners" class="sidebar-link <?= isActive('admin/banners', $currentUrl) ?>">
        <i class="bi bi-images"></i> Quản lý Banner
      </a>

      <!-- Báo cáo & Vi Phạm -->
      <div class="px-3 pt-4 pb-1 text-white/30 text-[11px] font-bold uppercase tracking-widest">Báo cáo & Vi Phạm</div>
      <a href="<?= $appUrl ?>/admin/reports" class="sidebar-link <?= isActive('admin/reports', $currentUrl) ?>">
        <i class="bi bi-bar-chart-line"></i> Báo cáo giao dịch
      </a>
      <a href="<?= $appUrl ?>/admin/system-reports" class="sidebar-link <?= isActive('system-reports', $currentUrl) ?>">
        <i class="bi bi-shield-exclamation"></i> Tố cáo vi phạm
      </a>
      <a href="<?= $appUrl ?>/admin/ratings" class="sidebar-link <?= isActive('admin/ratings', $currentUrl) ?>">
        <i class="bi bi-star-half"></i> Kiểm duyệt Đánh giá
      </a>
      <a href="<?= $appUrl ?>/admin/audit-log" class="sidebar-link <?= isActive('audit-log', $currentUrl) ?>">
        <i class="bi bi-journal-text"></i> Nhật ký Admin
      </a>
    </nav>

    <!-- Sidebar Footer -->
    <div class="px-3 py-3 border-t border-white/[.06] flex-shrink-0">
      <a href="<?= $appUrl ?>/products" target="_blank"
         class="flex items-center gap-2 px-3 py-2 rounded-lg text-white/40 hover:text-white/80 hover:bg-white/[.07] text-sm font-semibold no-underline transition-all">
        <i class="bi bi-box-arrow-up-right"></i> Xem trang web
      </a>
    </div>
  </aside>

  <!-- Mobile Sidebar Overlay -->
  <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
       class="lg:hidden fixed inset-0 z-40 bg-black/60 backdrop-blur-sm" x-transition.opacity></div>

  <!-- Mobile Sidebar Panel -->
  <aside x-show="sidebarOpen" x-cloak x-transition:enter="transition transform duration-300"
         x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition transform duration-300"
         x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
         class="lg:hidden fixed top-0 left-0 bottom-0 w-64 z-50 overflow-y-auto flex flex-col"
         style="background:linear-gradient(180deg,#1e1b4b 0%,#0f172a 100%)">

    <!-- Brand -->
    <div class="flex items-center justify-between px-5 py-4 border-b border-white/[.07]">
      <a href="<?= $appUrl ?>/admin" class="flex items-center gap-3 no-underline">
        <div class="w-9 h-9 flex items-center justify-center rounded-[10px] text-white flex-shrink-0"
             style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
          <i class="bi bi-shield-lock"></i>
        </div>
        <div class="text-white font-black text-sm">Admin<span class="text-indigo-300">Panel</span></div>
      </a>
      <button @click="sidebarOpen = false" class="text-white/50 hover:text-white border-0 bg-transparent cursor-pointer text-xl">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <!-- Mobile Nav Links -->
    <nav class="flex-1 px-3 py-3 space-y-0.5 overflow-y-auto">
      <a href="<?= $appUrl ?>/admin" class="sidebar-link">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
      <a href="<?= $appUrl ?>/admin/users" class="sidebar-link">
        <i class="bi bi-people"></i> Người dùng
      </a>
      <a href="<?= $appUrl ?>/admin/products" class="sidebar-link">
        <i class="bi bi-card-checklist"></i> Kiểm duyệt bài
      </a>
      <a href="<?= $appUrl ?>/admin/categories" class="sidebar-link">
        <i class="bi bi-tags"></i> Danh mục
      </a>
      <a href="<?= $appUrl ?>/admin/reports" class="sidebar-link">
        <i class="bi bi-bar-chart-line"></i> Báo cáo
      </a>
      <a href="<?= $appUrl ?>/admin/audit-log" class="sidebar-link">
        <i class="bi bi-journal-text"></i> Nhật ký
      </a>
    </nav>
  </aside>

  <!-- ─── Main Area ──────────────────────────────────────────────────────── -->
  <div class="flex flex-col flex-1 lg:ml-64">

    <!-- Topbar -->
    <header class="sticky top-0 z-30 flex items-center justify-between px-5 py-3
                   bg-white dark:bg-dark-card border-b border-gray-100 dark:border-dark-border
                   shadow-xs">

      <!-- Left: Mobile toggle + Page title -->
      <div class="flex items-center gap-3">
        <!-- Hamburger (mobile only) -->
        <button @click="sidebarOpen = true"
                class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg
                       text-gray-500 hover:bg-gray-100 dark:hover:bg-dark-2
                       border-0 bg-transparent cursor-pointer transition-colors">
          <i class="bi bi-list text-xl"></i>
        </button>

        <!-- Page title with accent bar -->
        <h5 class="m-0 font-extrabold text-lg text-gray-800 dark:text-dark-text flex items-center gap-2">
          <span class="w-1 h-5 rounded-full bg-grad-primary inline-block flex-shrink-0"></span>
          <?= $title ?>
        </h5>
      </div>

      <!-- Right: Dark mode + User info -->
      <div class="flex items-center gap-2">

        <!-- Dark mode toggle -->
        <button id="themeToggleBtnAdmin"
                class="w-9 h-9 flex items-center justify-center rounded-full border-0 cursor-pointer transition-all"
                style="background:rgba(99,102,241,.1)" title="Chuyển chế độ Sáng/Tối">
          <i class="bi bi-moon-stars text-primary"></i>
        </button>

        <!-- Avatar + Name -->
        <div class="flex items-center gap-2 pl-2 border-l border-gray-100 dark:border-dark-border">
          <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0"
               style="background:linear-gradient(135deg,#6366f1,#ec4899);box-shadow:0 2px 8px rgba(99,102,241,.35)">
            <?= mb_strtoupper(mb_substr($user['name'] ?? 'A', 0, 1)) ?>
          </div>
          <div class="hidden sm:block">
            <div class="text-sm font-bold text-gray-800 dark:text-dark-text leading-tight">
              <?= htmlspecialchars($user['name'] ?? 'Admin', ENT_QUOTES) ?>
            </div>
            <div class="text-xs text-gray-400">Administrator</div>
          </div>
        </div>

        <!-- Logout -->
        <a href="<?= $appUrl ?>/logout"
           class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm font-bold no-underline transition-all"
           style="background:rgba(239,68,68,.1);color:#ef4444;border:1px solid rgba(239,68,68,.2)"
           onmouseover="this.style.background='rgba(239,68,68,.18)'"
           onmouseout="this.style.background='rgba(239,68,68,.1)'">
          <i class="bi bi-box-arrow-right"></i>
          <span class="hidden sm:inline">Thoát</span>
        </a>
      </div>
    </header>

    <!-- Flash Messages -->
    <div class="flash-banner px-5 pt-4">
      <?= Flash::render() ?>
    </div>

    <!-- Content -->
    <main class="flex-1 p-5 md:p-7 animate-[fadeInUp_0.4s_ease-out_both]">
      <?= $content ?>
    </main>
  </div>

</div>

<!-- Alpine.js (thay thế Bootstrap JS) -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

<script>
  // Dark Mode Toggle — Admin
  document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('themeToggleBtnAdmin');
    if (!btn) return;

    function updateBtn() {
      const isDark = document.documentElement.classList.contains('dark');
      if (isDark) {
        btn.innerHTML = '<i class="bi bi-sun-fill text-yellow-400"></i>';
        btn.style.background = 'rgba(245,158,11,.1)';
      } else {
        btn.innerHTML = '<i class="bi bi-moon-stars text-indigo-500"></i>';
        btn.style.background = 'rgba(99,102,241,.1)';
      }
    }
    updateBtn();

    btn.addEventListener('click', function() {
      const isDark = document.documentElement.classList.toggle('dark');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
      updateBtn();
    });
  });
</script>

<?php if (isset($extraJs)): ?>
  <?= $extraJs ?>
<?php endif; ?>

<style>
  /* Sidebar link component — dùng trong admin layout */
  .sidebar-link {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .6rem 1rem;
    border-radius: 10px;
    color: rgba(255,255,255,.55);
    font-size: .87rem;
    font-weight: 600;
    text-decoration: none;
    transition: all .2s cubic-bezier(.4,0,.2,1);
    margin-bottom: 2px;
  }
  .sidebar-link i {
    font-size: 1rem;
    width: 20px;
    flex-shrink: 0;
  }
  .sidebar-link:hover {
    background: rgba(255,255,255,.08);
    color: #fff;
    transform: translateX(3px);
  }
  .sidebar-link.active {
    background: linear-gradient(135deg, rgba(99,102,241,.35), rgba(139,92,246,.25));
    color: #a5b4fc;
    border: 1px solid rgba(99,102,241,.3);
    box-shadow: 0 2px 12px rgba(99,102,241,.2), inset 0 0 0 1px rgba(99,102,241,.15);
  }
  .sidebar-link.active i { color: #818cf8; }
</style>

<!-- ─── Toast Notification System ─────────────────────────── -->
<div id="toastContainer" class="fixed bottom-4 right-4 z-[99999] flex flex-col gap-2.5 pointer-events-none max-w-[340px] w-full"></div>

<!-- ─── Scroll-to-top Button ──────────────────────────────── -->
<button id="scrollToTopBtn"
  onclick="window.scrollTo({top:0,behavior:'smooth'})"
  class="fixed bottom-4 right-[360px] z-[9998] w-11 h-11 rounded-full bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border shadow-lg text-gray-500 dark:text-gray-300 flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-all opacity-0 translate-y-4 pointer-events-none cursor-pointer">
  <i class="bi bi-chevron-up font-bold"></i>
</button>

<script>
// Global Toast System
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

// Scroll-to-top visibility
(function() {
  const btn = document.getElementById('scrollToTopBtn');
  const mainContent = document.getElementById('adminMainContent') || document.querySelector('main') || window;
  const target = mainContent === window ? window : mainContent;
  
  target.addEventListener('scroll', () => {
    const scrolled = mainContent === window ? window.scrollY : mainContent.scrollTop;
    const show = scrolled > 300;
    if (btn) {
      btn.classList.toggle('opacity-100', show);
      btn.classList.toggle('translate-y-0', show);
      btn.classList.toggle('pointer-events-auto', show);
      btn.classList.toggle('opacity-0', !show);
      btn.classList.toggle('translate-y-4', !show);
      btn.classList.toggle('pointer-events-none', !show);
    }
  }, { passive: true });
  
  if (btn) btn.onclick = () => {
      if(mainContent === window) {
          window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
          mainContent.scrollTo({ top: 0, behavior: 'smooth' });
      }
  };
})();
</script>
</body>
</html>
