<?php
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
$errors = $errors ?? [];
$isStep2 = isset($question) && $question !== '';
use Core\Flash;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quên mật khẩu — SinhVienMarket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-page">
  <div class="auth-card">
    <div class="text-center mb-4">
      <div class="auth-logo"><i class="bi bi-key me-1"></i>Quên<span>Mật Khẩu</span></div>
      <div class="text-muted mt-2">
        <?= $isStep2 ? 'Trả lời câu hỏi bảo mật để tiếp tục' : 'Nhập email của bạn để bắt đầu khôi phục' ?>
      </div>
    </div>

    <?= Flash::render() ?>

    <form action="<?= $appUrl ?>/forgot-password<?= $isStep2 ? '?email='.urlencode($old['email']) : '' ?>" method="<?= $isStep2 ? 'POST' : 'GET' ?>" id="forgotForm">
      <?php if ($isStep2): ?>
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">
          <input type="hidden" name="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>">

          <div class="mb-3">
            <label class="form-label text-primary fw-bold"><i class="bi bi-question-circle me-1"></i> <?= htmlspecialchars($question) ?></label>
            <input type="text" name="security_answer" class="form-control" placeholder="Nhập câu trả lời của bạn" required autofocus>
          </div>
          <button type="submit" class="btn btn-primary w-100" id="btnSubmit">Khôi Phục Mật Khẩu</button>
      <?php else: ?>
          <div class="mb-3">
            <label for="email" class="form-label">Email tài khoản</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                <input type="email" id="email" name="email" class="form-control border-start-0 ps-0" placeholder="you@student.edu.vn" required autofocus>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100" id="btnSubmit">Xác Nhận Email</button>
      <?php endif; ?>
    </form>

    <div class="text-center mt-4">
      <a href="<?= $appUrl ?>/login" class="text-muted text-decoration-none" style="font-size: 0.85rem;">
        <i class="bi bi-arrow-left me-1"></i> Quay lại Đăng nhập
      </a>
    </div>
  </div>
</div>

<script>
document.getElementById('forgotForm').addEventListener('submit', function() {
  document.getElementById('btnSubmit').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
  document.getElementById('btnSubmit').disabled = true;
});
</script>
</body>
</html>
