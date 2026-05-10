<?php
/**
 * View: Thông báo tài khoản bị khóa
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

$lockedUser  = $_SESSION['user'] ?? [];
if (empty($lockedUser)) {
    header('Location: ' . $appUrl . '/login-role');
    exit;
}

$lockedAt    = $lockedUser['locked_at']    ?? null;
$lockedUntil = $lockedUser['locked_until'] ?? null;
$lockReason  = $lockedUser['lock_reason']  ?? 'Vi phạm điều khoản sử dụng';

$lockedAtFmt    = $lockedAt    ? date('H:i:s \n\g\à\y d/m/Y', strtotime($lockedAt))    : null;
$lockedUntilFmt = $lockedUntil ? date('H:i:s \n\g\à\y d/m/Y', strtotime($lockedUntil)) : null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tài khoản bị khóa — SinhVienMarket</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/app.css" rel="stylesheet">
  <style>
    @keyframes blobFloat { from{transform:translate(0,0) scale(1);} to{transform:translate(30px,20px) scale(1.1);} }
    @keyframes slideUp   { from{opacity:0;transform:translateY(44px) scale(.97);} to{opacity:1;transform:none;} }
    @keyframes iconPulse {
      0%,100%{box-shadow:0 12px 36px rgba(239,68,68,.5);}
      50%{box-shadow:0 12px 48px rgba(239,68,68,.75),0 0 0 12px rgba(239,68,68,.12);}
    }
    @keyframes particleFloat { from{transform:translateY(0);opacity:.2;} to{transform:translateY(-28px);opacity:.8;} }
  </style>
</head>
<body class="font-sans antialiased min-h-screen flex items-center justify-center p-6 relative overflow-hidden"
      style="background:linear-gradient(135deg,#0f0c29 0%,#302b63 50%,#1a055c 100%)">

<!-- Animated blobs -->
<div class="absolute rounded-full pointer-events-none" style="width:500px;height:500px;background:#ef4444;top:-180px;right:-100px;filter:blur(80px);opacity:.45;animation:blobFloat 14s ease-in-out infinite alternate"></div>
<div class="absolute rounded-full pointer-events-none" style="width:380px;height:380px;background:#dc2626;bottom:-150px;left:-80px;filter:blur(80px);opacity:.45;animation:blobFloat 10s ease-in-out infinite alternate-reverse;animation-delay:-5s"></div>
<div class="absolute rounded-full pointer-events-none" style="width:250px;height:250px;background:#7c3aed;top:40%;left:40%;filter:blur(80px);opacity:.4;animation:blobFloat 8s ease-in-out infinite alternate;animation-delay:-3s"></div>

<!-- Floating particles -->
<div class="absolute w-2 h-2 rounded-full bg-white/30" style="top:15%;left:8%;animation:particleFloat 6s ease-in-out infinite alternate"></div>
<div class="absolute w-2 h-2 rounded-full bg-white/30" style="top:65%;left:12%;animation:particleFloat 6s ease-in-out infinite alternate;animation-delay:1.8s"></div>
<div class="absolute w-2 h-2 rounded-full bg-white/30" style="top:28%;right:9%;animation:particleFloat 6s ease-in-out infinite alternate;animation-delay:.7s"></div>
<div class="absolute w-2 h-2 rounded-full bg-white/30" style="top:80%;right:18%;animation:particleFloat 6s ease-in-out infinite alternate;animation-delay:2.5s"></div>

<!-- Card -->
<div class="relative z-10 w-full max-w-lg bg-white rounded-[28px] text-center p-10"
     style="box-shadow:0 40px 100px rgba(0,0,0,.4),0 0 0 1px rgba(255,255,255,.3);animation:slideUp .55s cubic-bezier(.16,1,.3,1)">

  <!-- Lock icon -->
  <div class="w-22 h-22 rounded-full flex items-center justify-center mx-auto mb-5 text-white text-5xl"
       style="width:88px;height:88px;background:linear-gradient(135deg,#ef4444,#dc2626);animation:iconPulse 2s ease-in-out infinite">
    <i class="bi bi-lock-fill"></i>
  </div>

  <h1 class="text-2xl font-black text-gray-900 tracking-tight mb-1">Tài khoản bị khóa</h1>
  <p class="text-gray-500 text-sm mb-6">
    Xin chào <strong class="text-gray-800"><?= htmlspecialchars($lockedUser['name'] ?? 'bạn', ENT_QUOTES) ?></strong>,
    tài khoản của bạn hiện đang bị hạn chế truy cập.
  </p>

  <!-- Info card -->
  <div class="rounded-2xl mb-5 text-left divide-y divide-red-100 overflow-hidden"
       style="background:#fff7f7;border:1.5px solid #fecaca">

    <?php if ($lockedAtFmt): ?>
    <div class="flex items-start gap-3 p-4">
      <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-red-400 mt-0.5" style="background:#fef2f2">
        <i class="bi bi-clock-fill"></i>
      </div>
      <div>
        <div class="text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Thời điểm bị khóa</div>
        <div class="text-sm font-bold text-gray-800 mt-0.5"><?= $lockedAtFmt ?></div>
      </div>
    </div>
    <?php endif; ?>

    <div class="flex items-start gap-3 p-4">
      <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-orange-400 mt-0.5" style="background:#fff7ed">
        <i class="bi bi-exclamation-triangle-fill"></i>
      </div>
      <div>
        <div class="text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Lý do khóa</div>
        <div class="text-sm font-bold text-red-600 mt-0.5"><?= htmlspecialchars($lockReason, ENT_QUOTES) ?></div>
      </div>
    </div>

    <div class="flex items-start gap-3 p-4">
      <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-green-500 mt-0.5" style="background:#f0fdf4">
        <i class="bi bi-calendar-check-fill"></i>
      </div>
      <div>
        <div class="text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Hệ thống sẽ mở lại vào</div>
        <?php if ($lockedUntilFmt): ?>
          <div class="text-sm font-bold text-green-600 mt-0.5"><?= $lockedUntilFmt ?></div>
        <?php else: ?>
          <div class="text-sm font-bold text-red-700 mt-0.5">
            <i class="bi bi-infinity mr-1"></i>Vĩnh viễn (liên hệ Admin để được hỗ trợ)
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Footer message -->
  <p class="text-sm text-gray-400 leading-relaxed border-t border-gray-100 pt-5 mb-5">
    <i class="bi bi-info-circle mr-1"></i>
    Mong bạn vui lòng sử dụng hệ thống có chuẩn mực hơn!<br>
    Nếu bạn cho rằng đây là nhầm lẫn, hãy liên hệ với quản trị viên ngay.
  </p>

  <!-- Action buttons -->
  <div class="flex flex-wrap gap-3 justify-center">
    <a href="mailto:admin@sinhvienmarket.vn?subject=Khiếu nại khóa tài khoản — <?= htmlspecialchars($lockedUser['email'] ?? '', ENT_QUOTES) ?>"
       class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-extrabold text-white text-sm no-underline transition-all"
       style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 6px 20px rgba(99,102,241,.4)"
       onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 10px 28px rgba(99,102,241,.5)'"
       onmouseout="this.style.transform='';this.style.boxShadow='0 6px 20px rgba(99,102,241,.4)'">
      <i class="bi bi-envelope-fill"></i>Liên hệ Admin
    </a>
    <a href="<?= $appUrl ?>/logout"
       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-gray-500 text-sm no-underline bg-gray-100 hover:bg-gray-200 hover:text-gray-700 transition-all">
      <i class="bi bi-box-arrow-right"></i>Đăng xuất
    </a>
  </div>
</div>

</body>
</html>
