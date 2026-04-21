<?php
/**
 * Admin Login View — Xác thực bằng mã PIN
 * Giao diện tối, tối giản, chuyên nghiệp
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$appName = $_ENV['APP_NAME'] ?? 'SinhVienMarket';
?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — <?= htmlspecialchars($appName) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: #0d0f1a;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      position: relative;
      overflow: hidden;
    }

    /* Background glow effect */
    body::before {
      content: '';
      position: fixed;
      top: -200px; left: 50%;
      transform: translateX(-50%);
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(99,102,241,.15) 0%, transparent 70%);
      pointer-events: none;
    }
    body::after {
      content: '';
      position: fixed;
      bottom: -200px; left: 50%;
      transform: translateX(-50%);
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(139,92,246,.1) 0%, transparent 70%);
      pointer-events: none;
    }

    .login-card {
      background: #161826;
      border: 1px solid #2a2d45;
      border-radius: 20px;
      padding: 48px 44px;
      width: 100%;
      max-width: 420px;
      position: relative;
      z-index: 1;
      box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 32px;
      justify-content: center;
    }
    .brand-icon {
      width: 46px; height: 46px;
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px;
    }
    .brand-name {
      font-size: 20px;
      font-weight: 800;
      color: #e2e8f0;
      letter-spacing: -0.5px;
    }

    .divider {
      border-top: 1px solid #2a2d45;
      margin: 0 0 32px;
    }

    .admin-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(99,102,241,.15);
      color: #a5b4fc;
      border: 1px solid rgba(99,102,241,.3);
      border-radius: 999px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 4px 12px;
      margin-bottom: 16px;
    }

    h1.title {
      font-size: 24px;
      font-weight: 800;
      color: #f1f5f9;
      margin: 0 0 8px;
      letter-spacing: -0.5px;
    }
    p.subtitle {
      color: #64748b;
      font-size: 14px;
      margin: 0 0 28px;
    }

    label.form-label {
      color: #94a3b8;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 8px;
      letter-spacing: 0.3px;
    }

    .pin-input-wrap {
      position: relative;
    }
    .pin-input-wrap input {
      background: #1e2235;
      border: 1.5px solid #2a2d45;
      border-radius: 12px;
      color: #f1f5f9;
      font-size: 22px;
      font-weight: 700;
      letter-spacing: 6px;
      font-family: 'Courier New', monospace;
      padding: 14px 52px 14px 18px;
      width: 100%;
      transition: border-color .2s, box-shadow .2s;
      text-align: center;
    }
    .pin-input-wrap input:focus {
      outline: none;
      border-color: #6366f1;
      box-shadow: 0 0 0 3px rgba(99,102,241,.2);
    }
    .pin-input-wrap input::placeholder {
      letter-spacing: 3px;
      font-size: 14px;
      color: #475569;
      font-weight: 400;
    }
    .pin-toggle {
      position: absolute;
      right: 16px; top: 50%;
      transform: translateY(-50%);
      background: none; border: none;
      color: #475569; cursor: pointer;
      font-size: 18px; padding: 0;
      transition: color .15s;
    }
    .pin-toggle:hover { color: #94a3b8; }

    .btn-login {
      width: 100%;
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      border: none;
      border-radius: 12px;
      color: #fff;
      font-size: 15px;
      font-weight: 700;
      padding: 14px;
      cursor: pointer;
      margin-top: 24px;
      transition: opacity .2s, transform .1s;
      letter-spacing: 0.2px;
    }
    .btn-login:hover { opacity: 0.92; transform: translateY(-1px); }
    .btn-login:active { transform: translateY(0); opacity: 1; }
    .btn-login:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

    /* Alerts */
    .alert-custom {
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 13px;
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin-bottom: 20px;
    }
    .alert-danger-dark {
      background: rgba(239,68,68,.1);
      border: 1px solid rgba(239,68,68,.3);
      color: #fca5a5;
    }
    .alert-info-dark {
      background: rgba(99,102,241,.1);
      border: 1px solid rgba(99,102,241,.3);
      color: #a5b4fc;
    }

    /* Attempts indicator */
    .attempts-bar {
      display: flex;
      gap: 5px;
      margin-top: 8px;
    }
    .attempts-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
    }
    .attempts-dot.used { background: #ef4444; }
    .attempts-dot.left { background: #2a2d45; }

    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      color: #475569;
      font-size: 13px;
      text-decoration: none;
      transition: color .15s;
    }
    .back-link:hover { color: #94a3b8; }
  </style>
</head>
<body>

<div class="login-card">

  <!-- Brand -->
  <div class="brand">
    <div class="brand-icon">🛒</div>
    <span class="brand-name"><?= htmlspecialchars($appName) ?></span>
  </div>
  <div class="divider"></div>

  <!-- Title -->
  <div class="admin-badge">
    <i class="bi bi-shield-lock-fill"></i> Khu vực quản trị
  </div>
  <h1 class="title">Đăng nhập Admin</h1>
  <p class="subtitle">Nhập mã PIN quản trị để truy cập hệ thống</p>

  <!-- Flash messages -->
  <?php
  $flash = \Core\Flash::get();
  if ($flash):
    $iconMap = ['danger' => 'bi-exclamation-triangle-fill', 'info' => 'bi-info-circle-fill', 'success' => 'bi-check-circle-fill'];
    $icon = $iconMap[$flash['type']] ?? 'bi-info-circle-fill';
    $cls  = $flash['type'] === 'danger' ? 'alert-danger-dark' : 'alert-info-dark';
  ?>
  <div class="alert-custom <?= $cls ?>">
    <i class="bi <?= $icon ?>" style="margin-top:1px;flex-shrink:0;"></i>
    <span><?= htmlspecialchars($flash['message']) ?></span>
  </div>
  <?php endif; ?>

  <!-- Lockout notice -->
  <?php if ($isLocked): ?>
  <div class="alert-custom alert-danger-dark">
    <i class="bi bi-clock-fill" style="margin-top:1px;flex-shrink:0;"></i>
    <span>Tài khoản tạm thời bị khóa do nhập sai quá nhiều lần. Vui lòng thử lại sau.</span>
  </div>
  <?php endif; ?>

  <!-- Form -->
  <form method="POST" action="<?= $appUrl ?>/admin/login" autocomplete="off">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">

    <div class="mb-3">
      <label class="form-label">MÃ PIN QUẢN TRỊ</label>
      <div class="pin-input-wrap">
        <input
          type="password"
          id="pinInput"
          name="pin"
          placeholder="Nhập mã PIN"
          maxlength="20"
          required
          <?= $isLocked ? 'disabled' : '' ?>
          autofocus
        >
        <button type="button" class="pin-toggle" id="pinToggle" tabindex="-1">
          <i class="bi bi-eye" id="eyeIcon"></i>
        </button>
      </div>

      <!-- Attempts remaining dots -->
      <?php if (!$isLocked && isset($_SESSION['admin_pin_attempts']) && $_SESSION['admin_pin_attempts'] > 0): ?>
      <div style="margin-top:10px;">
        <div style="font-size:11px;color:#64748b;margin-bottom:6px;">Số lần thử còn lại:</div>
        <div class="attempts-bar">
          <?php for ($i = 0; $i < 5; $i++): ?>
          <div class="attempts-dot <?= $i < (int)$_SESSION['admin_pin_attempts'] ? 'used' : 'left' ?>"></div>
          <?php endfor; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <button type="submit" class="btn-login" <?= $isLocked ? 'disabled' : '' ?>>
      <i class="bi bi-shield-check me-2"></i>Xác nhận đăng nhập
    </button>
  </form>

  <!-- Back link -->
  <a href="<?= $appUrl ?>/" class="back-link">
    <i class="bi bi-arrow-left me-1"></i>Về trang chủ
  </a>

</div>

<script>
  // Toggle hiện/ẩn PIN
  const pinInput = document.getElementById('pinInput');
  const pinToggle = document.getElementById('pinToggle');
  const eyeIcon = document.getElementById('eyeIcon');

  pinToggle.addEventListener('click', () => {
    const isPassword = pinInput.type === 'password';
    pinInput.type = isPassword ? 'text' : 'password';
    eyeIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
  });

  // Nếu không bị lock thì focus vào input
  pinInput && !pinInput.disabled && pinInput.focus();
</script>

</body>
</html>
