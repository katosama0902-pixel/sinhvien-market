<?php
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$tx = $tx ?? [];
$priceFmt = number_format($tx['amount'], 0, ',', '.') . 'đ';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>ZaloPay Sandbox Gateway</title>
  <style>
    body { background: #e5f2ff; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
    .zalo-card { background: white; border-radius: 20px; width: 380px; padding: 30px 20px; text-align: center; box-shadow: 0 15px 35px rgba(0, 104, 255, 0.15); border-top: 5px solid #0068ff; }
    .logo { color: #0068ff; font-size: 32px; font-weight: 800; letter-spacing: -1px; margin-bottom: 5px; }
    .sandbox-tag { background: #ffc107; color: #000; font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: bold; position: relative; top: -5px; }
    .price { font-size: 36px; font-weight: 700; color: #000; margin: 25px 0 10px; }
    .merchant { color: #666; font-size: 15px; margin-bottom: 30px; }
    .btn-pay { background: #0068ff; color: white; border: none; padding: 16px; width: 100%; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.2s; }
    .btn-pay:hover { background: #0056d6; transform: translateY(-2px); }
    .btn-cancel { background: transparent; color: #888; border: none; padding: 15px; width: 100%; border-radius: 12px; font-size: 15px; cursor: pointer; margin-top: 5px; }
    .btn-cancel:hover { background: #f5f5f5; color: #333; }
    .loader { display: none; width: 24px; height: 24px; border: 3px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: #fff; animation: spin 1s ease-in-out infinite; margin: 0 auto; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <div class="zalo-card">
    <div class="logo">ZaloPay <span class="sandbox-tag">SANDBOX</span></div>
    
    <div class="price"><?= $priceFmt ?></div>
    <div class="merchant">Thanh toán cho: <strong>SinhVienMarket</strong><br><small style="color:#999">Mã GD: #<?= $tx['id'] ?></small></div>

    <form action="<?= $appUrl ?>/transactions/zalopay-callback" method="POST" id="payForm">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
      <input type="hidden" name="transaction_id" value="<?= $tx['id'] ?>">
      
      <button type="submit" class="btn-pay" id="btnPay">
        <span id="btnText">Xác Nhận Thanh Toán</span>
        <div class="loader" id="btnLoader"></div>
      </button>
    </form>
    
    <a href="<?= $appUrl ?>/transactions/history"><button class="btn-cancel">Hủy giao dịch</button></a>
  </div>

  <script>
    document.getElementById('payForm').addEventListener('submit', function(e) {
      document.getElementById('btnText').style.display = 'none';
      document.getElementById('btnLoader').style.display = 'block';
      document.getElementById('btnPay').disabled = true;
    });
  </script>
</body>
</html>
