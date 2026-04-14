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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-page">
  <div class="auth-card">
    <div class="text-center mb-4">
      <div class="auth-logo"><i class="bi bi-envelope-check me-1"></i>Xác minh<span>Tài khoản</span></div>
      <div class="text-muted mt-2">Mã OTP gồm 6 chữ số đã được gửi tới email của bạn. Mở hộp thư để lấy mã.</div>
    </div>

    <?= Flash::render() ?>

    <form action="<?= $appUrl ?>/verify-otp" method="POST" id="otpForm">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">

      <div class="mb-4 text-center">
        <label for="otp_code" class="form-label fw-bold">Nhập mã OTP</label>
        <input type="text" id="otp_code" name="otp_code"
               class="form-control text-center text-primary fs-3 fw-bold mx-auto"
               style="letter-spacing: 0.5rem; width: 200px;" 
               maxlength="6" placeholder="------" autocomplete="off" required>
      </div>

      <button type="submit" class="btn btn-primary w-100 btn-lg" id="btnVerify">
        Xác Minh Ngay
      </button>
    </form>

    <div class="text-center mt-4 pt-3 border-top">
      <p class="text-muted mb-2">Chưa nhận được mã?</p>
      <?php
        $resendCount = (int)($_SESSION['otp_resend_count'] ?? 0);
        $remaining   = max(0, 3 - $resendCount);
      ?>
      <?php if ($remaining > 0): ?>
        <a href="<?= $appUrl ?>/resend-otp" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Gửi lại mã OTP</a>
        <div class="text-muted mt-1" style="font-size:.76rem">Còn <strong><?= $remaining ?></strong>/3 lượt gửi lại</div>
      <?php else: ?>
        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled>Gửi lại mã OTP</button>
        <div class="text-danger mt-1" style="font-size:.76rem"><i class="bi bi-lock-fill me-1"></i>Đã dùng hết lượt. Đợi 10 phút để gửi lại.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.getElementById('otpForm').addEventListener('submit', function() {
  document.getElementById('btnVerify').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang kiểm tra...';
  document.getElementById('btnVerify').disabled = true;
});
</script>
</body>
</html>
