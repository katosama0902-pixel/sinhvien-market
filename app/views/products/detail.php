<?php
/**
 * View: Chi tiết sản phẩm + Đấu giá ngược
 * Biến nhận: $product (đã join auction), $auctionPrice, $sellerRank, $csrf
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$user   = $_SESSION['user'] ?? null;

function fmtPrice(int $price): string {
    return number_format($price, 0, ',', '.') . 'đ';
}

$p = $product;
$conditionMap = [
    'new'      => ['🟢 Mới 100%',            'text-green-700 bg-green-100 border-green-200'],
    'like_new' => ['🔵 Như mới (90%+)',       'text-blue-700 bg-blue-100 border-blue-200'],
    'used'     => ['🟡 Đã qua sử dụng',       'text-yellow-700 bg-yellow-100 border-yellow-200'],
    'worn'     => ['🔴 Cũ & có dấu vết',      'text-red-700 bg-red-100 border-red-200'],
];
?>

<div class="container mx-auto px-4 py-8 max-w-6xl">
  <div class="flex flex-col lg:flex-row gap-8">

    <!-- ─── Ảnh sản phẩm ──────────────────────────────── -->
    <div class="lg:w-1/2 animate-[fadeInUp_0.5s_ease-out_0.1s_both]">
      <div class="bg-white dark:bg-dark-card rounded-3xl border border-light-border dark:border-dark-border overflow-hidden shadow-sm h-[420px]">
        <?php if ($p['image']): ?>
          <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($p['image'], ENT_QUOTES) ?>"
               alt="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>"
               class="w-full h-full object-cover">
        <?php else: ?>
          <div class="w-full h-full flex items-center justify-center bg-gray-50 dark:bg-dark-2">
            <i class="bi bi-image text-gray-300 dark:text-gray-600 text-[5rem] opacity-30"></i>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ─── Thông tin + Hành động ──────────────────────── -->
    <div class="lg:w-1/2 flex flex-col animate-[fadeInUp_0.5s_ease-out_0.2s_both]">

      <!-- Breadcrumb nhỏ -->
      <div class="mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2">
        <a href="<?= $appUrl ?>/products" class="text-gray-400 hover:text-primary transition-colors no-underline">Sản phẩm</a>
        <i class="bi bi-chevron-right text-[10px]"></i>
        <span class="text-gray-600 dark:text-gray-300"><?= htmlspecialchars($p['category_name'], ENT_QUOTES) ?></span>
      </div>

      <!-- Loại & Tình trạng -->
      <div class="flex flex-wrap gap-2 mb-3">
        <?php if ($p['type'] === 'auction'): ?>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold text-white bg-gradient-to-r from-red-500 to-orange-500 shadow-sm">
            <i class="bi bi-lightning-fill"></i>Đấu giá ngược
          </span>
        <?php elseif ($p['type'] === 'exchange'): ?>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold text-white bg-cyan-500 shadow-sm">
            🔄 Trao đổi
          </span>
        <?php endif; ?>
        
        <?php if (!empty($p['condition']) && isset($conditionMap[$p['condition']])): ?>
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border <?= $conditionMap[$p['condition']][1] ?>">
             <?= $conditionMap[$p['condition']][0] ?>
          </span>
        <?php endif; ?>
      </div>

      <!-- Tiêu đề -->
      <h1 class="text-2xl lg:text-3xl font-extrabold text-gray-800 dark:text-dark-text leading-tight mb-4">
        <?= htmlspecialchars($p['title'], ENT_QUOTES) ?>
      </h1>

      <!-- Người bán + Ngày -->
      <div class="flex flex-wrap items-center gap-4 mb-6 text-sm text-gray-500 border-b border-gray-100 dark:border-dark-border pb-6">
        <div class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300 font-semibold">
          <i class="bi bi-person-circle"></i><?= htmlspecialchars($p['seller_name'], ENT_QUOTES) ?>
          <?php if (!empty($p['seller_verified'])): ?>
            <span title="Sinh viên đã xác thực" class="text-green-500 ml-0.5">🛡️</span>
          <?php endif; ?>
        </div>
        <div class="flex items-center gap-1.5"><i class="bi bi-calendar3"></i><?= date('d/m/Y', strtotime($p['created_at'])) ?></div>
        <div class="flex items-center gap-1.5"><i class="bi bi-folder"></i><?= htmlspecialchars($p['category_name'], ENT_QUOTES) ?></div>
      </div>

      <?php if ($p['type'] === 'auction' && $auctionPrice): ?>
        <!-- ─── Khối đấu giá ngược ─────────────────────────────── -->
        <div class="rounded-3xl p-6 mb-6 border-2 transition-all duration-300 hover:-translate-y-1 shadow-sm" id="auctionCard"
             style="background:linear-gradient(135deg,rgba(239,68,68,0.05),rgba(249,115,22,0.05)); border-color:rgba(239,68,68,0.2)">

          <!-- Giá hiện tại -->
          <div class="text-center mb-5">
            <p class="text-sm font-bold text-red-500/70 mb-1 uppercase tracking-widest"><i class="bi bi-tag-fill mr-1"></i>Giá hiện tại</p>
            <div class="text-[2.5rem] font-black text-red-600 dark:text-red-500 font-mono leading-none tracking-tight mb-2 transition-colors duration-300" id="currentPriceDisplay">
              <?= fmtPrice($auctionPrice['current_price']) ?>
            </div>
            <?php if (!$auctionPrice['is_at_floor']): ?>
              <div class="text-sm font-bold text-red-500">
                <i class="bi bi-arrow-down-circle-fill mr-1 animate-[bounceDown_1s_ease-in-out_infinite_alternate]"></i>
                Giảm còn: <strong class="text-lg"><?= fmtPrice($auctionPrice['floor_price']) ?></strong>
              </div>
            <?php else: ?>
              <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-600">🔒 Đã chạm giá sàn</span>
            <?php endif; ?>
          </div>

          <!-- Countdown đến lần giảm tiếp theo -->
          <?php if (!$auctionPrice['is_at_floor']): ?>
            <div class="text-center mb-5">
              <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Giảm tiếp theo sau</p>
              <div class="text-2xl font-black text-gray-800 dark:text-white font-mono bg-white dark:bg-dark-card border border-light-border dark:border-dark-border px-4 py-2 rounded-xl inline-block mb-2 shadow-sm" id="countdownTimer">--:--</div>
              <p class="text-xs text-gray-400 font-semibold mb-0">Giảm <?= fmtPrice($auctionPrice['decrease_amount']) ?>/lần • Mỗi <?= $auctionPrice['step_minutes'] ?> phút</p>
            </div>
          <?php endif; ?>

          <!-- Thông số đấu giá -->
          <div class="grid grid-cols-2 gap-3 mb-6 text-center">
            <div class="bg-white/50 dark:bg-dark-card/50 rounded-xl p-3 border border-red-500/10">
              <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Giá khởi điểm</div>
              <div class="text-base font-extrabold text-gray-800 dark:text-gray-200"><?= fmtPrice($auctionPrice['start_price']) ?></div>
            </div>
            <div class="bg-white/50 dark:bg-dark-card/50 rounded-xl p-3 border border-red-500/10">
              <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Giá sàn</div>
              <div class="text-base font-extrabold text-red-500"><?= fmtPrice($auctionPrice['floor_price']) ?></div>
            </div>
          </div>

          <!-- Nút mua -->
          <?php if (!$user): ?>
            <a href="<?= $appUrl ?>/login" class="flex items-center justify-center w-full py-3.5 rounded-xl bg-red-500 text-white font-bold text-base hover:bg-red-600 transition-colors no-underline">
              <i class="bi bi-box-arrow-in-right mr-2"></i>Đăng nhập để mua
            </a>
          <?php elseif ($p['status'] === 'active' && $p['auction_status'] === 'active'): ?>
            <div class="flex flex-col gap-3">
                <button type="button" class="flex items-center justify-center w-full py-3.5 rounded-xl bg-gradient-to-r from-red-600 to-red-500 text-white font-bold text-base shadow-[0_8px_20px_rgba(239,68,68,0.3)] hover:-translate-y-1 transition-all border-0 cursor-pointer" id="btnBuyNow"
                        onclick="prepareCheckout(<?= $p['auction_id'] ?>, <?= $auctionPrice['current_price'] ?>, 'auction')">
                  <i class="bi bi-lightning-fill mr-2"></i>Mua ngay với giá
                  <span id="btnPrice" class="ml-1"><?= fmtPrice($auctionPrice['current_price']) ?></span>
                </button>
                
                <?php if ($user && (int)$p['user_id'] === (int)$user['id']): ?>
                <button type="button" class="flex items-center justify-center w-full py-2.5 rounded-xl border-2 border-amber-500 text-amber-600 dark:text-amber-500 font-bold text-sm hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors bg-transparent cursor-pointer"
                        onclick="confirmBump(<?= $p['id'] ?>, '<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>')">
                  <i class="bi bi-rocket-takeoff mr-2"></i>Đẩy bài ngay (50 xu)
                </button>
                <?php endif; ?>
            </div>
            <p class="text-center text-xs font-semibold text-gray-400 mt-3 mb-0">
              <i class="bi bi-shield-check mr-1"></i>Giao dịch được bảo vệ • Không thể hoàn tác
            </p>
          <?php else: ?>
            <button class="flex items-center justify-center w-full py-3.5 rounded-xl bg-gray-200 dark:bg-dark-2 text-gray-500 font-bold text-base cursor-not-allowed border-0">
              <i class="bi bi-bag-x mr-2"></i>Sản phẩm đã được mua
            </button>
          <?php endif; ?>
        </div>

      <?php elseif ($p['type'] === 'sale'): ?>
        <!-- ─── Giá bán thường ─────────────────────────────────── -->
        <div class="mb-6">
          <span class="text-4xl font-black text-primary font-mono"><?= fmtPrice((int)$p['price']) ?></span>
        </div>
        
        <?php if (!$user): ?>
            <a href="<?= $appUrl ?>/login" class="flex items-center justify-center w-full py-3.5 rounded-xl bg-primary text-white font-bold text-base hover:brightness-110 transition-colors no-underline mb-2">
              <i class="bi bi-box-arrow-in-right mr-2"></i>Đăng nhập để mua
            </a>
        <?php elseif ((int)$p['user_id'] === (int)$user['id']): ?>
            <div class="flex flex-col gap-3">
                <p class="text-center text-gray-400 font-semibold text-sm mb-0"><em>Đây là sản phẩm của bạn</em></p>
                <?php if ($p['status'] === 'active'): ?>
                <button type="button" class="flex items-center justify-center w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold text-base shadow-md hover:brightness-110 transition-all border-0 cursor-pointer" 
                        onclick="confirmBump(<?= $p['id'] ?>, '<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>')">
                  <i class="bi bi-rocket-takeoff mr-2"></i>Đẩy bài lên đầu (50 xu)
                </button>
                <?php endif; ?>
            </div>
        <?php elseif ($p['status'] === 'active'): ?>
          <button type="button" class="flex items-center justify-center w-full py-3.5 rounded-xl bg-primary text-white font-bold text-base shadow-[0_8px_20px_rgba(99,102,241,0.3)] hover:-translate-y-1 transition-all border-0 cursor-pointer mb-2" onclick="prepareCheckout(<?= $p['id'] ?>, <?= $p['price'] ?>, 'sale')">
            <i class="bi bi-cart-check mr-2"></i>Mua ngay
          </button>
          <p class="text-gray-400 font-semibold text-xs text-center mt-3">
            <i class="bi bi-shield-check mr-1"></i>Thanh toán an toàn đa phương thức
          </p>
        <?php endif; ?>
      <?php else: ?>
        <!-- ─── Trao đổi ────────────────────────────────────────── -->
        <div class="mb-6">
          <span class="inline-flex items-center px-4 py-2 rounded-xl bg-cyan-100 text-cyan-700 text-lg font-black">🔄 Trao đổi đồ</span>
        </div>
      <?php endif; ?>

      <!-- ─── Trạng thái nếu đã bán ────────────────────────────── -->
      <?php if ($p['status'] === 'sold'): ?>
        <div class="bg-gray-100 dark:bg-dark-2 border border-gray-200 dark:border-dark-border text-gray-600 dark:text-gray-400 font-bold px-4 py-3 rounded-xl mt-4 flex items-center gap-2">
          <i class="bi bi-bag-check text-lg"></i>Sản phẩm đã được mua • Không còn khả dụng
        </div>
      <?php endif; ?>

      <!-- ─── Nút Chia sẻ QR Code ────────────────────────────── -->
      <div class="mt-auto pt-6 border-t border-gray-100 dark:border-dark-border">
        <button onclick="openDetailModal('qrShareModal')" class="flex items-center justify-center w-full py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-bold text-sm bg-transparent hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors cursor-pointer">
            <i class="bi bi-qr-code-scan mr-2"></i> Chia sẻ Mã QR Sản Phẩm
        </button>
      </div>

    </div>
  </div>

  <!-- ─── Phần Dưới (Mô tả, Map, Thông tin người bán) ──────────────── -->
  <div class="flex flex-col lg:flex-row gap-8 mt-8 animate-[fadeInUp_0.5s_ease-out_0.3s_both]">
    <!-- Cột Trái -->
    <div class="lg:w-2/3 flex flex-col gap-6">
      
      <!-- Mô tả -->
      <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md">
        <h5 class="text-base font-extrabold text-gray-800 dark:text-dark-text mb-4 uppercase tracking-wider"><i class="bi bi-card-text text-primary mr-2"></i>Mô tả sản phẩm</h5>
        <div class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed whitespace-pre-wrap font-medium">
          <?= nl2br(htmlspecialchars($p['description'], ENT_QUOTES)) ?>
        </div>
      </div>

      <!-- Bản Đồ -->
      <?php if (!empty($p['seller_address'])): ?>
      <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-md">
        <h5 class="text-base font-extrabold text-gray-800 dark:text-dark-text mb-2 uppercase tracking-wider"><i class="bi bi-geo-alt-fill text-red-500 mr-2"></i>Khu vực giao dịch</h5>
        <p class="text-xs font-semibold text-gray-400 mb-4">📍 Vị trí tương đối dựa trên ký túc xá khai báo của người bán: <strong class="text-gray-600 dark:text-gray-300"><?= htmlspecialchars($p['seller_address'], ENT_QUOTES) ?></strong></p>
        <div id="osm-map" class="w-full h-80 rounded-xl border border-light-border dark:border-dark-border z-[1]"></div>
      </div>
      <?php endif; ?>

    </div>

    <!-- Cột Phải -->
    <div class="lg:w-1/3">
      <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border p-6 shadow-sm sticky top-24">
        <div class="flex justify-between items-center mb-5">
          <h5 class="text-base font-extrabold text-gray-800 dark:text-dark-text m-0 uppercase tracking-wider">Người bán</h5>
          <?php if (isset($user) && $user['id'] !== $p['user_id']): ?>
          <button onclick="openDetailModal('reportModal')" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-red-200 text-red-500 hover:bg-red-50 font-bold text-xs bg-transparent cursor-pointer transition-colors">
            <i class="bi bi-flag-fill mr-1"></i>Tố cáo
          </button>
          <?php endif; ?>
        </div>
        
        <div class="flex items-center gap-4 mb-6">
          <a href="<?= $appUrl ?>/users/profile?id=<?= $p['user_id'] ?>" class="flex items-center gap-4 w-full no-underline group">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-primary to-purple-500 text-white flex items-center justify-center text-xl font-black shadow-md flex-shrink-0">
              <?= mb_strtoupper(mb_substr($p['seller_name'], 0, 1)) ?>
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-extrabold text-gray-800 dark:text-dark-text text-base group-hover:text-primary transition-colors truncate"><?= htmlspecialchars($p['seller_name'], ENT_QUOTES) ?></div>
              <div class="text-xs text-gray-500 mt-1 flex items-center gap-1 flex-wrap">
                Sinh viên / 
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-<?= $sellerRank['color'] ?? 'secondary' ?> text-white font-bold text-[10px]">
                  <i class="bi bi-<?= $sellerRank['icon'] ?? 'person' ?> mr-1"></i><?= $sellerRank['name'] ?? 'Thành viên' ?>
                </span>
              </div>
            </div>
            <div class="text-gray-300 group-hover:text-primary transition-colors">
              <i class="bi bi-chevron-right"></i>
            </div>
          </a>
        </div>
        
        <!-- Nút Liên hệ người bán & Nút Trả Giá -->
        <?php if ($user && (int)$p['user_id'] !== (int)$user['id']): ?>
          <div class="flex gap-2 mb-3">
            <form action="<?= $appUrl ?>/chat/start" method="POST" class="flex-1 m-0">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <button type="submit" class="w-full py-2.5 rounded-xl bg-primary text-white font-bold text-sm hover:brightness-110 shadow-md shadow-primary/20 transition-all border-0 cursor-pointer flex items-center justify-center gap-2">
                <i class="bi bi-chat-dots-fill"></i> Nhanh
              </button>
            </form>
            <?php if ($p['type'] === 'sale' && $p['status'] === 'active'): ?>
            <button onclick="openDetailModal('offerModal')" type="button" class="flex-1 py-2.5 rounded-xl bg-amber-100 text-amber-700 font-bold text-sm hover:bg-amber-200 transition-all border-0 cursor-pointer flex items-center justify-center gap-2">
              <i class="bi bi-tag-fill"></i> Trả giá
            </button>
            <?php endif; ?>
          </div>
          <form action="<?= $appUrl ?>/wishlist/toggle" method="POST" class="m-0">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <button type="submit" class="w-full py-2.5 rounded-xl border-2 border-red-100 text-red-500 font-bold text-sm bg-transparent hover:bg-red-50 transition-colors border-0 cursor-pointer flex items-center justify-center gap-2">
              <i class="bi bi-heart"></i> Thêm vào Yêu thích
            </button>
          </form>
        <?php elseif (!$user): ?>
          <a href="<?= $appUrl ?>/login" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl border-2 border-primary text-primary font-bold text-sm hover:bg-primary hover:text-white transition-colors no-underline mt-4">
            <i class="bi bi-box-arrow-in-right"></i> Đăng nhập để liên hệ
          </a>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<!-- ─── Modals (Vanilla JS) ───────────────────────────────── -->
<!-- Modal Thanh Toán -->
<div class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200" id="checkoutModal"
     onclick="if(event.target===this) closeDetailModal('checkoutModal')">
  <div class="modal-inner bg-white dark:bg-dark-card border-0 shadow-2xl rounded-3xl overflow-hidden w-full max-w-[520px] transform scale-95 opacity-0 transition-all duration-300 m-4 max-h-[90vh] overflow-y-auto">
    <div class="px-6 py-4 bg-gray-50 dark:bg-dark-2 border-b border-light-border dark:border-dark-border flex justify-between items-center sticky top-0 z-10">
      <h5 class="font-extrabold text-gray-800 dark:text-dark-text m-0 text-lg flex items-center">
        <i class="bi bi-cart-check-fill text-primary mr-2"></i>Thanh Toán Đơn Hàng
      </h5>
      <button type="button" onclick="closeDetailModal('checkoutModal')" class="text-gray-400 hover:text-gray-600 bg-transparent border-0 cursor-pointer text-xl leading-none">&times;</button>
    </div>
      <form action="<?= $appUrl ?>/transactions/checkout" method="POST" id="checkoutForm">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
        <input type="hidden" name="target_id" id="checkoutTargetId" value="">
        <input type="hidden" name="type" id="checkoutType" value="">
        <input type="hidden" name="price" id="checkoutPrice" value="">

        <div class="p-6">
          <div class="flex justify-between items-center bg-gray-50 dark:bg-dark-2 p-4 rounded-2xl border border-light-border dark:border-dark-border mb-6">
            <span class="text-sm font-bold text-gray-500">Tổng thanh toán:</span>
            <span class="text-xl font-black text-red-500 font-mono" id="checkoutTotalDisplay">0đ</span>
          </div>

          <h6 class="font-bold text-sm text-gray-800 dark:text-dark-text uppercase tracking-wider mb-3">Thông tin giao nhận</h6>
          <div class="mb-6">
            <label class="block text-xs font-semibold text-gray-500 mb-2">Địa chỉ nhận hàng (Ký túc xá, Lớp học...)</label>
            <textarea id="shipping_address" name="shipping_address" class="w-full px-4 py-3 rounded-xl border border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all text-sm resize-none" rows="2" placeholder="VD: Phòng 204 Ký túc xá khu A..." required></textarea>
          </div>

          <h6 class="font-bold text-sm text-gray-800 dark:text-dark-text uppercase tracking-wider mb-3">Phương thức thanh toán</h6>
          <div class="flex flex-col gap-3">
            <label class="flex items-center p-4 rounded-2xl border border-light-border dark:border-dark-border cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors">
              <input type="radio" name="payment_method" value="cod" checked class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary mr-4">
              <div class="flex-1">
                <div class="text-sm font-bold text-gray-800 dark:text-dark-text">Thanh toán khi nhận hàng (COD)</div>
              </div>
              <i class="bi bi-cash-stack text-2xl text-green-500"></i>
            </label>
            <label class="flex items-center p-4 rounded-2xl border border-light-border dark:border-dark-border cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors">
              <input type="radio" name="payment_method" value="banking" class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary mr-4">
              <div class="flex-1">
                <div class="text-sm font-bold text-gray-800 dark:text-dark-text">Chuyển khoản Ngân hàng (VietQR)</div>
              </div>
              <i class="bi bi-bank text-2xl text-primary"></i>
            </label>
            <label class="flex items-center p-4 rounded-2xl border border-light-border dark:border-dark-border cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors">
              <input type="radio" name="payment_method" value="zalopay" class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary mr-4">
              <div class="flex-1">
                <div class="text-sm font-bold text-gray-800 dark:text-dark-text">Chuyển qua ZaloPay</div>
              </div>
              <i class="bi bi-wallet2 text-2xl text-[#0068ff]"></i>
            </label>
            <label class="flex items-center p-4 rounded-2xl border border-light-border dark:border-dark-border cursor-pointer hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors">
              <input type="radio" name="payment_method" value="momo" class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary mr-4">
              <div class="flex-1">
                <div class="text-sm font-bold text-gray-800 dark:text-dark-text">Chuyển qua MoMo</div>
              </div>
              <i class="bi bi-phone text-2xl text-[#a50064]"></i>
            </label>
          </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 dark:bg-dark-2 border-t border-light-border dark:border-dark-border flex justify-end gap-3">
          <button type="button" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-gray-600 dark:text-gray-300 font-bold text-sm hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors border-0 cursor-pointer" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary text-white font-bold text-sm hover:brightness-110 shadow-md shadow-primary/20 transition-all border-0 cursor-pointer flex items-center gap-2" id="btnSubmitCheckout">
            Xác Nhận Đặt Hàng <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      </form>
  </div>
</div>

<!-- Modal QR Code -->
<div class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200" id="qrShareModal"
     onclick="if(event.target===this) closeDetailModal('qrShareModal')">
  <div class="modal-inner bg-white dark:bg-dark-card border-0 shadow-2xl rounded-3xl overflow-hidden w-full max-w-[360px] transform scale-95 opacity-0 transition-all duration-300 m-4">
    <div class="flex justify-end p-3">
      <button type="button" onclick="closeDetailModal('qrShareModal')" class="text-gray-400 hover:text-gray-600 bg-transparent border-0 cursor-pointer text-2xl leading-none">&times;</button>
    </div>
      <div class="px-6 pb-6 text-center -mt-4">
        <h5 class="font-extrabold text-gray-800 dark:text-dark-text mb-4 flex items-center justify-center gap-2">
            <i class="bi bi-qr-code text-primary"></i> Quét mã để xem
        </h5>
        <div class="bg-white p-3 rounded-2xl shadow-sm inline-block border border-gray-100">
            <img id="qrCodeImage" src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?= urlencode($appUrl . '/products/show?id=' . $p['id']) ?>" alt="QR Code" class="w-[200px] h-[200px]" crossorigin="anonymous">
        </div>
        <p class="text-xs font-semibold text-gray-500 mt-4 mb-5 truncate"><?= htmlspecialchars($p['title'], ENT_QUOTES) ?></p>
        
        <button class="w-full py-2.5 rounded-xl bg-primary text-white font-bold text-sm hover:brightness-110 shadow-md shadow-primary/20 transition-all border-0 cursor-pointer flex items-center justify-center gap-2 mb-4" onclick="downloadQRCode()">
            <i class="bi bi-download"></i>Tải QR xuống
        </button>

        <div class="relative">
          <div class="text-[11px] font-bold text-gray-400 mb-2 uppercase tracking-wider">Hoặc sao chép đường link</div>
          <div class="flex rounded-xl overflow-hidden border border-light-border dark:border-dark-border">
            <input type="text" id="productShareUrl" value="<?= htmlspecialchars($appUrl . '/products/show?id=' . $p['id'], ENT_QUOTES) ?>" readonly 
                   class="flex-1 bg-gray-50 dark:bg-dark-2 text-xs text-center text-gray-600 dark:text-gray-300 border-0 outline-none cursor-pointer py-2">
            <button class="bg-gray-100 dark:bg-dark-border border-0 px-4 text-gray-600 dark:text-gray-300 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors" id="copyLinkBtn" onclick="copyProductLink()">
              <i class="bi bi-clipboard" id="copyLinkIcon"></i>
            </button>
          </div>
          <div id="copySuccessMsg" class="absolute -bottom-6 left-0 right-0 text-[10px] font-bold text-green-500 hidden">
            <i class="bi bi-check-circle-fill mr-1"></i>Đã sao chép!
          </div>
        </div>
      </div>
  </div>
</div>

<!-- Modal Report -->
<div class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200" id="reportModal"
     onclick="if(event.target===this) closeDetailModal('reportModal')">
  <div class="modal-inner bg-white dark:bg-dark-card border-0 shadow-2xl rounded-3xl overflow-hidden w-full max-w-[500px] transform scale-95 opacity-0 transition-all duration-300 m-4">
    <div class="px-6 py-4 bg-red-500 text-white flex justify-between items-center">
      <h5 class="font-extrabold m-0 text-lg flex items-center"><i class="bi bi-shield-exclamation mr-2"></i>Báo cáo vi phạm</h5>
      <button type="button" onclick="closeDetailModal('reportModal')" class="text-white/80 hover:text-white bg-transparent border-0 cursor-pointer text-2xl leading-none">&times;</button>
    </div>
      <form action="<?= $appUrl ?>/reports/store" method="POST">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
        <input type="hidden" name="target_user_id" value="<?= $p['user_id'] ?>">
        
        <div class="p-6">
          <p class="text-xs font-semibold text-gray-500 mb-5 leading-relaxed">Chúng tôi sát cánh cùng bạn để xây dựng cộng đồng mua bán an toàn. Vui lòng cung cấp chi tiết vi phạm để được xử lý nhanh nhất.</p>
          
          <div class="mb-4">
            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Lý do báo cáo <span class="text-red-500">*</span></label>
            <select name="reason" class="w-full px-4 py-2.5 rounded-xl border border-light-border dark:border-dark-border bg-gray-50 dark:bg-dark-2 outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 text-sm font-medium" required>
              <option value="">-- Chọn lý do --</option>
              <option value="Hàng giả / Trái pháp luật">Hàng giả / Cấm buôn bán</option>
              <option value="Lừa đảo">Người bán có dấu hiệu lừa đảo</option>
              <option value="Phản cảm">Hình ảnh / Nội dung phản cảm</option>
              <option value="Ngôn từ đe dọa">Ngôn từ đe dọa / Quấy rối</option>
              <option value="Khác">Lý do khác</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Chi tiết vi phạm <span class="text-red-500">*</span></label>
            <textarea name="description" class="w-full px-4 py-3 rounded-xl border border-light-border dark:border-dark-border bg-gray-50 dark:bg-dark-2 outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 text-sm resize-none" rows="4" placeholder="Mô tả cụ thể hành vi vi phạm..." required></textarea>
          </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 dark:bg-dark-2 border-t border-light-border dark:border-dark-border flex justify-end gap-3">
        <button type="button" onclick="closeDetailModal('reportModal')" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-gray-600 dark:text-gray-300 font-bold text-sm hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors border-0 cursor-pointer">Hủy</button>
          <button type="submit" class="px-6 py-2.5 rounded-xl bg-red-500 text-white font-bold text-sm hover:bg-red-600 shadow-md shadow-red-500/20 transition-all border-0 cursor-pointer flex items-center gap-2">
            <i class="bi bi-send-fill"></i>Gửi báo cáo
          </button>
        </div>
      </form>
  </div>
</div>

<!-- Modal Offer -->
<div class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200" id="offerModal"
     onclick="if(event.target===this) closeDetailModal('offerModal')">
  <div class="modal-inner bg-white dark:bg-dark-card border-0 shadow-2xl rounded-3xl overflow-hidden w-full max-w-[500px] transform scale-95 opacity-0 transition-all duration-300 m-4">
    <div class="px-6 py-4 bg-gray-50 dark:bg-dark-2 border-b border-light-border dark:border-dark-border flex justify-between items-center">
      <h5 class="font-extrabold text-gray-800 dark:text-dark-text m-0 text-lg flex items-center"><i class="bi bi-tag-fill text-amber-500 mr-2"></i> Trả giá sản phẩm</h5>
      <button type="button" onclick="closeDetailModal('offerModal')" class="text-gray-400 hover:text-gray-600 bg-transparent border-0 cursor-pointer text-2xl leading-none">&times;</button>
    </div>
      <div class="p-6">
          <div class="flex items-center gap-4 mb-6 bg-gray-50 dark:bg-dark-2 p-3 rounded-2xl border border-light-border dark:border-dark-border">
              <img src="<?= $p['image'] ? ($appUrl . '/public/uploads/' . $p['image']) : ($appUrl . '/public/assets/img/og-fallback.png') ?>" class="w-16 h-16 object-cover rounded-xl shadow-sm flex-shrink-0" alt="Product">
              <div class="min-w-0">
                  <div class="font-bold text-sm text-gray-800 dark:text-dark-text truncate leading-tight"><?= htmlspecialchars($p['title'], ENT_QUOTES) ?></div>
                  <div class="text-primary font-black text-lg mt-1 font-mono"><?= number_format((int)$p['price']) ?> đ</div>
              </div>
          </div>
          <form id="formOffer">
              <input type="hidden" id="offer_productId" value="<?= $p['id'] ?>">
              <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Mức giá bạn muốn mua (VNĐ)</label>
              <div class="flex items-center rounded-2xl border-2 border-amber-200 overflow-hidden mb-4 focus-within:ring-4 focus-within:ring-amber-100 transition-all">
                  <span class="px-4 bg-amber-50 text-amber-600 font-black text-lg border-r border-amber-200">₫</span>
                  <input type="number" id="offerPriceInput" value="<?= round((int)$p['price'] * 0.8) ?>" step="1000" min="1000" class="flex-1 px-4 py-3 border-0 outline-none font-black text-xl text-gray-800 dark:text-dark-text bg-white dark:bg-dark-card">
              </div>
              <p class="text-[11px] font-semibold text-gray-400 mb-6 flex items-center gap-1.5"><i class="bi bi-info-circle text-amber-500"></i> Gợi ý: Thương lượng ở mức giá 80% - 90% sẽ dễ thành công!</p>
              
              <button type="button" class="w-full py-3.5 rounded-xl bg-amber-100 text-amber-700 font-bold text-base hover:bg-amber-200 transition-colors border-0 cursor-pointer flex items-center justify-center gap-2" onclick="submitOffer(this)">
                <i class="bi bi-send-fill"></i>Gửi đề nghị ngay
              </button>
          </form>
      </div>
    </div>
  </div>
</div>

<script>
// Scripts cho Đấu giá ngược
<?php if ($p['type'] === 'auction' && isset($p['auction_id']) && $p['auction_status'] === 'active'): ?>
(function() {
  var appUrl    = '<?= $appUrl ?>';
  var auctionId = <?= $p['auction_id'] ?>;
  var interval  = null;
  var cdInterval = null;
  var nextDropIn = <?= $auctionPrice['next_drop_in_seconds'] ?? 0 ?>;

  function startCountdown(seconds) {
    clearInterval(cdInterval);
    var el = document.getElementById('countdownTimer');
    if (!el) return;
    function renderTime() {
      if (seconds <= 0) { clearInterval(cdInterval); fetchPrice(); return; }
      var m = Math.floor(seconds / 60).toString().padStart(2, '0');
      var s = (seconds % 60).toString().padStart(2, '0');
      el.textContent = m + ':' + s;
      if (seconds < 10) el.classList.add('text-red-500'); else el.classList.remove('text-red-500');
    }
    renderTime();
    if (seconds > 0) { cdInterval = setInterval(function() { seconds--; renderTime(); }, 1000); }
  }

  function fetchPrice() {
    fetch(appUrl + '/api/auction/price?id=' + auctionId)
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.status !== 'active') {
          clearInterval(interval);
          document.getElementById('currentPriceDisplay').textContent = 'Đã kết thúc';
          var btn = document.getElementById('btnBuyNow');
          if (btn) { btn.disabled = true; btn.innerHTML = '<i class="bi bi-bag-x mr-2"></i>Sản phẩm đã được mua'; btn.className = 'flex items-center justify-center w-full py-3.5 rounded-xl bg-gray-200 dark:bg-dark-2 text-gray-500 font-bold text-base cursor-not-allowed border-0'; }
          return;
        }
        var fmt = data.formatted_price;
        var priceEl = document.getElementById('currentPriceDisplay');
        if (priceEl) { priceEl.textContent = fmt; priceEl.classList.add('scale-110'); setTimeout(function() { priceEl.classList.remove('scale-110'); }, 300); }
        var btnPrice = document.getElementById('btnPrice');
        if (btnPrice) btnPrice.textContent = fmt;
        var buyPrice = document.getElementById('checkoutPrice');
        if (buyPrice) buyPrice.value = data.current_price;
        
        var el = document.getElementById('countdownTimer');
        if (data.is_at_floor) {
          clearInterval(cdInterval);
          if (el) el.innerHTML = '<span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-600 border-0">🔒 Đã chạm giá sàn</span>';
        } else if (data.next_drop_in_seconds) { startCountdown(data.next_drop_in_seconds); }
      }).catch(function() {});
  }
  startCountdown(nextDropIn);
  interval = setInterval(fetchPrice, 8000);
})();
<?php endif; ?>

// Các script dùng chung
function prepareCheckout(targetId, price, type) {
  var modalEl = document.getElementById('checkoutModal');
  if (!modalEl) { alert("Lỗi tải modal"); return; }
  
  document.getElementById('checkoutTargetId').value = targetId;
  document.getElementById('checkoutType').value     = type;
  document.getElementById('checkoutPrice').value    = price;
  document.getElementById('checkoutTotalDisplay').textContent = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
  
  openDetailModal('checkoutModal');
}

document.getElementById('checkoutForm')?.addEventListener('submit', function() {
    var btn = document.getElementById('btnSubmitCheckout');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Đang xử lý...';
});

function downloadQRCode() {
    const img = document.getElementById('qrCodeImage');
    
    // Tải ảnh qua tab mới để tránh lỗi CORS từ api.qrserver.com
    const a = document.createElement('a');
    a.href = img.src;
    a.download = 'SVMarket_QR_<?= $p['id'] ?>.png';
    a.target = '_blank';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

function copyProductLink() {
    const input = document.getElementById('productShareUrl');
    const icon  = document.getElementById('copyLinkIcon');
    const msg   = document.getElementById('copySuccessMsg');
    navigator.clipboard.writeText(input.value).then(() => {
        icon.className = 'bi bi-clipboard-check-fill text-green-500';
        msg.classList.remove('hidden');
        setTimeout(() => { icon.className = 'bi bi-clipboard'; msg.classList.add('hidden'); }, 2500);
    }).catch(() => {
        input.select(); document.execCommand('copy');
        msg.classList.remove('hidden'); setTimeout(() => { msg.classList.add('hidden'); }, 2500);
    });
}

async function submitOffer(btn) {
    const price = document.getElementById('offerPriceInput').value;
    const productId = document.getElementById('offer_productId').value;
    if(!price || price <= 0) { alert("Vui lòng nhập giá trị lớn hơn 0"); return; }
    btn.innerHTML = `<svg class="animate-spin h-5 w-5 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Đang xử lý...`;
    btn.disabled = true;
    try {
        const form = document.createElement('form'); form.method = 'POST'; form.action = '<?= $appUrl ?>/chat/start';
        form.appendChild(Object.assign(document.createElement('input'), {type:'hidden', name:'_csrf', value:'<?= htmlspecialchars($csrf, ENT_QUOTES) ?>'}));
        form.appendChild(Object.assign(document.createElement('input'), {type:'hidden', name:'product_id', value: productId}));
        form.appendChild(Object.assign(document.createElement('input'), {type:'hidden', name:'offer_price', value: price}));
        document.body.appendChild(form); form.submit();
    } catch(e) { console.error(e); }
}
</script>

<!-- Leaflet Map -->
<?php if (!empty($p['seller_address'])): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function() {
    const DEFAULT_LAT = 10.8700, DEFAULT_LNG = 106.8030;
    const addressLabel = <?= json_encode($p['seller_address']) ?>;
    const searchQuery = <?= json_encode($p['seller_address'] . ' Làng Đại Học Quốc Gia TP.HCM') ?>;

    const map = L.map('osm-map', { center: [DEFAULT_LAT, DEFAULT_LNG], zoom: 15, zoomControl: true, scrollWheelZoom: false });
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors', maxZoom: 19 }).addTo(map);

    const pinIcon = L.divIcon({
        className: '',
        html: `<div style="width:36px;height:36px;background:#ef4444;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid #fff;box-shadow:0 4px 12px rgba(239,68,68,0.45);display:flex;align-items:center;justify-content:center;"><span style="transform:rotate(45deg);font-size:14px;line-height:1;">📍</span></div>`,
        iconSize: [36, 36], iconAnchor: [18, 36], popupAnchor: [0, -36]
    });

    function renderMap(lat, lng, isExact) {
        map.setView([lat, lng], 15);
        const marker = L.marker([lat, lng], { icon: pinIcon }).addTo(map);
        let popupHtml = `<div style="font-family:inherit;min-width:180px;padding:4px 2px">
            <div style="color:#ef4444;font-weight:800;margin-bottom:4px;font-size:13px">📍 Điểm giao dịch dự kiến</div>
            <div style="color:#6b7280;font-size:12px;line-height:1.5;font-weight:600">${addressLabel}</div>
            ${!isExact ? '<div style="color:#9ca3af;font-size:11px;margin-top:6px;font-weight:600">⚠️ Vị trí mang tính tương đối</div>' : ''}
        </div>`;
        marker.bindPopup(popupHtml).openPopup();
        L.circle([lat, lng], { radius: 200, color: '#f59e0b', fillColor: '#f59e0b', fillOpacity: 0.15, weight: 1.5 }).addTo(map);
        setTimeout(() => { map.invalidateSize(); }, 500);
    }

    const knownLocations = [
        { regex: /KTX\s*Khu\s*B|Ký\s*túc\s*xá\s*Khu\s*B/i, lat: 10.8821, lng: 106.7820 },
        { regex: /KTX\s*Khu\s*A|Ký\s*túc\s*xá\s*Khu\s*A/i, lat: 10.8785, lng: 106.8066 },
        { regex: /Bách\s*Khoa|HCMUT/i, lat: 10.8806, lng: 106.8053 },
        { regex: /Tự\s*Nhiên|KHTN|HCMUS/i, lat: 10.8761, lng: 106.7974 },
        { regex: /Quốc\s*Tế|IU/i, lat: 10.8800, lng: 106.7979 },
        { regex: /Khoa\s*học\s*xã\s*hội|KHXH/i, lat: 10.8715, lng: 106.8020 },
        { regex: /Kinh\s*tế\s*luật|UEL/i, lat: 10.8724, lng: 106.7765 },
        { regex: /Nông\s*Lâm/i, lat: 10.8696, lng: 106.7960 },
        { regex: /Công\s*nghệ\s*Thông\s*tin|UIT/i, lat: 10.8700, lng: 106.8030 }
    ];

    let foundExact = false;
    for (let loc of knownLocations) { if (loc.regex.test(addressLabel)) { renderMap(loc.lat, loc.lng, true); foundExact = true; break; } }
    if (!foundExact) {
        fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(searchQuery), { headers: { 'Accept-Language': 'vi' } })
        .then(r => r.json())
        .then(data => { if (data && data.length > 0) renderMap(parseFloat(data[0].lat), parseFloat(data[0].lon), false); else renderMap(DEFAULT_LAT, DEFAULT_LNG, false); })
        .catch(() => { renderMap(DEFAULT_LAT, DEFAULT_LNG, false); });
    }
})();
</script>
<?php endif ?>

<script>
// ─── Vanilla JS Modal Helpers (replaces Bootstrap Modal) ───────────────────
function openDetailModal(id) {
  const modal = document.getElementById(id);
  const inner = modal.querySelector('.modal-inner');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  void modal.offsetWidth;
  modal.classList.remove('opacity-0');
  if (inner) inner.classList.remove('scale-95', 'opacity-0');
  document.body.style.overflow = 'hidden';
}
function closeDetailModal(id) {
  const modal = document.getElementById(id);
  const inner = modal.querySelector('.modal-inner');
  modal.classList.add('opacity-0');
  if (inner) inner.classList.add('scale-95', 'opacity-0');
  setTimeout(() => {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
  }, 300);
}
</script>
