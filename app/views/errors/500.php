<?php
/**
 * Error 500 — Lỗi máy chủ
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Lỗi máy chủ — SinhVienMarket</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="<?= $appUrl ?>/public/css/app.css">
  <style>
    @keyframes pulse500 {
      0%,100%{box-shadow:0 0 0 12px rgba(239,68,68,.1),0 16px 40px rgba(239,68,68,.3);}
      50%    {box-shadow:0 0 0 20px rgba(239,68,68,.05),0 16px 40px rgba(239,68,68,.45);}
    }
  </style>
</head>
<body class="font-sans antialiased min-h-screen flex items-center justify-center p-8"
      style="background:linear-gradient(135deg,#0f0c29 0%,#302b63 50%,#24243e 100%)">

  <div class="w-full max-w-lg text-center p-12 rounded-[28px]"
       style="background:rgba(255,255,255,.05);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.12);box-shadow:0 30px 80px rgba(0,0,0,.4)">

    <!-- Pulsing icon -->
    <div class="flex items-center justify-center text-4xl mx-auto mb-6"
         style="width:88px;height:88px;border-radius:50%;background:linear-gradient(135deg,#ef4444,#dc2626);animation:pulse500 2.5s ease-in-out infinite">
      ⚙️
    </div>

    <!-- Error code -->
    <div class="text-8xl font-black leading-none mb-2"
         style="background:linear-gradient(90deg,#ef4444,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
      500
    </div>

    <h1 class="text-2xl font-extrabold text-white mb-3">Lỗi máy chủ nội bộ</h1>
    <p class="text-white/60 leading-relaxed mb-8">
      Hệ thống đang gặp sự cố kỹ thuật. Đội ngũ của chúng tôi đã được thông báo và đang khắc phục.<br>
      Xin lỗi vì sự bất tiện này!
    </p>

    <!-- Actions -->
    <div class="flex gap-3 justify-center flex-wrap">
      <a href="<?= $appUrl ?>"
         class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-white text-sm no-underline transition-all"
         style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 8px 24px rgba(99,102,241,.4)"
         onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 32px rgba(99,102,241,.55)'"
         onmouseout="this.style.transform='';this.style.boxShadow='0 8px 24px rgba(99,102,241,.4)'">
        <i class="bi bi-house-fill"></i>Về trang chủ
      </a>
      <a href="javascript:history.back()"
         class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-white/75 text-sm no-underline transition-all hover:text-white"
         style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18)"
         onmouseover="this.style.background='rgba(255,255,255,.15)'"
         onmouseout="this.style.background='rgba(255,255,255,.08)'">
        <i class="bi bi-arrow-left"></i>Quay lại
      </a>
    </div>

    <div class="mt-8 text-xs text-white/30">
      <i class="bi bi-shield-check mr-1"></i>Lỗi đã được ghi nhật ký tự động.
    </div>
  </div>

</body>
</html>
