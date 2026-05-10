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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/app.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-page">
  <div class="auth-card">
    <div class="text-center mb-6">
      <div class="flex items-center justify-center mb-3">
        <div class="w-12 h-12 flex items-center justify-center rounded-2xl text-white text-xl"
             style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
          <i class="bi bi-shield-lock"></i>
        </div>
      </div>
      <h2 class="text-2xl font-black mb-1">Đặt Lại <span class="text-gradient">Mật Khẩu</span></h2>
      <p class="text-sm text-gray-500 mt-1">Đảm bảo mật khẩu mới mạnh và khác biệt.</p>
    </div>

    <?= Flash::render() ?>

    <form action="<?= $appUrl ?>/reset-password" method="POST" id="resetForm">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">
      <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '', ENT_QUOTES) ?>">
      <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '', ENT_QUOTES) ?>">

      <!-- Mật khẩu mới -->
      <div class="mb-4">
        <label for="password" class="form-label font-bold">Mật khẩu mới</label>
        <div class="flex rounded-sm border-2 border-light-border focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10 transition-all overflow-hidden">
          <span class="flex items-center px-3 text-gray-400 bg-gray-50 border-r border-light-border">
            <i class="bi bi-lock"></i>
          </span>
          <input type="password" id="password" name="password"
                 class="flex-1 px-3 py-2.5 text-sm border-0 outline-none bg-white"
                 placeholder="••••••••" required minlength="8">
          <button type="button" onclick="togglePass('password', this)"
                  class="px-3 text-gray-400 hover:text-primary bg-white border-0 cursor-pointer transition-colors">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <!-- Xác nhận mật khẩu -->
      <div class="mb-6">
        <label for="password_confirm" class="form-label font-bold">Xác nhận mật khẩu</label>
        <div class="flex rounded-sm border-2 border-light-border focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10 transition-all overflow-hidden">
          <span class="flex items-center px-3 text-gray-400 bg-gray-50 border-r border-light-border">
            <i class="bi bi-lock-fill"></i>
          </span>
          <input type="password" id="password_confirm" name="password_confirm"
                 class="flex-1 px-3 py-2.5 text-sm border-0 outline-none bg-white"
                 placeholder="••••••••" required>
          <button type="button" onclick="togglePass('password_confirm', this)"
                  class="px-3 text-gray-400 hover:text-primary bg-white border-0 cursor-pointer transition-colors">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-full py-3 text-base" id="btnReset">
        <i class="bi bi-check-circle mr-2"></i>Lưu Mật Khẩu
      </button>
    </form>
  </div>
</div>

<script>
function togglePass(id, btn) {
  const input = document.getElementById(id);
  const icon  = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text'; icon.className = 'bi bi-eye-slash';
  } else {
    input.type = 'password'; icon.className = 'bi bi-eye';
  }
}
document.getElementById('resetForm').addEventListener('submit', function() {
  const btn = document.getElementById('btnReset');
  btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Đang xử lý...';
  btn.disabled = true;
});
</script>
</body>
</html>
