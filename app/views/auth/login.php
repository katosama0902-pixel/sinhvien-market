<?php
/**
 * Login View - Trang đăng nhập Sinh Viên
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
$errors = $errors ?? [];
$old    = $old ?? [];
use Core\Flash;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng nhập — SinhVienMarket</title>
  <meta name="description" content="Đăng nhập vào SinhVienMarket để mua bán, trao đổi sản phẩm sinh viên.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/app.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-page">
  <div class="auth-card">

    <!-- Logo -->
    <div class="text-center mb-6">
      <div class="text-2xl font-black text-gradient mb-1">
        <i class="bi bi-shop-window mr-1"></i>SinhVien<span style="color:#ec4899">Market</span>
      </div>
      <p class="text-sm text-gray-500">Chào mừng trở lại! Đăng nhập để tiếp tục.</p>
    </div>

    <!-- Flash message -->
    <?= Flash::render() ?>

    <!-- Rate limit warning -->
    <?php if (!empty($errors['rate_limit'])): ?>
      <div class="alert alert-danger flex items-center gap-2 mb-3">
        <i class="bi bi-shield-exclamation text-lg flex-shrink-0"></i>
        <div class="text-sm"><?= htmlspecialchars($errors['rate_limit'], ENT_QUOTES) ?></div>
      </div>
    <?php endif; ?>

    <!-- General error -->
    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-danger flex items-center gap-2 mb-3">
        <i class="bi bi-exclamation-circle text-lg flex-shrink-0"></i>
        <div class="text-sm"><?= htmlspecialchars($errors['general'], ENT_QUOTES) ?></div>
      </div>
    <?php endif; ?>

    <!-- Recent accounts (rendered by JS) -->
    <div id="recentAccounts"></div>

    <!-- Login Form -->
    <form action="<?= $appUrl ?>/login" method="POST" novalidate id="loginForm">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">

      <!-- Email -->
      <div class="mb-4">
        <label for="email" class="form-label font-semibold text-sm">Email</label>
        <div class="relative">
          <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <i class="bi bi-envelope"></i>
          </span>
          <input type="email" id="email" name="email"
                 class="form-control pl-10 <?= isset($errors['email']) ? 'border-danger ring-4 ring-danger/10' : '' ?>"
                 style="padding-left:2.5rem"
                 placeholder="you@student.edu.vn hoặc Số điện thoại"
                 value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>"
                 autocomplete="email" required>
        </div>
        <?php if (isset($errors['email'])): ?>
          <p class="text-xs text-danger mt-1.5">
            <i class="bi bi-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['email'], ENT_QUOTES) ?>
          </p>
        <?php endif; ?>
      </div>

      <!-- Password -->
      <div class="mb-5">
        <div class="flex justify-between items-center mb-1.5">
          <label for="password" class="form-label mb-0 font-semibold text-sm">Mật khẩu</label>
          <a href="<?= $appUrl ?>/forgot-password" class="text-xs font-semibold text-primary hover:text-primary-dark no-underline">Quên mật khẩu?</a>
        </div>
        <div class="flex rounded-sm border-2 <?= isset($errors['password']) ? 'border-danger ring-4 ring-danger/10' : 'border-light-border focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10' ?> transition-all overflow-hidden">
          <span class="flex items-center pl-3.5 pr-2 text-gray-400">
            <i class="bi bi-lock"></i>
          </span>
          <input type="password" id="password" name="password"
                 class="flex-1 px-2 py-2.5 text-sm border-0 outline-none bg-white"
                 placeholder="••••••••" autocomplete="current-password" required>
          <button type="button" onclick="togglePass('password', this)"
                  class="px-3.5 text-gray-400 hover:text-primary bg-white border-0 cursor-pointer transition-colors"
                  title="Hiện/Ẩn">
            <i class="bi bi-eye"></i>
          </button>
        </div>
        <?php if (isset($errors['password'])): ?>
          <p class="text-xs text-danger mt-1.5">
            <i class="bi bi-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['password'], ENT_QUOTES) ?>
          </p>
        <?php endif; ?>
      </div>

      <!-- Submit -->
      <button type="submit" class="btn btn-primary w-full py-3 text-base" id="btnLogin">
        <i class="bi bi-box-arrow-in-right mr-2"></i>Đăng nhập
      </button>
    </form>

    <!-- Divider -->
    <div class="flex items-center gap-3 text-gray-400 text-sm my-5">
      <div class="flex-1 h-px bg-gray-100"></div>
      hoặc
      <div class="flex-1 h-px bg-gray-100"></div>
    </div>

    <!-- Google Login -->
    <div class="mb-5">
      <a href="<?= $appUrl ?>/auth/google"
         class="flex items-center justify-center gap-3 w-full py-2.5 rounded-sm border-2 border-light-border font-bold text-sm text-gray-700 no-underline hover:bg-gray-50 hover:border-gray-300 transition-all">
        <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        <span>Tiếp tục với Google</span>
      </a>
    </div>

    <!-- Footer links -->
    <div class="text-center">
      <p class="text-sm text-gray-500 mb-1.5">
        Chưa có tài khoản?
        <a href="<?= $appUrl ?>/register" class="font-bold text-primary no-underline hover:text-primary-dark">Đăng ký ngay</a>
      </p>
      <a href="<?= $appUrl ?>/login-role" class="text-xs text-gray-400 hover:text-gray-600 no-underline transition-colors">
        <i class="bi bi-arrow-left mr-1"></i>Quay lại cổng chính
      </a>
    </div>
  </div>
</div>

<script>
// Quản lý Recent Accounts (localStorage)
const RECENT_ACCOUNTS_KEY = 'svmarket_recent_accounts';

function getRecentAccounts() {
    try { return JSON.parse(localStorage.getItem(RECENT_ACCOUNTS_KEY) || '[]'); }
    catch(e) { return []; }
}

function saveRecentAccount(name, email) {
    let accounts = getRecentAccounts().filter(a => a.email !== email);
    accounts.unshift({ name, email });
    localStorage.setItem(RECENT_ACCOUNTS_KEY, JSON.stringify(accounts.slice(0, 3)));
}

function removeRecentAccount(email, event) {
    if (event) event.stopPropagation();
    let accounts = getRecentAccounts().filter(a => a.email !== email);
    localStorage.setItem(RECENT_ACCOUNTS_KEY, JSON.stringify(accounts));
    renderRecentAccounts();
}

function fillAccount(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').focus();
}

function renderRecentAccounts() {
    const list = getRecentAccounts();
    const container = document.getElementById('recentAccounts');
    if (!list.length) { container.innerHTML = ''; return; }

    let html = `<p class="text-center text-sm font-semibold text-gray-400 mb-3">Tiếp tục với tài khoản cũ</p>
                <div class="flex gap-3 justify-center mb-4 flex-wrap">`;
    list.forEach(acc => {
        const initial = acc.name.charAt(0).toUpperCase();
        const hash = acc.email.split('').reduce((a,b) => { a=((a<<5)-a)+b.charCodeAt(0); return a&a; }, 0);
        const hue = Math.abs(hash % 360);
        const bg = `linear-gradient(135deg,hsl(${hue},70%,60%),hsl(${(hue+40)%360},70%,50%))`;
        html += `
          <div class="relative group cursor-pointer text-center" onclick="fillAccount('${acc.email}')">
            <button class="absolute -top-1 -right-1 w-4 h-4 bg-gray-200 rounded-full text-xs text-gray-600 hover:bg-red-400 hover:text-white z-10 flex items-center justify-center border-0 cursor-pointer leading-none"
                    onclick="removeRecentAccount('${acc.email}',event)">×</button>
            <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-extrabold text-base mx-auto shadow-sm" style="background:${bg}">${initial}</div>
            <div class="text-xs text-gray-500 mt-1 max-w-[60px] truncate">${acc.name}</div>
          </div>`;
    });
    html += `</div>
             <div class="flex items-center gap-3 text-gray-400 text-sm mb-4">
               <div class="flex-1 h-px bg-gray-100"></div>hoặc dùng email khác<div class="flex-1 h-px bg-gray-100"></div>
             </div>`;
    container.innerHTML = html;
}

function checkNewLogin() {
    const match = document.cookie.match(new RegExp('(^| )_recent_user=([^;]+)'));
    if (match) {
        try {
            const user = JSON.parse(decodeURIComponent(match[2]));
            saveRecentAccount(user.name, user.email);
            document.cookie = "_recent_user=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        } catch(e) {}
    }
}

function togglePass(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { input.type = 'password'; icon.className = 'bi bi-eye'; }
}

document.addEventListener('DOMContentLoaded', () => { checkNewLogin(); renderRecentAccounts(); });

document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('btnLogin');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Đang đăng nhập...';
});
</script>
</body>
</html>
