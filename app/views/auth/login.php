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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .auth-page-bg {
      min-height: 100vh;
      background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4f46e5 80%, #6366f1 100%);
      display: flex; align-items: center; justify-content: center;
      padding: 2rem 1rem; position: relative; overflow: hidden;
    }
    .auth-page-bg::before {
      content: '';
      position: absolute;
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(139,92,246,.35) 0%, transparent 65%);
      border-radius: 50%;
      top: -180px; right: -150px;
      animation: floatOrb 9s ease-in-out infinite alternate;
    }
    .auth-page-bg::after {
      content: '';
      position: absolute;
      width: 450px; height: 450px;
      background: radial-gradient(circle, rgba(236,72,153,.28) 0%, transparent 65%);
      border-radius: 50%;
      bottom: -120px; left: -100px;
      animation: floatOrb 12s ease-in-out infinite alternate-reverse;
    }
    @keyframes floatOrb {
      from { transform: translate(0,0) scale(1); }
      to   { transform: translate(30px,25px) scale(1.1); }
    }
    .auth-wrap { position: relative; z-index: 2; width: 100%; }

    .login-card {
      background: rgba(255,255,255,.96);
      border: 1px solid rgba(255,255,255,.7);
      border-radius: 24px;
      box-shadow: 0 30px 80px rgba(0,0,0,.25), 0 0 0 1px rgba(255,255,255,.3);
      padding: 2.75rem;
      width: 100%; max-width: 460px;
      margin: 0 auto;
      animation: slideUp .5s cubic-bezier(.16,1,.3,1);
    }
    @keyframes slideUp {
      from { opacity:0; transform: translateY(40px) scale(.97); }
      to   { opacity:1; transform: translateY(0) scale(1); }
    }

    .login-logo {
      font-size: 1.75rem; font-weight: 900;
      background: linear-gradient(135deg, #6366f1, #ec4899);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .form-floating-custom {
      position: relative;
      margin-bottom: 1.25rem;
    }
    .form-floating-custom .field-icon {
      position: absolute;
      left: 14px; top: 50%; transform: translateY(-50%);
      color: #94a3b8; font-size: 1.1rem; z-index: 5;
      pointer-events: none;
    }
    .form-floating-custom .form-control {
      padding-left: 2.8rem;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      height: 52px;
      font-size: .95rem;
      transition: border-color .2s, box-shadow .2s;
    }
    .form-floating-custom .form-control:focus {
      border-color: #6366f1;
      box-shadow: 0 0 0 4px rgba(99,102,241,.12);
    }
    .form-floating-custom .form-control.is-invalid { border-color: #ef4444; }
    .form-floating-custom .input-group { position: relative; }
    .form-floating-custom .input-group .form-control {
      border-right: none;
      border-radius: 12px 0 0 12px !important;
    }
    .form-floating-custom .password-toggle {
      border: 2px solid #e2e8f0; border-left: none;
      border-radius: 0 12px 12px 0 !important;
      background: #fff; color: #94a3b8;
      padding: 0 14px;
      transition: color .2s;
    }
    .form-floating-custom .password-toggle:hover { color: #6366f1; }

    .btn-login {
      width: 100%; padding: .85rem;
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      border: none; border-radius: 12px;
      color: #fff; font-weight: 800; font-size: 1rem;
      font-family: inherit;
      box-shadow: 0 6px 20px rgba(99,102,241,.4);
      transition: all .25s;
      position: relative; overflow: hidden;
    }
    .btn-login::after {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.25) 50%, transparent 60%);
      transform: translateX(-100%);
      transition: transform .5s;
    }
    .btn-login:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(99,102,241,.5); color: #fff; filter: brightness(1.06); }
    .btn-login:hover::after { transform: translateX(100%); }
    .btn-login:disabled { opacity: .65; transform: none; }

    .divider {
      display: flex; align-items: center; gap: .75rem;
      color: #94a3b8; font-size: .85rem; margin: 1.25rem 0;
    }
    .divider::before, .divider::after {
      content: ''; flex: 1; height: 1px; background: #e2e8f0;
    }

    .forgot-link {
      float: right; font-size: .82rem; font-weight: 600; color: #6366f1;
      text-decoration: none;
    }
    .forgot-link:hover { color: #4f46e5; text-decoration: underline; }
  </style>
</head>
<body>

<div class="auth-page-bg">
  <div class="auth-wrap">
    <div class="login-card">
      <!-- Logo -->
      <div class="text-center mb-4">
        <div class="login-logo mb-1">
          <i class="bi bi-shop-window me-1"></i>SinhVien<span style="color:#ec4899">Market</span>
        </div>
        <p class="text-muted mb-0" style="font-size:.9rem">Chào mừng trở lại! Đăng nhập để tiếp tục.</p>
      </div>

      <!-- Flash message -->
      <?= Flash::render() ?>

      <!-- Rate limit warning -->
      <?php if (!empty($errors['rate_limit'])): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mb-3">
          <i class="bi bi-shield-exclamation fs-5 flex-shrink-0"></i>
          <div class="small"><?= htmlspecialchars($errors['rate_limit'], ENT_QUOTES) ?></div>
        </div>
      <?php endif; ?>

      <!-- General error -->
      <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mb-3">
          <i class="bi bi-exclamation-circle fs-5 flex-shrink-0"></i>
          <div class="small"><?= htmlspecialchars($errors['general'], ENT_QUOTES) ?></div>
        </div>
      <?php endif; ?>

      <!-- Form -->
      <div id="recentAccounts"></div>

      <form action="<?= $appUrl ?>/login" method="POST" novalidate id="loginForm">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">

        <!-- Email -->
        <div class="mb-1 d-flex justify-content-between align-items-center">
          <label for="email" class="form-label mb-0 fw-600" style="font-size:.88rem;color:#334155">Email</label>
        </div>
        <div class="form-floating-custom">
          <span class="field-icon"><i class="bi bi-envelope"></i></span>
          <input type="email" id="email" name="email"
                 class="form-control form-control-pill <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                 placeholder="you@student.edu.vn hoặc Số điện thoại"
                 value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>"
                 autocomplete="email" required>
          <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback d-block" style="font-size:.82rem;color:#ef4444;margin-top:.3rem">
              <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errors['email'], ENT_QUOTES) ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Password -->
        <div class="mb-1 d-flex justify-content-between align-items-center">
          <label for="password" class="form-label mb-0 fw-600" style="font-size:.88rem;color:#334155">Mật khẩu</label>
          <a href="<?= $appUrl ?>/forgot-password" class="forgot-link">Quên mật khẩu?</a>
        </div>
        <div class="form-floating-custom">
          <span class="field-icon"><i class="bi bi-lock"></i></span>
          <div class="input-group input-group-pill">
            <input type="password" id="password" name="password"
                   class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                   placeholder="••••••••"
                   autocomplete="current-password" required>
            <button class="password-toggle btn" type="button" onclick="togglePass('password', this)" title="Hiện/Ẩn">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <?php if (isset($errors['password'])): ?>
            <div class="invalid-feedback d-block" style="font-size:.82rem;color:#ef4444;margin-top:.3rem">
              <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errors['password'], ENT_QUOTES) ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Submit -->
        <div class="mt-4">
          <button type="submit" class="btn-login" id="btnLogin">
            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
          </button>
        </div>
      </form>

      <div class="divider">hoặc</div>

      <div class="mb-4">
        <a href="<?= $appUrl ?>/auth/google" class="btn-social-google">
          <img src="https://www.svgrepo.com/show/475656/google-color.svg" width="18" alt="Google">
          <span>Tiếp tục với Google</span>
        </a>
      </div>

      <div class="text-center">
        <p class="mb-2" style="font-size:.9rem;color:#64748b">
          Chưa có tài khoản?
          <a href="<?= $appUrl ?>/register" class="fw-700" style="color:#6366f1">Đăng ký ngay</a>
        </p>
        <a href="<?= $appUrl ?>/login-role" class="text-muted" style="font-size:.82rem">
          <i class="bi bi-arrow-left me-1"></i>Quay lại cổng chính
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Quản lý Recent Accounts (localStorage)
const RECENT_ACCOUNTS_KEY = 'svmarket_recent_accounts';

