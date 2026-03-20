<?php
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$tx = $tx ?? [];
$priceFmt = number_format($tx['amount'], 0, ',', '.') . 'đ';
// Tạo mã QR VietQR động (Giả lập ngân hàng VCB, STK 112233)
// ID ngân hàng Vietcombank là 970436
$qrUrl = "https://img.vietqr.io/image/970436-0901234567-compact2.jpg?amount={$tx['amount']}&accountName=DAO%20MINH%20ANH&addInfo=ThanhToanDH" . $tx['id'];

use Core\Flash;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thanh toán Chuyển khoản — SinhVienMarket</title>
  <style>
    body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .bank-card { max-width: 450px; margin: 40px auto; background: white; border-radius: 16px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; }
    .qr-box { border: 2px dashed #0d6efd; padding: 15px; border-radius: 12px; margin: 20px 0; background: #f8f9fa; }
    .qr-img { width: 100%; max-width: 250px; border-radius: 8px; }
    .btn-confirm { background: #0d6efd; color: white; border: none; padding: 12px 0; width: 100%; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.2s; margin-top: 15px; }
    .btn-confirm:hover { background: #0b5ed7; }
    .amount { font-size: 24px; color: #dc3545; font-weight: bold; margin: 10px 0; }
  </style>
</head>
<body>
  <div class="bank-card">
    <h2 style="margin-top:0;color:#333;">Thanh toán Chuyển khoản</h2>
    <p style="color:#666;">Quét mã QR dưới đây bằng Ứng dụng Ngân hàng của bạn để thanh toán cho đơn hàng <strong>#<?= $tx['id'] ?></strong></p>
    
    <div class="amount"><?= $priceFmt ?></div>

    <div class="qr-box">
      <img src="<?= $qrUrl ?>" alt="VietQR" class="qr-img">
    </div>

    <p style="font-size: 14px; color: #888;">Nội dung CK: <strong>ThanhToanDH<?= $tx['id'] ?></strong></p>

    <form action="<?= $appUrl ?>/transactions/bank-confirm" method="POST">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
      <input type="hidden" name="transaction_id" value="<?= $tx['id'] ?>">
      <button type="submit" class="btn-confirm">Tôi Đã Chuyển Khoản Thành Công</button>
    </form>
    
    <div style="margin-top: 20px; font-size: 13px;">
      <a href="<?= $appUrl ?>/transactions/history" style="color: #6c757d; text-decoration: none;">&larr; Quay lại Lịch sử</a>
    </div>
  </div>
</body>
</html>
