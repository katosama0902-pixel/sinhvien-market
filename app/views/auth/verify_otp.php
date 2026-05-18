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
  <title>Xác minh OTP — SinhVienMarket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/app.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-page">
  <div class="auth-card">
    <div class="text-center mb-6">
      <div class="flex items-center justify-center gap-2 mb-3">
        <div class="w-12 h-12 flex items-center justify-center rounded-2xl text-white text-xl"
             style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
          <i class="bi bi-envelope-check"></i>
        </div>
      </div>
      <h2 class="text-2xl font-black text-gradient mb-1">Xác minh<span class="text-primary"> Tài khoản</span></h2>
      <p class="text-sm text-gray-500 mt-1">Mã OTP gồm 6 chữ số đã được gửi tới email của bạn.</p>
    </div>

    <?= Flash::render() ?>

    <form action="<?= $appUrl ?>/verify-otp" method="POST" id="otpForm">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">

      <div class="mb-5 text-center">
        <label for="otp_code" class="form-label">Nhập mã OTP</label>
        <input type="text" id="otp_code" name="otp_code"
               class="form-control text-center text-3xl font-black mx-auto block"
               style="letter-spacing:.5rem;width:220px;color:#6366f1"
               maxlength="6" placeholder="------" autocomplete="off" required>
      </div>

      <button type="submit" class="btn btn-primary w-full py-3 text-base" id="btnVerify">
        <i class="bi bi-check2-circle mr-2"></i>Xác Minh Ngay
      </button>
    </form>

    <div class="text-center mt-5 pt-4 border-t border-gray-100">
      <p class="text-sm text-gray-400 mb-2">Chưa nhận được mã?</p>
      <?php
        $resendCount = (int)($_SESSION['otp_resend_count'] ?? 0);
        $remaining   = max(0, 3 - $resendCount);
      ?>
      <?php if ($remaining > 0): ?>
        <a href="<?= $appUrl ?>/resend-otp"
           class="inline-flex items-center gap-1 px-4 py-1.5 rounded-full text-sm font-semibold no-underline transition-all"
           style="border:1.5px solid #e2e8f0;color:#64748b" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
          Gửi lại mã OTP
        </a>
        <p class="text-xs text-gray-400 mt-1">Còn <strong><?= $remaining ?></strong>/3 lượt gửi lại</p>
      <?php else: ?>
        <button class="inline-flex items-center gap-1 px-4 py-1.5 rounded-full text-sm font-semibold opacity-40 cursor-not-allowed border border-gray-200" disabled>
          Gửi lại mã OTP
        </button>
        <p class="text-xs text-red-400 mt-1"><i class="bi bi-lock-fill mr-1"></i>Đã dùng hết lượt. Đợi 10 phút để gửi lại.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.getElementById('otpForm').addEventListener('submit', function() {
  const btn = document.getElementById('btnVerify');
  btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Đang kiểm tra...';
  btn.disabled = true;
});
</script>
</body>
</html>
