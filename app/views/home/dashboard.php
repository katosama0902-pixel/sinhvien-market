<?php
/**
 * View: Dashboard sinh viên — bài đăng + giao dịch của tôi
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$user   = $_SESSION['user'];
use Core\Controller;
use Core\Flash;
$ctrl = new class extends Controller {};
$csrf = $ctrl->csrfToken();

$statusMap = [
    'pending'   => ['Chờ duyệt',   'warning',   'bi-hourglass-split'],
    'active'    => ['Đang bán',    'success',   'bi-check-circle'],
    'sold'      => ['Đã bán',      'dark',      'bi-bag-check-fill'],
    'cancelled' => ['Đã thu hồi', 'secondary', 'bi-x-circle'],
];

$totalBuyAmt  = 0;
$totalSellAmt = 0;
foreach ($transactions as $t) {
    $os = $t['order_status'] ?? '';
    if (in_array($os, ['delivered', 'received', 'completed'])) {
        if ((int)$t['buyer_id']  === (int)$user['id']) $totalBuyAmt  += $t['amount'];
        if ((int)$t['seller_id'] === (int)$user['id']) $totalSellAmt += $t['amount'];
    }
}
?>

<div class="container mx-auto px-4 py-8 max-w-6xl font-sans text-gray-800 dark:text-dark-text">

  <!-- Welcome banner -->
  <div class="relative overflow-hidden rounded-[24px] p-6 md:p-8 flex items-center gap-5 mb-8 shadow-[0_12px_40px_rgba(79,70,229,0.25)] bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 animate-[fadeInUp_0.4s_ease-out_both]">
    <div class="w-[72px] h-[72px] rounded-full flex items-center justify-center flex-shrink-0 bg-white/20 text-white text-[2rem] font-black border border-white/30 backdrop-blur-sm shadow-inner">
      <?= mb_strtoupper(mb_substr($user['name'], 0, 1)) ?>
    </div>
    <div class="flex-grow z-10 text-white">
      <div class="text-white/80 text-sm font-medium">Xin chào,</div>
      <h4 class="font-extrabold text-2xl mb-1 m-0"><?= htmlspecialchars($user['name'], ENT_QUOTES) ?></h4>
      <div class="text-white/70 text-sm"><?= htmlspecialchars($user['email'], ENT_QUOTES) ?></div>
    </div>
    <div class="hidden md:block z-10">
      <a href="<?= $appUrl ?>/products/create" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-indigo-600 font-bold text-sm hover:bg-gray-50 hover:-translate-y-0.5 transition-all shadow-lg no-underline">
        <i class="bi bi-plus-circle-fill text-lg leading-none"></i> Đăng bán mới
      </a>
    </div>
    <!-- Decor blobs -->
    <div class="absolute w-64 h-64 bg-white/10 rounded-full blur-3xl -top-10 -right-10 pointer-events-none"></div>
    <div class="absolute w-40 h-40 bg-pink-400/20 rounded-full blur-2xl -bottom-10 left-1/4 pointer-events-none"></div>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-8 animate-[fadeInUp_0.5s_ease-out_0.1s_both]">
    <!-- Bài đăng -->
    <div class="bg-white dark:bg-dark-card rounded-2xl p-5 text-center border-2 border-gray-100 dark:border-dark-border shadow-sm hover:-translate-y-1 hover:shadow-md transition-all">
      <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Bài đăng</div>
      <div class="font-black text-3xl text-indigo-500"><?= count($products) ?></div>
    </div>
    <!-- Đã bán -->
    <div class="bg-white dark:bg-dark-card rounded-2xl p-5 text-center border-2 border-gray-100 dark:border-dark-border shadow-sm hover:-translate-y-1 hover:shadow-md transition-all">
      <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Đã bán được</div>
      <div class="font-black text-2xl md:text-3xl text-emerald-500"><?= number_format($totalSellAmt, 0, ',', '.') ?>đ</div>
    </div>
    <!-- Đã mua -->
    <div class="bg-white dark:bg-dark-card rounded-2xl p-5 text-center border-2 border-gray-100 dark:border-dark-border shadow-sm hover:-translate-y-1 hover:shadow-md transition-all">
      <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Đã mua</div>
      <div class="font-black text-2xl md:text-3xl text-red-500"><?= number_format($totalBuyAmt, 0, ',', '.') ?>đ</div>
    </div>
    <!-- Giao dịch -->
    <div class="bg-white dark:bg-dark-card rounded-2xl p-5 text-center border-2 border-gray-100 dark:border-dark-border shadow-sm hover:-translate-y-1 hover:shadow-md transition-all">
      <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Giao dịch</div>
      <div class="font-black text-3xl text-cyan-500"><?= count($transactions) ?></div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8">

    <!-- ─── Sản phẩm của tôi ─────────────────────────────── -->
    <div class="lg:col-span-7 animate-[fadeInUp_0.6s_ease-out_0.2s_both]">
      <div class="bg-white dark:bg-dark-card rounded-[20px] border-2 border-gray-100 dark:border-dark-border overflow-hidden shadow-sm">
        <div class="flex justify-between items-center px-6 py-4 border-b-2 border-gray-50 dark:border-dark-border bg-gray-50/50 dark:bg-dark-2/50">
          <h6 class="font-extrabold text-gray-800 dark:text-dark-text m-0 text-base flex items-center gap-2">
            <i class="bi bi-bag-fill text-indigo-500"></i> Bài đăng của tôi
          </h6>
          <a href="<?= $appUrl ?>/products/my" class="text-sm font-bold text-indigo-500 hover:text-indigo-600 no-underline px-3 py-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">Xem tất cả</a>
        </div>

        <?php if (empty($products)): ?>
          <div class="p-10 text-center text-gray-400">
            <i class="bi bi-bag-x block text-[3rem] mb-3 opacity-30"></i>
            <div class="text-sm mb-1">Chưa có bài đăng.</div>
            <a href="<?= $appUrl ?>/products/create" class="text-indigo-500 font-bold no-underline hover:underline">Đăng bán ngay!</a>
          </div>
        <?php else: ?>
          <div class="flex flex-col">
            <?php foreach (array_slice($products, 0, 5) as $p): ?>
              <?php 
                $statusMapTW = [
                    'pending'   => ['Chờ duyệt',   'bg-amber-100 text-amber-600',   'bi-hourglass-split'],
                    'active'    => ['Đang bán',    'bg-emerald-100 text-emerald-600',   'bi-check-circle-fill'],
                    'sold'      => ['Đã bán',      'bg-gray-200 text-gray-600',      'bi-bag-check-fill'],
                    'cancelled' => ['Đã thu hồi', 'bg-red-100 text-red-600', 'bi-x-circle-fill'],
                ];
                [$statusLabel, $statusColor, $statusIcon] = $statusMapTW[$p['status']] ?? ['?','bg-gray-100 text-gray-500','bi-dash']; 
              ?>
              <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-50 dark:border-dark-border hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors last:border-0 group">
                <div class="w-14 h-14 rounded-xl overflow-hidden flex-shrink-0 bg-gray-100 dark:bg-dark-2 shadow-sm border border-gray-200 dark:border-gray-700 relative">
                  <?php if ($p['image']): ?>
                    <img src="<?= $appUrl ?>/products/image?id=<?= (int)$p['id'] ?>" class="w-full h-full object-cover">
                  <?php else: ?>
                    <div class="flex items-center justify-center h-full text-gray-300">
                      <i class="bi bi-image"></i>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="flex-grow min-w-0">
                  <a href="<?= $appUrl ?>/products/show?id=<?= $p['id'] ?>" class="font-bold text-gray-800 dark:text-dark-text no-underline block truncate mb-1 group-hover:text-indigo-500 transition-colors">
                    <?= htmlspecialchars($p['title'], ENT_QUOTES) ?>
                  </a>
                  <div class="text-xs text-gray-400 font-medium"><i class="bi bi-calendar3 mr-1"></i><?= date('d/m/Y', strtotime($p['created_at'])) ?></div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold flex-shrink-0 <?= $statusColor ?>">
                  <i class="bi <?= $statusIcon ?>"></i><?= $statusLabel ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ─── Giao dịch gần đây ────────────────────────────── -->
    <div class="lg:col-span-5 animate-[fadeInUp_0.7s_ease-out_0.3s_both]">
      <div class="bg-white dark:bg-dark-card rounded-[20px] border-2 border-gray-100 dark:border-dark-border overflow-hidden shadow-sm">
        <div class="flex justify-between items-center px-6 py-4 border-b-2 border-gray-50 dark:border-dark-border bg-gray-50/50 dark:bg-dark-2/50">
          <h6 class="font-extrabold text-gray-800 dark:text-dark-text m-0 text-base flex items-center gap-2">
            <i class="bi bi-receipt text-emerald-500"></i> Giao dịch gần đây
          </h6>
          <a href="<?= $appUrl ?>/transactions/history" class="text-sm font-bold text-emerald-500 hover:text-emerald-600 no-underline px-3 py-1.5 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors">Lịch sử</a>
        </div>

        <?php if (empty($transactions)): ?>
          <div class="p-10 text-center text-gray-400">
            <i class="bi bi-receipt-cutoff block text-[3rem] mb-3 opacity-30"></i>
            <div class="text-sm font-medium">Chưa có giao dịch nào.</div>
          </div>
        <?php else: ?>
          <div class="flex flex-col">
            <?php foreach (array_slice($transactions, 0, 6) as $t): ?>
              <?php 
                $isBuyer = (int)$t['buyer_id'] === (int)$user['id']; 
                $isCancelled = ($t['order_status'] ?? '') === 'cancelled';
              ?>
              <div class="flex justify-between items-center px-6 py-4 border-b border-gray-50 dark:border-dark-border hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors last:border-0">
                <div class="min-w-0 pr-3">
                  <div class="text-sm font-bold text-gray-800 dark:text-dark-text truncate mb-1">
                    <?= htmlspecialchars($t['product_title'], ENT_QUOTES) ?>
                  </div>
                  <div class="text-xs text-gray-500 flex items-center flex-wrap gap-1">
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold <?= $isBuyer ? 'bg-indigo-50 text-indigo-500' : 'bg-emerald-50 text-emerald-500' ?>">
                      <?= $isBuyer ? 'Mua' : 'Bán' ?>
                    </span>
                    <span class="truncate max-w-[120px]">
                      <?= $isBuyer ? htmlspecialchars($t['seller_name'], ENT_QUOTES) : htmlspecialchars($t['buyer_name'], ENT_QUOTES) ?>
                    </span>
                    <?php if ($isCancelled): ?>
                      <span class="text-red-500 bg-red-50 px-1.5 py-0.5 rounded font-bold text-[10px] ml-1">Đã hủy</span>
                    <?php endif; ?>
                  </div>
                </div>
                <span class="font-black text-sm flex-shrink-0 <?= $isCancelled ? 'text-gray-400 line-through' : ($isBuyer ? 'text-red-500' : 'text-emerald-500') ?> font-mono">
                  <?= $isBuyer ? '−' : '+' ?><?= number_format($t['amount'], 0, ',', '.') ?>đ
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Quick links -->
      <div class="mt-6 flex flex-col gap-3">
        <a href="<?= $appUrl ?>/products/create" class="flex items-center justify-center gap-2 w-full py-3.5 rounded-xl bg-indigo-500 text-white font-extrabold text-sm hover:brightness-110 shadow-md shadow-indigo-500/20 transition-all border-0 no-underline">
          <i class="bi bi-plus-circle-fill"></i> Đăng bán sản phẩm mới
        </a>
        <a href="<?= $appUrl ?>/products" class="flex items-center justify-center gap-2 w-full py-3.5 rounded-xl bg-white dark:bg-dark-card text-gray-600 dark:text-gray-300 border-2 border-gray-200 dark:border-dark-border font-bold text-sm hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors border-0 no-underline">
          <i class="bi bi-shop"></i> Mua sắm thêm
        </a>
      </div>
    </div>

  </div>
</div>
