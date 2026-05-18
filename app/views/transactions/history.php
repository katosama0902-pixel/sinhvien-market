<?php
/**
 * View: Lịch sử giao dịch
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$user   = $_SESSION['user'];

$typeLabel = [
  'auction' => ['⚡ Đấu giá',    'bg-red-100 text-red-600'],
  'direct'  => ['💰 Bán thường', 'bg-indigo-100 text-indigo-700'],
];

$orderStatusMap = [
  'pending'   => ['Chờ xác nhận',   'bg-gray-100 text-gray-500 border-gray-200'],
  'shipping'  => ['Đang giao hàng', 'bg-cyan-100 text-cyan-700 border-cyan-200'],
  'delivered' => ['Đã giao đến nơi','bg-indigo-100 text-indigo-700 border-indigo-200'],
  'received'  => ['Hoàn tất',       'bg-green-100 text-green-700 border-green-200'],
  'completed' => ['Hoàn tất',       'bg-green-100 text-green-700 border-green-200'],
  'cancelled' => ['Đã hủy',         'bg-red-50 text-red-500 border-red-200'],
];

$paymentIcons = [
  'cod'     => ['bi-cash',        'text-green-500', 'COD'],
  'banking' => ['bi-bank',        'text-primary',   'Chuyển khoản'],
  'zalopay' => ['bi-wallet2',     'text-cyan-500',  'ZaloPay'],
  'vnpay'   => ['bi-credit-card', 'text-red-500',   'VNPay'],
  'momo'    => ['bi-phone',       'text-green-500', 'MoMo'],
];
?>

<div class="container mx-auto px-4 py-8">
  <h1 class="text-2xl font-extrabold text-gray-800 dark:text-dark-text flex items-center gap-2 mb-6">
    <i class="bi bi-receipt text-primary"></i>Lịch sử giao dịch
  </h1>

  <?php if (empty($transactions)): ?>
    <div class="text-center py-20">
      <i class="bi bi-receipt text-6xl text-gray-200 dark:text-gray-600 block mb-4"></i>
      <div class="text-lg font-bold text-gray-400 mb-1">Chưa có giao dịch nào</div>
      <p class="text-sm text-gray-400 mb-4">Giao dịch sẽ xuất hiện ở đây sau khi bạn mua hoặc bán thành công.</p>
      <a href="<?= $appUrl ?>/products"
         class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-primary text-white font-bold text-sm no-underline hover:brightness-110">
        <i class="bi bi-bag"></i>Xem sản phẩm
      </a>
    </div>

  <?php else:
    $totalBuy  = array_filter($transactions, fn($t) => (int)$t['buyer_id']  === (int)$user['id']);
    $totalSell = array_filter($transactions, fn($t) => (int)$t['seller_id'] === (int)$user['id']);
    $sumSell   = array_reduce($totalSell, fn($sum,$t) => $sum + ($t['order_status'] === 'delivered' ? $t['amount'] : 0), 0);
  ?>

    <!-- Summary stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
      <?php
        $stats = [
          ['Tổng giao dịch', count($transactions), 'text-gray-800 dark:text-dark-text'],
          ['Đã mua',         count($totalBuy),     'text-red-500'],
          ['Đã bán',         count($totalSell),    'text-green-500'],
          ['Doanh thu bán',  number_format($sumSell, 0, ',', '.') . 'đ', 'text-green-500'],
        ];
        foreach ($stats as [$label, $val, $cls]):
      ?>
        <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border p-4 text-center">
          <div class="text-xs text-gray-400 mb-1"><?= $label ?></div>
          <div class="font-extrabold text-xl <?= $cls ?>"><?= $val ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto rounded-2xl border border-light-border dark:border-dark-border">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-dark-2 border-b border-light-border dark:border-dark-border">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider w-1/4">Sản phẩm</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider w-1/6">Vai trò</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider w-1/5">Trạng thái & TT</th>
            <th class="px-4 py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider w-1/6">Số tiền</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Ngày & Hành động</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
          <?php foreach ($transactions as $t):
            $isBuyer = (int)$t['buyer_id'] === (int)$user['id'];
            [$typeText, $typeCls] = $typeLabel[$t['type']] ?? ['?', 'bg-gray-100 text-gray-500'];
            $os = $t['order_status'] ?? 'pending';
            [$osText, $osCls] = $orderStatusMap[$os] ?? ['?', 'bg-gray-100 text-gray-400 border-gray-200'];
            [$payIcon, $payColor, $payLabel] = $paymentIcons[$t['payment_method']] ?? ['bi-cash', 'text-gray-400', 'N/A'];
          ?>
            <tr class="bg-white dark:bg-dark-card hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors">

              <!-- Sản phẩm -->
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <div class="w-11 h-11 rounded-xl overflow-hidden flex-shrink-0 bg-gray-100 dark:bg-dark-2">
                    <?php if ($t['product_image']): ?>
                      <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($t['product_image'], ENT_QUOTES) ?>"
                           class="w-full h-full object-cover">
                    <?php else: ?>
                      <div class="w-full h-full flex items-center justify-center">
                        <i class="bi bi-image text-gray-300 dark:text-gray-600"></i>
                      </div>
                    <?php endif; ?>
                  </div>
                  <span class="font-semibold text-gray-800 dark:text-dark-text max-w-[180px] truncate block">
                    <?= htmlspecialchars($t['product_title'], ENT_QUOTES) ?>
                  </span>
                </div>
              </td>

              <!-- Vai trò -->
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold mb-1 <?= $typeCls ?>">
                  <?= $typeText ?>
                </span><br>
                <?php if ($isBuyer): ?>
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">
                    <i class="bi bi-bag"></i>Người mua
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                    <i class="bi bi-shop"></i>Người bán
                  </span>
                <?php endif; ?>
                <div class="text-xs text-gray-400 mt-1">
                  Đối tác: <?= htmlspecialchars($isBuyer ? $t['seller_name'] : $t['buyer_name'], ENT_QUOTES) ?>
                </div>
              </td>

              <!-- Trạng thái -->
              <td class="px-4 py-3">
                <div class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">
                  <i class="bi <?= $payIcon ?> <?= $payColor ?> mr-1"></i><?= $payLabel ?>
                  <?php if ($t['payment_status'] === 'paid'): ?>
                    | <span class="text-green-500"><i class="bi bi-check-circle mr-0.5"></i>Đã TT</span>
                  <?php else: ?>
                    | <span class="text-yellow-500"><i class="bi bi-clock mr-0.5"></i>Chờ TT</span>
                  <?php endif; ?>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold border <?= $osCls ?>">
                  <i class="bi bi-box-seam"></i><?= $osText ?>
                </span>
              </td>

              <!-- Số tiền -->
              <td class="px-4 py-3 text-right font-extrabold whitespace-nowrap">
                <?php if ($isBuyer): ?>
                  <span class="text-red-500">−<?= number_format($t['amount'], 0, ',', '.') ?>đ</span>
                <?php else: ?>
                  <span class="text-green-500">+<?= number_format($t['amount'], 0, ',', '.') ?>đ</span>
                <?php endif; ?>
              </td>

              <!-- Ngày & Hành động -->
              <td class="px-4 py-3">
                <div class="text-xs text-gray-400 mb-0.5">
                  <i class="bi bi-clock mr-1"></i><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                </div>
                <div class="text-xs text-gray-400 truncate max-w-[200px] mb-2"
                     title="<?= htmlspecialchars($t['shipping_address'] ?? 'Không có', ENT_QUOTES) ?>">
                  <i class="bi bi-geo-alt mr-1"></i><?= htmlspecialchars($t['shipping_address'] ?? 'Không có địa chỉ', ENT_QUOTES) ?>
                </div>

                <!-- Thanh toán ngân hàng (VietQR) -->
                <?php if ($t['payment_method'] === 'banking' && !empty($t['payment_proof'])): ?>
                  <div class="mt-1 mb-2">
                    <a href="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($t['payment_proof']) ?>" target="_blank" class="text-xs text-primary hover:underline font-semibold flex items-center gap-1">
                      <i class="bi bi-image"></i> Xem UNC chuyển khoản
                    </a>
                  </div>
                  <?php if (!$isBuyer && $t['payment_status'] !== 'paid' && !in_array($os, ['cancelled'])): ?>
                    <form action="<?= $appUrl ?>/transactions/bank-received" method="POST" class="mb-2">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
                      <input type="hidden" name="id" value="<?= $t['id'] ?>">
                      <button type="submit" onclick="return confirm('Bạn xác nhận đã nhận đủ tiền vào tài khoản ngân hàng? Đơn hàng sẽ tự động chuyển sang Đang Giao.')" 
                              class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold text-white cursor-pointer border-0 bg-green-500 hover:bg-green-600 shadow-sm transition-colors">
                        <i class="bi bi-check2-circle"></i> Xác nhận đã nhận tiền
                      </button>
                    </form>
                  <?php endif; ?>
                <?php endif; ?>

                <!-- Action buttons -->
                <?php if (!in_array($os, ['completed', 'received', 'cancelled'])): ?>
                  <form action="<?= $appUrl ?>/transactions/update-status" method="POST" class="flex flex-wrap gap-1.5">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">

                    <?php if (!$isBuyer): // Seller actions ?>
                      <?php if ($os === 'pending'): ?>
                        <input type="hidden" name="status" value="shipping">
                        <button type="submit"
                                onclick="return confirm('Xác nhận bạn đã đóng gói và bắt đầu giao hàng?')"
                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold text-white cursor-pointer border-0 bg-cyan-500 hover:bg-cyan-600 transition-colors">
                          <i class="bi bi-truck"></i>Gửi hàng
                        </button>
                      <?php elseif ($os === 'shipping'): ?>
                        <input type="hidden" name="status" value="delivered">
                        <button type="submit"
                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold text-white cursor-pointer border-0 bg-primary hover:brightness-110 transition-all">
                          <i class="bi bi-geo"></i>Đã đến nơi
                        </button>
                      <?php endif; ?>

                      <button type="button"
                              onclick="if(confirm('Hủy đơn hàng này? Sản phẩm sẽ quay lại trạng thái đang bán.')) { const f=this.closest('form'); let s=f.querySelector('[name=status]'); if(s) s.value='cancelled'; else f.innerHTML+='<input type=\"hidden\" name=\"status\" value=\"cancelled\">'; f.submit(); }"
                              class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold text-red-500 border border-red-300 hover:bg-red-500 hover:text-white bg-transparent cursor-pointer transition-all">
                        <i class="bi bi-x-circle"></i>Hủy đơn
                      </button>

                    <?php else: // Buyer actions ?>
                      <?php if (in_array($os, ['shipping', 'delivered'])): ?>
                        <input type="hidden" name="status" value="completed">
                        <button type="submit"
                                onclick="return confirm('Xác nhận bạn đã nhận được hàng và hàng đúng mô tả?')"
                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold text-white cursor-pointer border-0 bg-green-500 hover:bg-green-600 transition-colors">
                          <i class="bi bi-check-circle-fill"></i>Đã nhận hàng
                        </button>
                      <?php endif; ?>
                    <?php endif; ?>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>