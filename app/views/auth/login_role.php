<?php
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Chọn vai trò đăng nhập — SinhVienMarket</title>
  <meta name="description" content="Chọn cổng đăng nhập phù hợp với tài khoản SinhVienMarket của bạn.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/app.css" rel="stylesheet">
</head>
<body class="font-sans antialiased overflow-hidden">

<!-- Background -->
<div class="min-h-screen flex items-center justify-center p-6 relative"
     style="background:linear-gradient(135deg,#0f0c29 0%,#302b63 50%,#1a055c 100%)">

  <!-- Animated blobs -->
  <div class="absolute rounded-full pointer-events-none" style="width:550px;height:550px;background:#6366f1;top:-200px;right:-100px;filter:blur(80px);opacity:.55;animation:blobDrift 12s ease-in-out infinite alternate"></div>
  <div class="absolute rounded-full pointer-events-none" style="width:400px;height:400px;background:#ec4899;bottom:-150px;left:-80px;filter:blur(80px);opacity:.55;animation:blobDrift 9s ease-in-out infinite alternate-reverse;animation-delay:-3s"></div>
  <div class="absolute rounded-full pointer-events-none" style="width:300px;height:300px;background:#8b5cf6;top:50%;left:50%;transform:translate(-50%,-50%);filter:blur(80px);opacity:.5;animation:blobDrift 15s ease-in-out infinite alternate;animation-delay:-6s"></div>

  <style>
    @keyframes blobDrift {
      from { transform: translate(0,0) scale(1); }
      to   { transform: translate(40px,30px) scale(1.15); }
    }
    .blob-3 { transform: translate(-50%,-50%) !important; }
  </style>

  <!-- Content -->
  <div class="relative z-10 w-full max-w-3xl">

    <!-- Header -->
    <div class="text-center mb-10 animate-slide-up">
      <span class="text-4xl font-black block mb-1"
            style="background:linear-gradient(135deg,#a5b4fc,#f0abfc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">
        <i class="bi bi-shop-window mr-2"></i>SinhVienMarket
      </span>
      <p class="text-white/60 text-base font-medium">Vui lòng chọn cổng đăng nhập phù hợp với tài khoản của bạn</p>
    </div>

    <!-- Role Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-2xl mx-auto">

      <!-- Sinh Viên -->
      <a href="<?= $appUrl ?>/login" class="group no-underline block animate-slide-up" style="animation-delay:.1s">
        <div class="rounded-3xl p-10 text-center cursor-pointer h-full transition-all duration-350"
             style="background:rgba(255,255,255,.08);backdrop-filter:blur(20px);border:1.5px solid rgba(255,255,255,.15)"
             onmouseover="this.style.background='rgba(99,102,241,.25)';this.style.borderColor='rgba(99,102,241,.6)';this.style.boxShadow='0 20px 60px rgba(99,102,241,.4)';this.style.transform='translateY(-10px) scale(1.02)'"
             onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.borderColor='rgba(255,255,255,.15)';this.style.boxShadow='none';this.style.transform='none'">
          <div class="w-24 h-24 rounded-3xl flex items-center justify-center mx-auto mb-5 text-5xl text-white"
               style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 12px 32px rgba(99,102,241,.5)">
            <i class="bi bi-mortarboard"></i>
          </div>
          <h4 class="text-white font-extrabold text-xl mb-2">Tài khoản Sinh Viên</h4>
          <p class="text-white/60 text-sm leading-relaxed mb-5">Mua bán, trao đổi giáo trình và tham gia đấu giá ngược mỗi ngày.</p>
          <span class="inline-flex items-center gap-1 px-5 py-2 rounded-full text-white font-bold text-sm transition-all"
                style="border:1.5px solid rgba(255,255,255,.35);background:rgba(255,255,255,.1)">
            Đăng nhập <i class="bi bi-arrow-right ml-1"></i>
          </span>
        </div>
      </a>

      <!-- Admin -->
      <a href="<?= $appUrl ?>/admin-login" class="group no-underline block animate-slide-up" style="animation-delay:.2s">
        <div class="rounded-3xl p-10 text-center cursor-pointer h-full transition-all duration-350"
             style="background:rgba(255,255,255,.08);backdrop-filter:blur(20px);border:1.5px solid rgba(255,255,255,.15)"
             onmouseover="this.style.background='rgba(236,72,153,.2)';this.style.borderColor='rgba(236,72,153,.5)';this.style.boxShadow='0 20px 60px rgba(236,72,153,.35)';this.style.transform='translateY(-10px) scale(1.02)'"
             onmouseout="this.style.background='rgba(255,255,255,.08)';this.style.borderColor='rgba(255,255,255,.15)';this.style.boxShadow='none';this.style.transform='none'">
          <div class="w-24 h-24 rounded-3xl flex items-center justify-center mx-auto mb-5 text-5xl text-white"
               style="background:linear-gradient(135deg,#ec4899,#f43f5e);box-shadow:0 12px 32px rgba(236,72,153,.5)">
            <i class="bi bi-shield-lock"></i>
          </div>
          <h4 class="text-white font-extrabold text-xl mb-2">Ban Quản Trị</h4>
          <p class="text-white/60 text-sm leading-relaxed mb-5">Hệ thống xét duyệt, quản lý gian hàng và tổ chức Sự kiện Giveaway.</p>
          <span class="inline-flex items-center gap-1 px-5 py-2 rounded-full text-white font-bold text-sm transition-all"
                style="border:1.5px solid rgba(255,255,255,.35);background:rgba(255,255,255,.1)">
            Truy cập Admin <i class="bi bi-arrow-right ml-1"></i>
          </span>
        </div>
      </a>
    </div>

    <!-- Back link -->
    <div class="text-center mt-8 animate-slide-up" style="animation-delay:.3s">
      <a href="<?= $appUrl ?>/products" class="text-white/50 hover:text-white/90 text-sm no-underline transition-colors">
        <i class="bi bi-arrow-left mr-1"></i>Trở về trang chủ
      </a>
    </div>
  </div>
</div>

</body>
</html>
