<?php
/**
 * Login View - Trang đăng nhập
 * Layout: KHÔNG dùng main.php (trang này tự render toàn trang)
 * $errors, $old được truyền từ AuthController
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-page">
  <div class="auth-card">

    <!-- Logo -->
    <div class="text-center mb-4">
      <div class="auth-logo"><i class="bi bi-shop-window me-1"></i>SinhVien<span>Market</span></div>
      <div class="auth-subtitle">Chào mừng trở lại! Đăng nhập để tiếp tục.</div>
    </div>

    <!-- Flash message -->
    <?= Flash::render() ?>

    <!-- Rate limit warning -->
    <?php if (!empty($errors['rate_limit'])): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2">
        <i class="bi bi-shield-exclamation fs-5"></i>
        <div><?= htmlspecialchars($errors['rate_limit'], ENT_QUOTES) ?></div>
      </div>
    <?php endif; ?>

    <!-- Form -->
    <form action="<?= $appUrl ?>/login" method="POST" novalidate id="loginForm">
      <!-- CSRF -->
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">

      <!-- Email -->
      <div class="mb-3">
        <label for="email" class="form-label">Email sinh viên</label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-envelope text-muted"></i>
          </span>
          <input type="email" id="email" name="email"
                 class="form-control border-start-0 ps-0 <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                 placeholder="you@student.edu.vn"
                 value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>"
                 autocomplete="email" required>
          <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['email'], ENT_QUOTES) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Password -->
      <div class="mb-4">
        <div class="d-flex justify-content-between">
          <label for="password" class="form-label">Mật khẩu</label>
        </div>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-lock text-muted"></i>
          </span>
          <input type="password" id="password" name="password"
                 class="form-control border-start-0 border-end-0 ps-0 <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                 placeholder="••••••••"
                 autocomplete="current-password" required>
          <button class="password-toggle btn border input-group-text" type="button" onclick="togglePass('password', this)"
                  title="Hiện/Ẩn mật khẩu">
            <i class="bi bi-eye"></i>
          </button>
          <?php if (isset($errors['password'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['password'], ENT_QUOTES) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Submit -->
      <button type="submit" class="btn btn-primary w-100 btn-lg" id="btnLogin">
        <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
      </button>
    </form>

    <div class="auth-divider">hoặc</div>

    <div class="text-center">
      <p class="mb-0 text-muted">Chưa có tài khoản?
        <a href="<?= $appUrl ?>/register" class="fw-600">Đăng ký ngay</a>
      </p>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass(id, btn) {
  var input = document.getElementById(id);
  var icon  = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'bi bi-eye';
  }
}

// Prevent double submit
document.getElementById('loginForm').addEventListener('submit', function() {
  var btn = document.getElementById('btnLogin');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang đăng nhập...';
});
</script>
</body>
</html>