function getRecentAccounts() {
    try {
        return JSON.parse(localStorage.getItem(RECENT_ACCOUNTS_KEY) || '[]');
    } catch(e) { return []; }
}

function saveRecentAccount(name, email) {
    let accounts = getRecentAccounts();
    // Xóa email cũ nếu đã có (để đưa lên đầu)
    accounts = accounts.filter(a => a.email !== email);
    accounts.unshift({ name, email });
    // Giữa tối đa 3 tài khoản gần nhất
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
    if (list.length === 0) {
        container.innerHTML = '';
        return;
    }

    let html = `
        <p class="text-center mb-3 small fw-600 text-muted">Tiếp tục với tài khoản cũ</p>
        <div class="recent-accounts-list">
    `;

    list.forEach(acc => {
        const initial = acc.name.charAt(0).toUpperCase();
        // Tạo gradient ngẫu nhiên dựa trên email
        const hash = acc.email.split('').reduce((a, b) => { a = ((a << 5) - a) + b.charCodeAt(0); return a & a; }, 0);
        const hue = Math.abs(hash % 360);
        const bg = `linear-gradient(135deg, hsl(${hue}, 70%, 60%), hsl(${(hue + 40) % 360}, 70%, 50%))`;

        html += `
            <div class="recent-account-item" onclick="fillAccount('${acc.email}')">
                <button class="btn-remove-account" onclick="removeRecentAccount('${acc.email}', event)" title="Xóa">×</button>
                <div class="recent-account-avatar" style="background: ${bg}">${initial}</div>
                <span class="recent-account-name">${acc.name}</span>
            </div>
        `;
    });

    html += `</div><div class="divider">hoặc dùng email khác</div>`;
    container.innerHTML = html;
}

// Kiểm tra có cookie user mới từ server không
function checkNewLogin() {
    const match = document.cookie.match(new RegExp('(^| )_recent_user=([^;]+)'));
    if (match) {
        try {
            const user = JSON.parse(decodeURIComponent(match[2]));
            saveRecentAccount(user.name, user.email);
            // Xóa cookie sau khi đã lưu
            document.cookie = "_recent_user=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        } catch(e) {}
    }
}

function togglePass(id, btn) {
  var input = document.getElementById(id);
  var icon  = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text'; icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password'; icon.className = 'bi bi-eye';
  }
}

document.addEventListener('DOMContentLoaded', () => {
    checkNewLogin();
    renderRecentAccounts();
});

document.getElementById('loginForm').addEventListener('submit', function() {
  var btn = document.getElementById('btnLogin');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang đăng nhập...';
});
</script>
</body>
</html>
