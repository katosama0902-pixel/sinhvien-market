<?php
/**
 * View: Chi tiết sản phẩm + Đấu giá ngược
 * Biến nhận: $product (đã join auction), $auctionPrice
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$user   = $_SESSION['user'] ?? null;
use Core\Controller;

$ctrl = new class extends Controller {};
$csrf = $ctrl->csrfToken();

function fmtPrice(int $price): string {
    return number_format($price, 0, ',', '.') . 'đ';
}

$p = $product;
?>

<div class="container py-4">
  <div class="row g-4">

    <!-- ─── Ảnh sản phẩm ──────────────────────────────── -->
    <div class="col-lg-6 fade-in-up delay-100">
      <div class="card-sv p-0 overflow-hidden" style="max-height:420px; border-radius:16px;">
        <?php if ($p['image']): ?>
          <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($p['image'], ENT_QUOTES) ?>"
               alt="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>"
               class="w-100" style="object-fit:cover;height:420px">
        <?php else: ?>
          <div class="d-flex align-items-center justify-content-center bg-light" style="height:420px">
            <i class="bi bi-image text-muted" style="font-size:5rem;opacity:.2"></i>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ─── Thông tin + Hành động ──────────────────────── -->
    <div class="col-lg-6 fade-in-up delay-200">

      <!-- Breadcrumb nhỏ -->
      <div class="mb-2 small text-muted">
        <a href="<?= $appUrl ?>/products" class="text-decoration-none text-muted">Sản phẩm</a>
        <i class="bi bi-chevron-right mx-1"></i>
        <span><?= htmlspecialchars($p['category_name'], ENT_QUOTES) ?></span>
      </div>

      <!-- Loại -->
      <?php if ($p['type'] === 'auction'): ?>
        <span class="badge-auction mb-2 d-inline-block px-3 py-1">
          <i class="bi bi-lightning-fill me-1"></i>Đấu giá ngược
        </span>
      <?php elseif ($p['type'] === 'exchange'): ?>
        <span class="badge bg-info text-white mb-2">🔄 Trao đổi</span>
      <?php endif; ?>

      <!-- Tiêu đề -->
      <h4 class="fw-700 mb-3"><?= htmlspecialchars($p['title'], ENT_QUOTES) ?></h4>

      <!-- Người bán + Ngày -->
      <div class="d-flex gap-3 mb-4 small text-muted">
        <span><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($p['seller_name'], ENT_QUOTES) ?></span>
        <span><i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($p['created_at'])) ?></span>
        <span><i class="bi bi-folder me-1"></i><?= htmlspecialchars($p['category_name'], ENT_QUOTES) ?></span>
      </div>

      <?php if ($p['type'] === 'auction' && $auctionPrice): ?>
        <!-- ─── Khối đấu giá ngược ─────────────────────────────── -->
        <div class="auction-card p-4 mb-4 rounded-4 hover-lift" style="background:linear-gradient(135deg,#fff5f5,#fff0f8);border:2px solid #ffcdd2">

          <!-- Giá hiện tại -->
          <div class="text-center mb-3">
            <p class="small text-muted mb-1"><i class="bi bi-tag-fill me-1"></i>Giá hiện tại</p>
            <div class="price-display-xl" id="currentPriceDisplay">
              <?= fmtPrice($auctionPrice['current_price']) ?>
            </div>
            <?php if (!$auctionPrice['is_at_floor']): ?>
              <small class="text-danger">
                <i class="bi bi-arrow-down-circle-fill me-1"></i>
                Giảm còn: <strong><?= fmtPrice($auctionPrice['floor_price']) ?></strong>
              </small>
            <?php else: ?>
              <span class="badge bg-danger">🔒 Đã chạm giá sàn</span>
            <?php endif; ?>
          </div>

          <!-- Countdown đến lần giảm tiếp theo -->
          <?php if (!$auctionPrice['is_at_floor']): ?>
            <div class="text-center mb-3">
              <p class="small text-muted mb-1">Giảm tiếp theo sau</p>
              <div class="countdown-timer" id="countdownTimer">--:--</div>
              <small class="text-muted">Giảm <?= fmtPrice($auctionPrice['decrease_amount']) ?>/lần • Mỗi <?= $auctionPrice['step_minutes'] ?> phút</small>
            </div>
          <?php endif; ?>

          <!-- Thông số đấu giá -->
          <div class="row g-2 mb-3 text-center">
            <div class="col-6">
              <div class="bg-white rounded-3 p-2">
                <div class="small text-muted">Giá khởi điểm</div>
                <div class="fw-700 text-dark"><?= fmtPrice($auctionPrice['start_price']) ?></div>
              </div>
            </div>
            <div class="col-6">
              <div class="bg-white rounded-3 p-2">
                <div class="small text-muted">Giá sàn</div>
                <div class="fw-700 text-danger"><?= fmtPrice($auctionPrice['floor_price']) ?></div>
              </div>
            </div>
          </div>

          <!-- Nút mua -->
          <?php if (!$user): ?>
            <a href="<?= $appUrl ?>/login" class="btn btn-danger w-100 btn-lg">
              <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập để mua
            </a>
          <?php elseif ((int)$p['user_id'] === (int)$user['id']): ?>
            <p class="text-center text-muted small mb-0"><em>Đây là sản phẩm của bạn</em></p>
          <?php elseif ($p['status'] === 'active' && $p['auction_status'] === 'active'): ?>
            <button type="button" class="btn btn-danger w-100 btn-lg" id="btnBuyNow"
                    onclick="prepareCheckout(<?= $p['auction_id'] ?>, <?= $auctionPrice['current_price'] ?>, 'auction')">
              <i class="bi bi-lightning-fill me-2"></i>Mua ngay với giá
              <span id="btnPrice"><?= fmtPrice($auctionPrice['current_price']) ?></span>
            </button>
            <p class="text-center small text-muted mt-2 mb-0">
              <i class="bi bi-shield-check me-1"></i>Giao dịch được bảo vệ • Không thể hoàn tác
            </p>
          <?php else: ?>
            <button class="btn btn-secondary w-100 btn-lg" disabled>
              <i class="bi bi-bag-x me-2"></i>Sản phẩm đã được mua
            </button>
          <?php endif; ?>
        </div>

      <?php elseif ($p['type'] === 'sale'): ?>
        <!-- ─── Giá bán thường ─────────────────────────────────── -->
        <div class="mb-4">
          <span class="price-badge" style="font-size:1.8rem"><?= fmtPrice((int)$p['price']) ?></span>
        </div>
        <?php if (!$user): ?>
            <a href="<?= $appUrl ?>/login" class="btn btn-primary w-100 btn-lg mb-2">
              <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập để mua
            </a>
        <?php elseif ((int)$p['user_id'] === (int)$user['id']): ?>
            <p class="text-center text-muted small"><em>Đây là sản phẩm của bạn</em></p>
        <?php elseif ($p['status'] === 'active'): ?>
          <button type="button" class="btn btn-primary w-100 btn-lg mb-2" onclick="prepareCheckout(<?= $p['id'] ?>, <?= $p['price'] ?>, 'sale')">
            <i class="bi bi-cart-check me-2"></i>Mua ngay
          </button>
          <p class="text-muted small text-center mt-2">
            <i class="bi bi-shield-check me-1"></i>Thanh toán an toàn đa phương thức
          </p>
        <?php endif; ?>
      <?php else: ?>
        <!-- ─── Trao đổi ────────────────────────────────────────── -->
        <div class="mb-4">
          <span class="badge bg-info fs-5 px-3 py-2">🔄 Trao đổi đồ</span>
        </div>
      <?php endif; ?>

      <!-- ─── Trạng thái nếu đã bán ────────────────────────────── -->
      <?php if ($p['status'] === 'sold'): ?>
        <div class="alert alert-secondary">
          <i class="bi bi-bag-check me-2"></i>Sản phẩm đã được mua • Không còn khả dụng
        </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- ─── Mô tả chi tiết ────────────────────────────────────────────── -->
  <div class="row mt-4 fade-in-up delay-300">
    <div class="col-lg-8">
      <div class="card-sv p-4 hover-lift">
        <h5 class="fw-700 mb-3">Mô tả sản phẩm</h5>
        <div style="white-space:pre-wrap;line-height:1.8">
          <?= nl2br(htmlspecialchars($p['description'], ENT_QUOTES)) ?>
        </div>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="card-sv p-4 hover-lift">
        <h5 class="fw-700 mb-3">Thông tin người bán</h5>
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
               style="width:48px;height:48px;font-size:1.4rem;font-weight:700">
            <?= mb_strtoupper(mb_substr($p['seller_name'], 0, 1)) ?>
          </div>
          <div>
            <div class="fw-700"><?= htmlspecialchars($p['seller_name'], ENT_QUOTES) ?></div>
            <div class="small text-muted">Sinh viên / Thành viên</div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ─── Modal Thanh Toán / Checkout ────────────────────────────── -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-light border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold" id="checkoutModalLabel"><i class="bi bi-cart-check-fill text-primary me-2"></i>Thanh Toán Đơn Hàng</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= $appUrl ?>/transactions/checkout" method="POST" id="checkoutForm">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
        <input type="hidden" name="target_id" id="checkoutTargetId" value="">
        <input type="hidden" name="type" id="checkoutType" value="">
        <input type="hidden" name="price" id="checkoutPrice" value="">

        <div class="modal-body p-4 pt-3">
          <!-- Thông tin tóm tắt -->
          <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded-3 bg-white border border-light shadow-sm">
            <span class="text-muted">Tổng thanh toán:</span>
            <span class="text-danger fw-bold fs-4" id="checkoutTotalDisplay">0đ</span>
          </div>

          <!-- Thông tin nhận hàng -->
          <h6 class="fw-bold mb-3">Thông tin giao nhận</h6>
          <div class="mb-4">
            <label for="shipping_address" class="form-label small text-muted">Địa chỉ nhận hàng (Ký túc xá, Lớp học...)</label>
            <textarea id="shipping_address" name="shipping_address" class="form-control bg-light" rows="2" placeholder="VD: Phòng 204 Ký túc xá khu A..." required></textarea>
          </div>

          <!-- Phương thức thanh toán -->
          <h6 class="fw-bold mb-3">Phương thức thanh toán</h6>
          <div class="list-group list-group-flush border rounded-3 overflow-hidden">
            <!-- COD -->
            <label class="list-group-item d-flex align-items-center cursor-pointer p-3 py-3">
              <input class="form-check-input me-3" type="radio" name="payment_method" value="cod" checked>
              <div class="flex-grow-1">
                <div class="fw-600">Thanh toán khi nhận hàng (COD)</div>
              </div>
              <i class="bi bi-cash-stack fs-4 text-success"></i>
            </label>
            
            <!-- VietQR Bank -->
            <label class="list-group-item d-flex align-items-center cursor-pointer p-3 py-3">
              <input class="form-check-input me-3" type="radio" name="payment_method" value="banking">
              <div class="flex-grow-1">
                <div class="fw-600">Chuyển khoản Ngân hàng (VietQR)</div>
              </div>
              <i class="bi bi-bank fs-4 text-primary"></i>
            </label>

            <!-- ZaloPay -->
            <label class="list-group-item d-flex align-items-center cursor-pointer p-3 py-3">
              <input class="form-check-input me-3" type="radio" name="payment_method" value="zalopay">
              <div class="flex-grow-1">
                <div class="fw-600">Chuyển qua ZaloPay (Thử nghiệm)</div>
              </div>
              <i class="bi bi-wallet2 fs-4" style="color:#0068ff"></i>
            </label>
          </div>
        </div>
        <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary px-4" id="btnSubmitCheckout">
            Xác Nhận Đặt Hàng <i class="bi bi-arrow-right ms-1"></i>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php if ($p['type'] === 'auction' && isset($p['auction_id']) && $p['auction_status'] === 'active'): ?>
<script>
(function() {
  var appUrl    = '<?= $appUrl ?>';
  var auctionId = <?= $p['auction_id'] ?>;
  var interval  = null;
  var cdInterval = null;
  var nextDropIn = <?= $auctionPrice['next_drop_in_seconds'] ?? 0 ?>;

  // ── Countdown timer ─────────────────────────────────────────────
  function startCountdown(seconds) {
    clearInterval(cdInterval);
    var el = document.getElementById('countdownTimer');
    if (!el) return;

    cdInterval = setInterval(function() {
      seconds--;
      if (seconds <= 0) {
        clearInterval(cdInterval);
        fetchPrice(); // lấy giá mới ngay khi countdown về 0
      }
      var m = Math.floor(seconds / 60).toString().padStart(2, '0');
      var s = (seconds % 60).toString().padStart(2, '0');
      el.textContent = m + ':' + s;
      // Đổi màu đỏ khi còn < 10 giây
      el.style.color = seconds < 10 ? '#ef4444' : '';
    }, 1000);
  }

  // ── Polling giá ─────────────────────────────────────────────────
  function fetchPrice() {
    fetch(appUrl + '/api/auction/price?id=' + auctionId)
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.status !== 'active') {
          clearInterval(interval);
          document.getElementById('currentPriceDisplay').textContent = 'Đã kết thúc';
          var btn = document.getElementById('btnBuyNow');
          if (btn) { btn.disabled = true; btn.textContent = 'Đã bán'; }
          return;
        }

        // Cập nhật giá
        var fmt = data.formatted_price;
        var priceEl = document.getElementById('currentPriceDisplay');
        if (priceEl) { priceEl.textContent = fmt; priceEl.classList.add('price-updated'); setTimeout(function() { priceEl.classList.remove('price-updated'); }, 600); }

        // Cập nhật nút mua
        var btnPrice = document.getElementById('btnPrice');
        if (btnPrice) btnPrice.textContent = fmt;
        var buyPrice = document.getElementById('buyPriceInput');
        if (buyPrice) buyPrice.value = data.current_price;

        // Reset countdown
        if (data.next_drop_in_seconds && !data.is_at_floor) {
          startCountdown(data.next_drop_in_seconds);
        }
      })
      .catch(function() {}); // Bỏ qua lỗi mạng
  }

  // Khởi động
  startCountdown(nextDropIn);
  interval = setInterval(fetchPrice, 8000); // poll mỗi 8 giây
  
})();

</style>
<?php endif; ?>

<script>
// Checkout Modal Logic (Dành cho Mua ngay và Auction)
function prepareCheckout(targetId, price, type) {
  var modalEl = document.getElementById('checkoutModal');
  if (!modalEl) { alert("Thiếu thư viện JS Bootstrap để hiển thị Modal!"); return; }
  var modal = new bootstrap.Modal(modalEl);

  // Gán value cho Form
  document.getElementById('checkoutTargetId').value = targetId;
  document.getElementById('checkoutType').value     = type;
  document.getElementById('checkoutPrice').value    = price;

  // Hiển thị giá định dạng
  var fmtPrice = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
  document.getElementById('checkoutTotalDisplay').textContent = fmtPrice;

  modal.show();
}

document.getElementById('checkoutForm').addEventListener('submit', function() {
    var btn = document.getElementById('btnSubmitCheckout');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
});
</script>
