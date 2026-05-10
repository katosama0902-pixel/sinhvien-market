<?php
/**
 * Admin Login View - Trang đăng nhập dành riêng cho Admin
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
  <title>Admin Panel — SinhVienMarket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/app.css" rel="stylesheet">
  <style>
    @keyframes slideUp { from{opacity:0;transform:translateY(36px) scale(.97);} to{opacity:1;transform:none;} }
    @keyframes gridLine {
      from { background-position: 0 0; }
      to   { background-position: 40px 40px; }
    }
  </style>
</head>
<body class="font-sans antialiased min-h-screen flex items-center justify-center" style="background:#020617">

<!-- Background -->
<div class="fixed inset-0 z-0"
     style="background:radial-gradient(ellipse at 60% 0%,rgba(236,72,153,.18) 0%,transparent 50%),radial-gradient(ellipse at 10% 100%,rgba(99,102,241,.2) 0%,transparent 50%),linear-gradient(180deg,#0f172a 0%,#020617 100%)">
  <!-- Grid overlay -->
  <div class="absolute inset-0"
       style="background-image:repeating-linear-gradient(0deg,transparent,transparent 40px,rgba(255,255,255,.02) 40px,rgba(255,255,255,.02) 41px),repeating-linear-gradient(90deg,transparent,transparent 40px,rgba(255,255,255,.02) 40px,rgba(255,255,255,.02) 41px)"></div>
</div>

<!-- Card -->
<div class="relative z-10 w-full max-w-md mx-4 p-8 rounded-3xl"
     style="background:rgba(30,41,59,.85);backdrop-filter:blur(24px);border:1px solid rgba(255,255,255,.08);box-shadow:0 40px 100px rgba(0,0,0,.6);animation:slideUp .5s cubic-bezier(.16,1,.3,1)">

  <!-- Logo -->
  <div class="text-center mb-6">
    <div class="flex items-center justify-center gap-3 mb-1">
      <div class="w-12 h-12 flex items-center justify-center rounded-xl text-white text-2xl flex-shrink-0"
           style="background:linear-gradient(135deg,#ec4899,#f97316);box-shadow:0 8px 24px rgba(236,72,153,.5)">
        <i class="bi bi-shield-lock"></i>
      </div>
      <div class="text-left">
        <div class="text-xl font-black text-white">Admin<span class="text-pink-400">Panel</span></div>
        <div class="text-xs text-white/40 font-medium">SinhVienMarket</div>
      </div>
    </div>
    <!-- Access badge -->
    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold mt-3"
          style="background:rgba(236,72,153,.12);border:1px solid rgba(236,72,153,.25);color:#f9a8d4">
      <i class="bi bi-lock-fill"></i>Yêu cầu xác thực
    </span>
  </div>

  <?= Flash::render() ?>

  <!-- Rate limit -->
  <?php if (!empty($errors['rate_limit'])): ?>
    <div class="flex items-center gap-2 mb-4 p-3 rounded-xl text-sm" style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);color:#fca5a5">
      <i class="bi bi-shield-exclamation flex-shrink-0"></i>
      <span><?= htmlspecialchars($errors['rate_limit'], ENT_QUOTES) ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($errors['general'])): ?>
    <div class="flex items-center gap-2 mb-4 p-3 rounded-xl text-sm" style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);color:#fca5a5">
      <i class="bi bi-exclamation-circle flex-shrink-0"></i>
      <span><?= htmlspecialchars($errors['general'], ENT_QUOTES) ?></span>
    </div>
  <?php endif; ?>

  <!-- Form -->
  <form action="<?= $appUrl ?>/admin-login" method="POST" novalidate id="adminLoginForm">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">

    <!-- Email -->
    <div class="mb-4">
      <label class="block text-xs font-bold text-white/65 mb-2 uppercase tracking-wider">Email quản trị</label>
      <div class="flex rounded-xl overflow-hidden transition-all <?= isset($errors['email']) ? 'ring-2 ring-red-500/50' : 'focus-within:ring-2 focus-within:ring-pink-500/40' ?>"
           style="background:rgba(255,255,255,.06);border:1.5px solid rgba(255,255,255,.1)">
        <span class="flex items-center pl-4 pr-2 text-white/35"><i class="bi bi-envelope"></i></span>
        <input type="email" name="email" id="email"
               class="flex-1 px-2 py-3 text-sm border-0 outline-none text-white placeholder-white/30"
               style="background:transparent"
               placeholder="admin@sinhvienmarket.vn"
               value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>"
               autocomplete="email" required>
      </div>
      <?php if (isset($errors['email'])): ?>
        <p class="text-xs text-red-400 mt-1.5"><i class="bi bi-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['email'], ENT_QUOTES) ?></p>
      <?php endif; ?>
    </div>

    <!-- Password -->
    <div class="mb-6">
      <label class="block text-xs font-bold text-white/65 mb-2 uppercase tracking-wider">Mật khẩu</label>
      <div class="flex rounded-xl overflow-hidden transition-all <?= isset($errors['password']) ? 'ring-2 ring-red-500/50' : 'focus-within:ring-2 focus-within:ring-pink-500/40' ?>"
           style="background:rgba(255,255,255,.06);border:1.5px solid rgba(255,255,255,.1)">
        <span class="flex items-center pl-4 pr-2 text-white/35"><i class="bi bi-key"></i></span>
        <input type="password" name="password" id="password"
               class="flex-1 px-2 py-3 text-sm border-0 outline-none text-white placeholder-white/30"
               style="background:transparent"
               placeholder="••••••••" autocomplete="current-password" required>
        <button type="button" onclick="togglePass('password',this)"
                class="px-4 text-white/35 hover:text-pink-400 border-0 bg-transparent cursor-pointer transition-colors">
          <i class="bi bi-eye"></i>
        </button>
      </div>
      <?php if (isset($errors['password'])): ?>
        <p class="text-xs text-red-400 mt-1.5"><i class="bi bi-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['password'], ENT_QUOTES) ?></p>
      <?php endif; ?>
    </div>

    <!-- Submit -->
    <button type="submit" id="btnAdminLogin"
            class="w-full py-3 rounded-xl font-extrabold text-white border-0 cursor-pointer transition-all text-base"
            style="background:linear-gradient(135deg,#ec4899,#f97316);box-shadow:0 6px 24px rgba(236,72,153,.4)"
            onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 10px 30px rgba(236,72,153,.55)'"
            onmouseout="this.style.transform='';this.style.boxShadow='0 6px 24px rgba(236,72,153,.4)'">
      <i class="bi bi-shield-check mr-2"></i>Xác thực & Đăng nhập
    </button>
  </form>

  <!-- Security note -->
  <div class="flex items-center gap-2 mt-4 p-3 rounded-xl text-xs"
       style="background:rgba(251,191,36,.07);border:1px solid rgba(251,191,36,.18);color:rgba(251,191,36,.75)">
    <i class="bi bi-shield-lock-fill flex-shrink-0"></i>
    <span>Hệ thống bảo mật theo dõi mọi lần đăng nhập. Tối đa 5 lần sai / 15 phút.</span>
  </div>

  <div class="text-center mt-4">
    <a href="<?= $appUrl ?>/login-role" class="text-xs no-underline transition-colors" style="color:rgba(255,255,255,.3)"
       onmouseover="this.style.color='rgba(255,255,255,.65)'" onmouseout="this.style.color='rgba(255,255,255,.3)'">
      <i class="bi bi-arrow-left mr-1"></i>Quay lại cổng chính
    </a>
  </div>
</div>

<script>
function togglePass(id, btn) {
  const input = document.getElementById(id);
  const icon  = btn.querySelector('i');
  input.type = input.type === 'password' ? 'text' : 'password';
  icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
document.getElementById('adminLoginForm').addEventListener('submit', function() {
  const btn = document.getElementById('btnAdminLogin');
  btn.disabled = true;
  btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Đang xác thực...';
});
</script>
</body>
</html>
