<?php
/**
 * Admin Login View - Trang đăng nhập dành riêng cho Admin
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
  <title>Admin Panel — SinhVienMarket</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: #020617;
      min-height: 100vh;
      margin: 0;
      display: flex; align-items: center; justify-content: center;
    }

    .admin-login-bg {
      min-height: 100vh;
      width: 100%;
      display: flex; align-items: center; justify-content: center;
      padding: 2rem 1rem;
      position: relative; overflow: hidden;
      background: radial-gradient(ellipse at 60% 0%, rgba(236,72,153,.18) 0%, transparent 50%),
                  radial-gradient(ellipse at 10% 100%, rgba(99,102,241,.2) 0%, transparent 50%),
                  linear-gradient(180deg, #0f172a 0%, #020617 100%);
    }

    /* Hex grid decorative BG */
    .admin-login-bg::before {
      content: '';
      position: absolute; inset: 0;
      background-image: repeating-linear-gradient(0deg, transparent, transparent 40px, rgba(255,255,255,.02) 40px, rgba(255,255,255,.02) 41px),
                        repeating-linear-gradient(90deg, transparent, transparent 40px, rgba(255,255,255,.02) 40px, rgba(255,255,255,.02) 41px);
      pointer-events: none;
    }

    .admin-card {
      background: rgba(30, 41, 59, 0.85);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(255,255,255,.08);
      border-radius: 24px;
      box-shadow: 0 40px 100px rgba(0,0,0,.6), 0 0 0 1px rgba(255,255,255,.05);
      padding: 2.75rem;
      width: 100%; max-width: 440px;
      position: relative; z-index: 2;
      animation: slideUp .5s cubic-bezier(.16,1,.3,1);
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(36px) scale(.97); }
      to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .admin-logo {
      font-size: 1.5rem; font-weight: 900; color: #fff;
      display: flex; align-items: center; gap: .6rem;
      margin-bottom: .4rem;
    }
    .admin-logo-icon {
      width: 44px; height: 44px;
      background: linear-gradient(135deg, #ec4899, #f97316);
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.3rem; color: #fff;
      box-shadow: 0 8px 24px rgba(236,72,153,.5);
    }
    .admin-logo-text { color: #fff; }
    .admin-logo-text span { color: #f472b6; }

    .admin-subtitle { color: rgba(255,255,255,.45); font-size: .88rem; margin-bottom: 1.75rem; }

    .badge-admin-access {
      display: inline-flex; align-items: center; gap: .4rem;
      background: rgba(236,72,153,.12);
      border: 1px solid rgba(236,72,153,.25);
      color: #f9a8d4;
      padding: .35rem .9rem;
      border-radius: 50px; font-size: .78rem; font-weight: 700;
      margin-bottom: 1.5rem;
      letter-spacing: .3px;
    }

    .form-label-admin { color: rgba(255,255,255,.65); font-size: .85rem; font-weight: 600; margin-bottom: .4rem; }

    .form-control-admin {
      background: rgba(255,255,255,.06);
      border: 1.5px solid rgba(255,255,255,.1);
      border-radius: 12px;
      color: #fff;
      font-family: inherit;
      font-size: .95rem;
      padding: .7rem 1rem .7rem 2.8rem;
      height: 52px;
      width: 100%;
      transition: border-color .2s, box-shadow .2s;
    }
    .form-control-admin::placeholder { color: rgba(255,255,255,.3); }
    .form-control-admin:focus {
      outline: none;
      background: rgba(255,255,255,.09);
      border-color: rgba(236,72,153,.6);
      box-shadow: 0 0 0 4px rgba(236,72,153,.12);
    }
    .form-control-admin.is-invalid { border-color: #ef4444; }

    .field-wrap { position: relative; margin-bottom: 1.25rem; }
    .field-icon-admin {
      position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
      color: rgba(255,255,255,.35); font-size: 1.05rem; z-index: 2; pointer-events: none;
    }
    .input-with-toggle { display: flex; }
    .input-with-toggle .form-control-admin { border-radius: 12px 0 0 12px !important; }
    .btn-toggle-pass {
      background: rgba(255,255,255,.06);
      border: 1.5px solid rgba(255,255,255,.1);
      border-left: none;
      border-radius: 0 12px 12px 0;
      color: rgba(255,255,255,.4);
      padding: 0 14px;
      cursor: pointer;
      transition: color .2s;
    }
    .btn-toggle-pass:hover { color: #f472b6; }

    .btn-admin-submit {
      width: 100%;
      background: linear-gradient(135deg, #ec4899, #f97316);
      border: none; border-radius: 12px;
      color: #fff; font-family: inherit;
      font-weight: 800; font-size: 1rem;
      padding: .85rem;
      box-shadow: 0 6px 24px rgba(236,72,153,.4);
      transition: all .25s;
      position: relative; overflow: hidden; cursor: pointer;
    }
    .btn-admin-submit::after {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,.2) 50%, transparent 60%);
      transform: translateX(-100%); transition: transform .5s;
    }
    .btn-admin-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(236,72,153,.55); filter: brightness(1.06); }
    .btn-admin-submit:hover::after { transform: translateX(100%); }
    .btn-admin-submit:disabled { opacity: .6; transform: none; }

    .invalid-hint { color: #f87171; font-size: .8rem; margin-top: .35rem; }
    .back-link { color: rgba(255,255,255,.3); font-size: .82rem; text-decoration: none; transition: color .2s; }
    .back-link:hover { color: rgba(255,255,255,.65); }

    .security-note {
      background: rgba(251,191,36,.07);
      border: 1px solid rgba(251,191,36,.18);
      border-radius: 10px; padding: .65rem .9rem;
      color: rgba(251,191,36,.75); font-size: .8rem;
      display: flex; align-items: center; gap: .5rem;
      margin-top: 1rem;
    }
  </style>
</head>
<body>

<div class="admin-login-bg">
  <div style="position:relative;z-index:2;width:100%;max-width:440px">

    <div class="admin-card">
      <!-- Logo -->
      <div class="text-center mb-1">
        <div class="admin-logo justify-content-center">
          <div class="admin-logo-icon"><i class="bi bi-shield-lock"></i></div>
          <span class="admin-logo-text">Admin<span>Panel</span></span>
        </div>
        <p class="admin-subtitle">Hệ thống quản trị SinhVienMarket</p>
        <span class="badge-admin-access">
          <i class="bi bi-lock-fill"></i>Yêu cầu xác thực
        </span>
      </div>

      <!-- Flash -->
      <?= Flash::render() ?>

      <!-- Rate limit -->
      <?php if (!empty($errors['rate_limit'])): ?>
        <div class="d-flex align-items-center gap-2 mb-3 p-3 rounded-3" style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25)">
          <i class="bi bi-shield-exclamation text-danger flex-shrink-0"></i>
          <div style="color:#fca5a5;font-size:.85rem"><?= htmlspecialchars($errors['rate_limit'], ENT_QUOTES) ?></div>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors['general'])): ?>
        <div class="d-flex align-items-center gap-2 mb-3 p-3 rounded-3" style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25)">
          <i class="bi bi-exclamation-circle text-danger flex-shrink-0"></i>
          <div style="color:#fca5a5;font-size:.85rem"><?= htmlspecialchars($errors['general'], ENT_QUOTES) ?></div>
        </div>
      <?php endif; ?>

      <!-- Form -->
      <form action="<?= $appUrl ?>/admin-login" method="POST" novalidate id="adminLoginForm">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">

        <!-- Email -->
        <div class="mb-1">
          <label class="form-label-admin">Email quản trị</label>
        </div>
        <div class="field-wrap">
          <span class="field-icon-admin"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" id="email"
                 class="form-control-admin <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                 placeholder="admin@sinhvienmarket.vn"
                 value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>"
                 autocomplete="email" required>
          <?php if (isset($errors['email'])): ?>
            <div class="invalid-hint"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errors['email'], ENT_QUOTES) ?></div>
          <?php endif; ?>
        </div>

        <!-- Password -->
        <div class="mb-1">
          <label class="form-label-admin">Mật khẩu</label>
        </div>
        <div class="field-wrap">
          <span class="field-icon-admin" style="top:26px;transform:none"><i class="bi bi-key"></i></span>
          <div class="input-with-toggle">
            <input type="password" name="password" id="password"
                   class="form-control-admin <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                   placeholder="••••••••" autocomplete="current-password" required
                   style="padding-left:2.8rem">
            <button type="button" class="btn-toggle-pass" onclick="togglePass('password', this)" title="Hiện/Ẩn">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <?php if (isset($errors['password'])): ?>
            <div class="invalid-hint"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($errors['password'], ENT_QUOTES) ?></div>
          <?php endif; ?>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn-admin-submit" id="btnAdminLogin">
            <i class="bi bi-shield-check me-2"></i>Xác thực & Đăng nhập
          </button>
        </div>
      </form>

      <div class="security-note">
        <i class="bi bi-shield-lock-fill flex-shrink-0"></i>
        <span>Hệ thống bảo mật theo dõi mọi lần đăng nhập. Tối đa 5 lần sai / 15 phút.</span>
      </div>

      <div class="text-center mt-3">
        <a href="<?= $appUrl ?>/login-role" class="back-link">
          <i class="bi bi-arrow-left me-1"></i>Quay lại cổng chính
        </a>
      </div>
    </div>

  </div>
</div>

<script>
function togglePass(id, btn) {
  var input = document.getElementById(id);
  var icon  = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text'; icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password'; icon.className = 'bi bi-eye';
  }
}
document.getElementById('adminLoginForm').addEventListener('submit', function() {
  var btn = document.getElementById('btnAdminLogin');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xác thực...';
});
</script>
</body>
</html>
