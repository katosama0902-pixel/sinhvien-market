<?php
/**
 * Register View - Trang đăng ký
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
  <title>Đăng ký — SinhVienMarket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-page">
  <div class="auth-card" style="max-width:520px;">

    <!-- Logo -->
    <div class="text-center mb-4">
      <div class="auth-logo"><i class="bi bi-shop-window me-1"></i>SinhVien<span>Market</span></div>
      <div class="auth-subtitle">Tạo tài khoản miễn phí và bắt đầu mua bán ngay!</div>
    </div>

    <!-- Flash message -->
    <?= Flash::render() ?>

    <!-- Form -->
    <form action="<?= $appUrl ?>/register" method="POST" novalidate id="registerForm">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">

      <!-- Họ tên -->
      <div class="mb-3">
        <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-person text-muted"></i>
          </span>
          <input type="text" id="name" name="name"
                 class="form-control border-start-0 ps-0 <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                 placeholder="Nguyễn Văn A"
                 value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES) ?>"
                 autocomplete="name" required>
          <?php if (isset($errors['name'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['name'], ENT_QUOTES) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Email -->
      <div class="mb-3">
        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
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

      <!-- Số điện thoại -->
      <div class="mb-3">
        <label for="phone" class="form-label">Số điện thoại <span class="text-muted">(tuỳ chọn)</span></label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-telephone text-muted"></i>
          </span>
          <input type="tel" id="phone" name="phone"
                 class="form-control border-start-0 ps-0 <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                 placeholder="0901 234 567"
                 value="<?= htmlspecialchars($old['phone'] ?? '', ENT_QUOTES) ?>"
                 autocomplete="tel">
          <?php if (isset($errors['phone'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['phone'], ENT_QUOTES) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Câu hỏi bảo mật -->
      <div class="mb-3">
        <label for="security_question" class="form-label">Câu hỏi bảo mật (dùng để khôi phục mật khẩu) <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-patch-question text-muted"></i>
          </span>
          <select id="security_question" name="security_question" class="form-select border-start-0 ps-0 <?= isset($errors['security_question']) ? 'is-invalid' : '' ?>" required>
            <option value="">Chọn câu hỏi bảo mật</option>
            <option value="q1" <?= ($old['security_question'] ?? '') == 'q1' ? 'selected' : '' ?>>Tên trường cấp 1 của bạn là gì?</option>
            <option value="q2" <?= ($old['security_question'] ?? '') == 'q2' ? 'selected' : '' ?>>Tên thú cưng đầu tiên của bạn?</option>
            <option value="q3" <?= ($old['security_question'] ?? '') == 'q3' ? 'selected' : '' ?>>Bạn thân thời thơ ấu của bạn tên gì?</option>
          </select>
          <?php if (isset($errors['security_question'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['security_question'], ENT_QUOTES) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Câu trả lời bảo mật -->
      <div class="mb-3">
        <label for="security_answer" class="form-label">Câu trả lời <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-pen text-muted"></i>
          </span>
          <input type="text" id="security_answer" name="security_answer"
                 class="form-control border-start-0 ps-0 <?= isset($errors['security_answer']) ? 'is-invalid' : '' ?>"
                 placeholder="Nhập câu trả lời"
                 value="<?= htmlspecialchars($old['security_answer'] ?? '', ENT_QUOTES) ?>" required>
          <?php if (isset($errors['security_answer'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['security_answer'], ENT_QUOTES) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Mật khẩu -->
      <div class="mb-3">
        <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-lock text-muted"></i>
          </span>
          <input type="password" id="password" name="password"
                 class="form-control border-start-0 border-end-0 ps-0 <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                 placeholder="Tối thiểu 8 ký tự"
                 autocomplete="new-password" required minlength="8">
          <button class="password-toggle btn border input-group-text" type="button"
                  onclick="togglePass('password', this)" title="Hiện/Ẩn mật khẩu">
            <i class="bi bi-eye"></i>
          </button>
          <?php if (isset($errors['password'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['password'], ENT_QUOTES) ?></div>
          <?php endif; ?>
        </div>
        <!-- Password strength indicator -->
        <div class="mt-1">
          <div class="progress" style="height:4px;">
            <div class="progress-bar" id="strengthBar" role="progressbar" style="width:0%"></div>
          </div>
          <small class="text-muted" id="strengthText"></small>
        </div>
      </div>

      <!-- Xác nhận mật khẩu -->
      <div class="mb-4">
        <label for="password_confirm" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-lock-fill text-muted"></i>
          </span>
          <input type="password" id="password_confirm" name="password_confirm"
                 class="form-control border-start-0 border-end-0 ps-0 <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                 placeholder="Nhập lại mật khẩu"
                 autocomplete="new-password" required>
          <button class="password-toggle btn border input-group-text" type="button"
                  onclick="togglePass('password_confirm', this)" title="Hiện/Ẩn">
            <i class="bi bi-eye"></i>
          </button>
          <?php if (isset($errors['password_confirm'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['password_confirm'], ENT_QUOTES) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 btn-lg" id="btnRegister">
        <i class="bi bi-person-plus me-2"></i>Tạo tài khoản
      </button>
    </form>

    <div class="auth-divider">hoặc</div>

    <div class="text-center">
      <p class="mb-0 text-muted">Đã có tài khoản?
        <a href="<?= $appUrl ?>/login" class="fw-600">Đăng nhập</a>
      </p>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass(id, btn) {
  var input = document.getElementById(id);
  var icon  = btn.querySelector('i');
  input.type = input.type === 'password' ? 'text' : 'password';
  icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

// Password strength meter
document.getElementById('password').addEventListener('input', function() {
  var val = this.value;
  var score = 0;
  if (val.length >= 8)    score++;
  if (/[A-Z]/.test(val))  score++;
  if (/[0-9]/.test(val))  score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  var bar  = document.getElementById('strengthBar');
  var text = document.getElementById('strengthText');
  var configs = [
    {w:'0%',   cls:'',            label:''},
    {w:'25%',  cls:'bg-danger',   label:'Yếu'},
    {w:'50%',  cls:'bg-warning',  label:'Trung bình'},
    {w:'75%',  cls:'bg-info',     label:'Khá mạnh'},
    {w:'100%', cls:'bg-success',  label:'Mạnh'},
  ];
  var c = configs[score] || configs[0];
  bar.style.width = c.w;
  bar.className   = 'progress-bar ' + c.cls;
  text.textContent = c.label ? 'Độ mạnh: ' + c.label : '';
});

// Prevent double submit
document.getElementById('registerForm').addEventListener('submit', function() {
  var btn = document.getElementById('btnRegister');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang tạo tài khoản...';
});
</script>
</body>
</html>
