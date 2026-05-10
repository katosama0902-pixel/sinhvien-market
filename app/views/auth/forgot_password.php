<?php
$appUrl  = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
$errors  = $errors ?? [];
$isStep2 = isset($question) && $question !== '';
use Core\Flash;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quên mật khẩu — SinhVienMarket</title>
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
          <i class="bi bi-key"></i>
        </div>
      </div>
      <h2 class="text-2xl font-black mb-1">Quên <span class="text-gradient">Mật Khẩu</span></h2>
      <p class="text-sm text-gray-500 mt-1">
        <?= $isStep2 ? 'Trả lời câu hỏi bảo mật để tiếp tục' : 'Nhập email của bạn để bắt đầu khôi phục' ?>
      </p>
    </div>

    <?= Flash::render() ?>

    <form action="<?= $appUrl ?>/forgot-password<?= $isStep2 ? '?email='.urlencode($old['email']) : '' ?>"
          method="<?= $isStep2 ? 'POST' : 'GET' ?>" id="forgotForm">
      <?php if ($isStep2): ?>
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>">

        <div class="mb-4">
          <label class="form-label text-primary font-bold">
            <i class="bi bi-question-circle mr-1"></i><?= htmlspecialchars($question) ?>
          </label>
          <input type="text" name="security_answer" class="form-control"
                 placeholder="Nhập câu trả lời của bạn" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary w-full py-3" id="btnSubmit">
          <i class="bi bi-shield-check mr-2"></i>Khôi Phục Mật Khẩu
        </button>
      <?php else: ?>
        <div class="mb-4">
          <label for="email" class="form-label">Email tài khoản</label>
          <div class="flex rounded-sm border-2 border-light-border focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10 transition-all overflow-hidden">
            <span class="flex items-center px-3 text-gray-400 bg-gray-50">
              <i class="bi bi-envelope"></i>
            </span>
            <input type="email" id="email" name="email"
                   class="flex-1 px-3 py-2.5 text-sm border-0 outline-none bg-white"
                   placeholder="you@student.edu.vn" required autofocus>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-full py-3" id="btnSubmit">
          <i class="bi bi-send mr-2"></i>Xác Nhận Email
        </button>
      <?php endif; ?>
    </form>

    <div class="text-center mt-5">
      <a href="<?= $appUrl ?>/login" class="text-sm text-gray-400 hover:text-primary no-underline transition-colors">
        <i class="bi bi-arrow-left mr-1"></i>Quay lại Đăng nhập
      </a>
    </div>
  </div>
</div>

<script>
document.getElementById('forgotForm').addEventListener('submit', function() {
  const btn = document.getElementById('btnSubmit');
  btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Đang xử lý...';
  btn.disabled = true;
});
</script>
</body>
</html>
