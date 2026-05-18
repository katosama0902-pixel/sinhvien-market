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
  <meta name="description" content="Tạo tài khoản SinhVienMarket miễn phí và bắt đầu mua bán ngay!">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/app.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
</head>
<body>

<div class="auth-page">
  <div class="auth-card" style="max-width:520px">

    <!-- Logo -->
    <div class="text-center mb-5">
      <div class="text-2xl font-black text-gradient mb-1">
        <i class="bi bi-shop-window mr-1"></i>SinhVienMarket
      </div>
      <p class="text-sm text-gray-500">Tạo tài khoản miễn phí và bắt đầu mua bán ngay!</p>
    </div>

    <?= Flash::render() ?>

    <form action="<?= $appUrl ?>/register" method="POST" novalidate id="registerForm">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">

      <?php
      // Helper macro: input-group row
      $field = function(string $id, string $type, string $name, string $icon, string $label, string $placeholder, array $errors, array $old, bool $required = true, string $extra = '') use (&$field): void {
          $hasError = isset($errors[$name]);
          $borderCls = $hasError ? 'border-danger ring-4 ring-danger/10' : 'border-light-border focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10';
          echo "<div class=\"mb-4\">";
          echo "<label for=\"{$id}\" class=\"form-label text-sm\">{$label}" . ($required ? " <span class=\"text-danger\">*</span>" : " <span class=\"text-gray-400 text-xs\">(tuỳ chọn)</span>") . "</label>";
          echo "<div class=\"flex rounded-sm border-2 {$borderCls} transition-all overflow-hidden\">";
          echo "<span class=\"flex items-center px-3 text-gray-400 bg-gray-50 border-r border-light-border\"><i class=\"bi bi-{$icon}\"></i></span>";
          echo "<input type=\"{$type}\" id=\"{$id}\" name=\"{$name}\" class=\"flex-1 px-3 py-2.5 text-sm border-0 outline-none bg-white\" placeholder=\"{$placeholder}\" value=\"" . htmlspecialchars($old[$name] ?? '', ENT_QUOTES) . "\" " . ($required ? 'required' : '') . " {$extra}>";
          echo "</div>";
          if ($hasError) echo "<p class=\"text-xs text-danger mt-1.5\"><i class=\"bi bi-exclamation-circle mr-1\"></i>" . htmlspecialchars($errors[$name], ENT_QUOTES) . "</p>";
          echo "</div>";
      };
      ?>

      <?php $field('name','text','name','person','Họ và tên','Nguyễn Văn A',$errors,$old) ?>
      <?php $field('email','email','email','envelope','Email','you@student.edu.vn hoặc Số điện thoại',$errors,$old) ?>
      <?php $field('phone','tel','phone','telephone','Số điện thoại','0901 234 567',$errors,$old,false,'autocomplete="tel"') ?>

      <!-- Câu hỏi bảo mật -->
      <div class="mb-4">
        <label for="security_question" class="form-label text-sm">
          Câu hỏi bảo mật <span class="text-danger">*</span>
          <span class="text-xs text-gray-400 ml-1">(dùng để khôi phục mật khẩu)</span>
        </label>
        <div class="flex rounded-sm border-2 <?= isset($errors['security_question']) ? 'border-danger ring-4 ring-danger/10' : 'border-light-border focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10' ?> transition-all overflow-hidden">
          <span class="flex items-center px-3 text-gray-400 bg-gray-50 border-r border-light-border">
            <i class="bi bi-patch-question"></i>
          </span>
          <select id="security_question" name="security_question"
                  class="flex-1 px-3 py-2.5 text-sm border-0 outline-none bg-white cursor-pointer" required>
            <option value="">Chọn câu hỏi bảo mật</option>
            <option value="q1" <?= ($old['security_question'] ?? '') == 'q1' ? 'selected' : '' ?>>Tên trường cấp 1 của bạn là gì?</option>
            <option value="q2" <?= ($old['security_question'] ?? '') == 'q2' ? 'selected' : '' ?>>Tên thú cưng đầu tiên của bạn?</option>
            <option value="q3" <?= ($old['security_question'] ?? '') == 'q3' ? 'selected' : '' ?>>Bạn thân thời thơ ấu của bạn tên gì?</option>
          </select>
        </div>
        <?php if (isset($errors['security_question'])): ?>
          <p class="text-xs text-danger mt-1.5"><i class="bi bi-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['security_question'], ENT_QUOTES) ?></p>
        <?php endif; ?>
      </div>

      <?php $field('security_answer','text','security_answer','pen','Câu trả lời','Nhập câu trả lời',$errors,$old) ?>

      <!-- Mật khẩu -->
      <div class="mb-4">
        <label for="password" class="form-label text-sm">Mật khẩu <span class="text-danger">*</span></label>
        <div class="flex rounded-sm border-2 <?= isset($errors['password']) ? 'border-danger ring-4 ring-danger/10' : 'border-light-border focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10' ?> transition-all overflow-hidden">
          <span class="flex items-center px-3 text-gray-400 bg-gray-50 border-r border-light-border"><i class="bi bi-lock"></i></span>
          <input type="password" id="password" name="password"
                 class="flex-1 px-3 py-2.5 text-sm border-0 outline-none bg-white"
                 placeholder="Tối thiểu 8 ký tự" autocomplete="new-password" required minlength="8">
          <button type="button" onclick="togglePass('password',this)"
                  class="px-3.5 text-gray-400 hover:text-primary bg-white border-0 cursor-pointer transition-colors">
            <i class="bi bi-eye"></i>
          </button>
        </div>
        <!-- Password strength -->
        <div class="mt-2">
          <div class="h-1 rounded-full bg-gray-100 overflow-hidden">
            <div id="strengthBar" class="h-full rounded-full transition-all duration-300" style="width:0%"></div>
          </div>
          <p id="strengthText" class="text-xs text-gray-400 mt-1"></p>
        </div>
        <?php if (isset($errors['password'])): ?>
          <p class="text-xs text-danger mt-1"><i class="bi bi-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['password'], ENT_QUOTES) ?></p>
        <?php endif; ?>
      </div>

      <!-- Xác nhận mật khẩu -->
      <div class="mb-5">
        <label for="password_confirm" class="form-label text-sm">Xác nhận mật khẩu <span class="text-danger">*</span></label>
        <div class="flex rounded-sm border-2 <?= isset($errors['password_confirm']) ? 'border-danger ring-4 ring-danger/10' : 'border-light-border focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10' ?> transition-all overflow-hidden">
          <span class="flex items-center px-3 text-gray-400 bg-gray-50 border-r border-light-border"><i class="bi bi-lock-fill"></i></span>
          <input type="password" id="password_confirm" name="password_confirm"
                 class="flex-1 px-3 py-2.5 text-sm border-0 outline-none bg-white"
                 placeholder="Nhập lại mật khẩu" autocomplete="new-password" required>
          <button type="button" onclick="togglePass('password_confirm',this)"
                  class="px-3.5 text-gray-400 hover:text-primary bg-white border-0 cursor-pointer transition-colors">
            <i class="bi bi-eye"></i>
          </button>
        </div>
        <?php if (isset($errors['password_confirm'])): ?>
          <p class="text-xs text-danger mt-1"><i class="bi bi-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['password_confirm'], ENT_QUOTES) ?></p>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary w-full py-3 text-base" id="btnRegister">
        <i class="bi bi-person-plus mr-2"></i>Tạo tài khoản
      </button>
    </form>

    <!-- Divider -->
    <div class="flex items-center gap-3 text-gray-400 text-sm my-5">
      <div class="flex-1 h-px bg-gray-100"></div>hoặc<div class="flex-1 h-px bg-gray-100"></div>
    </div>

    <!-- Google -->
    <div class="mb-5">
      <a href="<?= $appUrl ?>/auth/google"
         class="flex items-center justify-center gap-3 w-full py-2.5 rounded-sm border-2 border-light-border font-bold text-sm text-gray-700 no-underline hover:bg-gray-50 hover:border-gray-300 transition-all">
        <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        <span>Đăng ký với Google</span>
      </a>
    </div>

    <div class="text-center">
      <p class="text-sm text-gray-500 mb-0">
        Đã có tài khoản?
        <a href="<?= $appUrl ?>/login" class="font-bold text-primary no-underline hover:text-primary-dark">Đăng nhập</a>
      </p>
    </div>
  </div>
</div>

<script>
function togglePass(id, btn) {
  const input = document.getElementById(id);
  const icon  = btn.querySelector('i');
  input.type = input.type === 'password' ? 'text' : 'password';
  icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

// Password strength meter
const strengthColors = ['','#ef4444','#f59e0b','#3b82f6','#10b981'];
const strengthLabels = ['','Yếu','Trung bình','Khá mạnh','Mạnh 💪'];
document.getElementById('password').addEventListener('input', function() {
  const v = this.value;
  let score = 0;
  if (v.length >= 8)         score++;
  if (/[A-Z]/.test(v))      score++;
  if (/[0-9]/.test(v))      score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  const bar  = document.getElementById('strengthBar');
  const text = document.getElementById('strengthText');
  bar.style.width = (score * 25) + '%';
  bar.style.background = strengthColors[score] || '';
  text.textContent = score > 0 ? 'Độ mạnh: ' + strengthLabels[score] : '';
  text.style.color = strengthColors[score] || '';
});

// Prevent double submit
document.getElementById('registerForm').addEventListener('submit', function() {
  const btn = document.getElementById('btnRegister');
  btn.disabled = true;
  btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Đang tạo tài khoản...';
});
</script>
</body>
</html>
