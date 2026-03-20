<?php
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
$errors = $errors ?? [];
use Core\Flash;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đặt lại mật khẩu — SinhVienMarket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-page">
  <div class="auth-card">
    <div class="text-center mb-4">
      <div class="auth-logo"><i class="bi bi-shield-lock me-1"></i>Đặt Lại<span>Mật Khẩu</span></div>
      <div class="text-muted mt-2">Vui lòng nhập mật khẩu mới cho tài khoản của bạn. Đảm bảo mật khẩu này mạnh và khác biệt.</div>
    </div>

    <?= Flash::render() ?>

    <form action="<?= $appUrl ?>/reset-password" method="POST" id="resetForm">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">
      <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '', ENT_QUOTES) ?>">
      <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '', ENT_QUOTES) ?>">

      <div class="mb-3">
        <label for="password" class="form-label fw-bold">Mật khẩu mới</label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
          <input type="password" id="password" name="password" class="form-control border-start-0 border-end-0 ps-0" placeholder="••••••••" required minlength="8">
          <button class="btn border border-start-0 bg-white" type="button" onclick="togglePass('password', this)"><i class="bi bi-eye"></i></button>
        </div>
      </div>

      <div class="mb-4">
        <label for="password_confirm" class="form-label fw-bold">Xác nhận mật khẩu</label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock-fill text-muted"></i></span>
          <input type="password" id="password_confirm" name="password_confirm" class="form-control border-start-0 border-end-0 ps-0" placeholder="••••••••" required>
          <button class="btn border border-start-0 bg-white" type="button" onclick="togglePass('password_confirm', this)"><i class="bi bi-eye"></i></button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 btn-lg" id="btnReset">
        Lưu Mật Khẩu
      </button>
    </form>
  </div>
</div>

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

document.getElementById('resetForm').addEventListener('submit', function() {
  document.getElementById('btnReset').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
  document.getElementById('btnReset').disabled = true;
});
</script>
</body>
</html>
