<?php
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Chọn vai trò đăng nhập — SinhVienMarket</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= $appUrl ?>/public/css/style.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      min-height: 100vh;
      overflow: hidden;
    }

    .role-page {
      min-height: 100vh;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
      background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #1a055c 100%);
    }

    /* Animated blobs */
    .blob {
      position: absolute;
      border-radius: 50%;
      filter: blur(80px);
      opacity: .55;
      animation: blobDrift 10s ease-in-out infinite alternate;
    }
    .blob-1 { width: 550px; height: 550px; background: #6366f1; top: -200px; right: -100px; animation-duration: 12s; }
    .blob-2 { width: 400px; height: 400px; background: #ec4899; bottom: -150px; left: -80px; animation-duration: 9s; animation-delay: -3s; }
    .blob-3 { width: 300px; height: 300px; background: #8b5cf6; top: 50%; left: 50%; transform: translate(-50%, -50%); animation-duration: 15s; animation-delay: -6s; }

    @keyframes blobDrift {
      from { transform: translate(0, 0) scale(1); }
      to   { transform: translate(40px, 30px) scale(1.15); }
    }

    .role-content { position: relative; z-index: 2; width: 100%; }

    .role-header { text-align: center; margin-bottom: 2.5rem; }
    .role-logo {
      font-size: 2.2rem; font-weight: 900;
      background: linear-gradient(135deg, #a5b4fc, #f0abfc);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
      display: block; margin-bottom: .4rem;
    }
    .role-subtitle {
      color: rgba(255,255,255,.6);
      font-size: 1rem; font-weight: 500;
    }

    /* Role cards */
    .role-card {
      background: rgba(255,255,255,.08);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1.5px solid rgba(255,255,255,.15);
      border-radius: 24px;
      padding: 2.5rem 2rem;
      text-align: center;
      cursor: pointer;
      transition: transform .35s cubic-bezier(.34,1.56,.64,1), box-shadow .3s ease, border-color .3s ease, background .3s ease;
      text-decoration: none;
      display: block;
      color: #fff;
      height: 100%;
    }

    .role-card:hover {
      transform: translateY(-10px) scale(1.02);
      color: #fff;
    }

    .role-card.student:hover {
      background: rgba(99,102,241,.25);
      border-color: rgba(99,102,241,.6);
      box-shadow: 0 20px 60px rgba(99,102,241,.4), 0 0 0 1px rgba(99,102,241,.3);
    }

    .role-card.admin:hover {
      background: rgba(236,72,153,.2);
      border-color: rgba(236,72,153,.5);
      box-shadow: 0 20px 60px rgba(236,72,153,.35), 0 0 0 1px rgba(236,72,153,.25);
    }

    .role-icon-wrap {
      width: 90px; height: 90px;
      border-radius: 24px;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 1.5rem;
      font-size: 2.8rem;
      position: relative;
    }

    .student .role-icon-wrap {
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      box-shadow: 0 12px 32px rgba(99,102,241,.5);
    }

    .admin .role-icon-wrap {
      background: linear-gradient(135deg, #ec4899, #f43f5e);
      box-shadow: 0 12px 32px rgba(236,72,153,.5);
    }

    .role-card h4 {
      font-weight: 800; font-size: 1.3rem; margin-bottom: .6rem; color: #fff;
    }

    .role-card p {
      color: rgba(255,255,255,.65);
      font-size: .9rem; line-height: 1.6;
      margin-bottom: 1.5rem;
    }

    .role-btn {
      display: inline-block;
      padding: .6rem 1.5rem;
      border-radius: 50px;
      font-weight: 700; font-size: .9rem;
      border: 1.5px solid rgba(255,255,255,.35);
      background: rgba(255,255,255,.1);
      color: #fff;
      transition: all .2s;
    }
    .role-card:hover .role-btn {
      background: rgba(255,255,255,.25);
      border-color: rgba(255,255,255,.6);
    }

    .role-back {
      text-align: center;
      margin-top: 2rem;
    }
    .role-back a {
      color: rgba(255,255,255,.5);
      font-size: .9rem;
      text-decoration: none;
      transition: color .2s;
    }
    .role-back a:hover { color: rgba(255,255,255,.85); }
  </style>
</head>
<body>

<div class="role-page">
  <!-- Animated blobs -->
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>

  <div class="role-content">
    <div class="container">
      <div class="role-header fade-in-up">
        <span class="role-logo"><i class="bi bi-shop-window me-2"></i>SinhVienMarket</span>
        <p class="role-subtitle">Vui lòng chọn cổng đăng nhập phù hợp với tài khoản của bạn</p>
      </div>

      <div class="row g-4 justify-content-center mx-auto" style="max-width:780px">
        <!-- Sinh Viên -->
        <div class="col-md-6 fade-in-up delay-100">
          <a href="<?= $appUrl ?>/login" class="role-card student">
            <div class="role-icon-wrap">
              <i class="bi bi-mortarboard"></i>
            </div>
            <h4>Tài khoản Sinh Viên</h4>
            <p>Mua bán, trao đổi giáo trình và tham gia đấu giá ngược mỗi ngày.</p>
            <span class="role-btn">Đăng nhập <i class="bi bi-arrow-right ms-1"></i></span>
          </a>
        </div>

        <!-- Admin -->
        <div class="col-md-6 fade-in-up delay-200">
          <a href="<?= $appUrl ?>/admin-login" class="role-card admin">
            <div class="role-icon-wrap">
              <i class="bi bi-shield-lock"></i>
            </div>
            <h4>Ban Quản Trị</h4>
            <p>Hệ thống xét duyệt, quản lý gian hàng và tổ chức Sự kiện Giveaway.</p>
            <span class="role-btn">Truy cập Admin <i class="bi bi-arrow-right ms-1"></i></span>
          </a>
        </div>
      </div>

      <div class="role-back fade-in-up delay-300">
        <a href="<?= $appUrl ?>/products">
          <i class="bi bi-arrow-left me-1"></i>Trở về trang chủ
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
