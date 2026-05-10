<?php
/**
 * Admin View: Dashboard — Premium Edition
 * $stats = [total_users, active_products, pending_count, tx_today, recent_tx, recent_products]
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$admin  = $_SESSION['user'];
?>

<div class="font-sans antialiased text-gray-800 dark:text-dark-text">

  <!-- Welcome Banner -->
  <div class="relative overflow-hidden bg-gradient-to-br from-[#1e1b4b] via-[#312e81] to-[#4f46e5] rounded-[24px] p-8 md:p-10 flex flex-col md:flex-row items-center justify-between gap-6 mb-8 shadow-[0_20px_60px_rgba(99,102,241,0.3)]">
    <!-- Blobs -->
    <div class="absolute w-[350px] h-[350px] rounded-full bg-[radial-gradient(circle,rgba(139,92,246,0.35)_0%,transparent_65%)] -top-[100px] right-[50px] pointer-events-none"></div>
    <div class="absolute w-[250px] h-[250px] rounded-full bg-[radial-gradient(circle,rgba(236,72,153,0.25)_0%,transparent_65%)] -bottom-[80px] left-[20%] pointer-events-none"></div>
    
    <div class="flex items-center gap-6 relative z-10 w-full md:w-auto">
      <div class="w-16 h-16 rounded-[16px] bg-gradient-to-br from-purple-400 to-pink-400 flex items-center justify-center font-black text-2xl text-white shadow-[0_8px_24px_rgba(0,0,0,0.3)] flex-shrink-0">
        <?= mb_strtoupper(mb_substr($admin['name'], 0, 1)) ?>
      </div>
      <div>
        <div class="text-sm font-medium text-white/60">Xin chào trở lại, 👋</div>
        <h2 class="text-2xl md:text-3xl font-extrabold text-white my-1"><?= htmlspecialchars($admin['name'], ENT_QUOTES) ?></h2>
        <div class="text-xs text-white/50 flex items-center gap-2">
          <span><i class="bi bi-calendar3 mr-1"></i><?= date('l, d/m/Y') ?></span>
          <span>·</span>
          <span><i class="bi bi-shield-check mr-1"></i>Administrator</span>
        </div>
      </div>
    </div>
    
    <div class="hidden lg:flex items-center gap-4 relative z-10">
      <?php if (($stats['pending_count'] ?? 0) > 0): ?>
        <div class="flex items-center bg-amber-500/15 border-[1.5px] border-amber-500/35 text-amber-400 px-4 py-2 rounded-full text-sm font-bold shadow-sm">
          <i class="bi bi-exclamation-triangle-fill mr-1.5"></i>
          <?= $stats['pending_count'] ?> bài chờ duyệt
        </div>
      <?php endif; ?>
      <a href="<?= $appUrl ?>/admin/products" class="flex items-center bg-white/15 border-[1.5px] border-white/30 text-white px-5 py-2.5 rounded-full text-sm font-bold no-underline hover:bg-white/25 transition-colors shadow-sm">
        <i class="bi bi-card-checklist mr-1.5"></i>Duyệt ngay
      </a>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-10">
    <?php
    $statsCards = [
      [
        'label'  => 'Tổng người dùng',
        'value'  => number_format($stats['total_users'] ?? 0),
        'icon'   => 'bi-people-fill',
        'bg'     => 'from-indigo-500 to-purple-500',
        'shadow' => 'shadow-indigo-500/50',
        'badge'  => 'Tài khoản',
        'trend'  => '+12%',
      ],
      [
        'label'  => 'Sản phẩm đang bán',
        'value'  => number_format($stats['active_products'] ?? 0),
        'icon'   => 'bi-bag-check-fill',
        'bg'     => 'from-emerald-500 to-emerald-600',
        'shadow' => 'shadow-emerald-500/50',
        'badge'  => 'Đang live',
        'trend'  => '+5%',
      ],
      [
        'label'  => 'Chờ duyệt',
        'value'  => number_format($stats['pending_count'] ?? 0),
        'icon'   => 'bi-hourglass-split',
        'bg'     => 'from-amber-500 to-red-500',
        'shadow' => 'shadow-amber-500/50',
        'badge'  => 'Cần xử lý',
        'trend'  => '',
      ],
      [
        'label'  => 'Giao dịch hôm nay',
        'value'  => number_format($stats['tx_today'] ?? 0),
        'icon'   => 'bi-receipt-cutoff',
        'bg'     => 'from-cyan-500 to-blue-500',
        'shadow' => 'shadow-cyan-500/50',
        'badge'  => 'Hôm nay',
        'trend'  => '+8%',
      ],
    ];
    ?>
    <?php foreach ($statsCards as $i => $card): ?>
      <div class="animate-[fadeInUp_0.5s_ease-out_both]" style="animation-delay:<?= $i * 80 ?>ms">
        <div class="bg-white dark:bg-dark-card rounded-[20px] border-[1.5px] border-gray-200 dark:border-dark-border p-6 relative overflow-hidden transition-all duration-300 hover:-translate-y-1.5 hover:shadow-[0_18px_44px_rgba(0,0,0,0.1)] dark:hover:shadow-black/50 flex items-center gap-5 group">
          <div class="w-14 h-14 rounded-[16px] flex items-center justify-center text-2xl text-white flex-shrink-0 bg-gradient-to-br <?= $card['bg'] ?> shadow-[0_10px_28px] <?= $card['shadow'] ?>">
            <i class="bi <?= $card['icon'] ?>"></i>
          </div>
          <div class="z-10">
            <div class="text-[0.8rem] font-bold text-gray-400 uppercase tracking-wider"><?= $card['label'] ?></div>
            <div class="text-3xl font-black text-gray-800 dark:text-dark-text leading-none my-1"><?= $card['value'] ?></div>
            <div class="flex items-center gap-2.5 mt-1.5">
              <span class="bg-gray-100 dark:bg-dark-2 text-gray-500 dark:text-gray-400 text-xs font-bold px-2.5 py-0.5 rounded-full"><?= $card['badge'] ?></span>
              <?php if ($card['trend']): ?>
                <span class="text-emerald-500 text-xs font-bold flex items-center"><i class="bi bi-arrow-up-right mr-1"></i><?= $card['trend'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="absolute -right-2 -bottom-2 text-[5.5rem] leading-none text-black/5 dark:text-white/5 pointer-events-none group-hover:scale-110 transition-transform">
            <i class="bi <?= $card['icon'] ?>"></i>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Content Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">

    <!-- Pending Posts -->
    <div class="animate-[fadeInUp_0.5s_ease-out_0.2s_both]">
      <div class="bg-white dark:bg-dark-card rounded-[20px] border-[1.5px] border-gray-200 dark:border-dark-border overflow-hidden shadow-sm">
        <div class="flex items-center justify-between px-6 py-5 border-b-[1.5px] border-gray-100 dark:border-dark-border bg-gray-50/50 dark:bg-dark-2/50">
          <div class="flex items-center gap-3 font-extrabold text-base text-gray-800 dark:text-dark-text">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-base bg-gradient-to-br from-amber-500 to-red-500">
              <i class="bi bi-hourglass-split"></i>
            </div>
            Bài đăng chờ duyệt
          </div>
          <a href="<?= $appUrl ?>/admin/products" class="text-sm font-bold text-indigo-500 no-underline flex items-center gap-1.5 hover:gap-2.5 transition-all">
            Xem tất cả <i class="bi bi-arrow-right"></i>
          </a>
        </div>
        <div class="p-2">
          <?php if (empty($stats['recent_products'])): ?>
            <div class="flex flex-col items-center justify-center gap-2 p-10 text-gray-400 text-sm">
              <i class="bi bi-check-circle-fill text-4xl text-gray-300 dark:text-gray-600"></i>
              <span>Không có bài nào chờ duyệt</span>
            </div>
          <?php else: ?>
            <div class="flex flex-col">
              <?php foreach (array_slice($stats['recent_products'], 0, 5) as $p): ?>
                <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-50 dark:border-dark-border hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors last:border-0">
                  <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0 bg-gradient-to-br from-amber-500 to-red-500">
                    <?= mb_strtoupper(mb_substr($p['seller_name'], 0, 1)) ?>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm text-gray-800 dark:text-dark-text truncate"><?= htmlspecialchars($p['title'], ENT_QUOTES) ?></div>
                    <div class="text-xs text-gray-400 mt-0.5 truncate">
                      bởi <?= htmlspecialchars($p['seller_name'], ENT_QUOTES) ?> · <?= date('d/m H:i', strtotime($p['created_at'])) ?>
                    </div>
                  </div>
                  <span class="text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap flex-shrink-0 <?= $p['type'] === 'auction' ? 'bg-amber-100 text-amber-600' : ($p['type'] === 'exchange' ? 'bg-cyan-100 text-cyan-600' : 'bg-indigo-100 text-indigo-600') ?>">
                    <?= $p['type'] === 'auction' ? '⚡ Đấu giá' : ($p['type'] === 'exchange' ? '🔄 Trao đổi' : '🏷️ Bán') ?>
                  </span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="animate-[fadeInUp_0.5s_ease-out_0.3s_both]">
      <div class="bg-white dark:bg-dark-card rounded-[20px] border-[1.5px] border-gray-200 dark:border-dark-border overflow-hidden shadow-sm">
        <div class="flex items-center justify-between px-6 py-5 border-b-[1.5px] border-gray-100 dark:border-dark-border bg-gray-50/50 dark:bg-dark-2/50">
          <div class="flex items-center gap-3 font-extrabold text-base text-gray-800 dark:text-dark-text">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-base bg-gradient-to-br from-cyan-500 to-blue-500">
              <i class="bi bi-receipt-cutoff"></i>
            </div>
            Giao dịch mới nhất
          </div>
          <a href="<?= $appUrl ?>/admin/reports" class="text-sm font-bold text-indigo-500 no-underline flex items-center gap-1.5 hover:gap-2.5 transition-all">
            Báo cáo <i class="bi bi-arrow-right"></i>
          </a>
        </div>
        <div class="p-2">
          <?php if (empty($stats['recent_tx'])): ?>
            <div class="flex flex-col items-center justify-center gap-2 p-10 text-gray-400 text-sm">
              <i class="bi bi-inbox text-4xl text-gray-300 dark:text-gray-600"></i>
              <span>Chưa có giao dịch nào</span>
            </div>
          <?php else: ?>
            <div class="flex flex-col">
              <?php foreach ($stats['recent_tx'] as $t): ?>
                <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-50 dark:border-dark-border hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors last:border-0">
                  <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0 bg-gradient-to-br from-cyan-500 to-blue-500">
                    <?= mb_strtoupper(mb_substr($t['buyer_name'], 0, 1)) ?>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm text-gray-800 dark:text-dark-text truncate">
                      <?= htmlspecialchars($t['product_title'], ENT_QUOTES) ?>
                      <?php if (($t['order_status'] ?? '') === 'cancelled'): ?>
                        <span class="bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded ml-1">Đã hủy</span>
                      <?php endif; ?>
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5 truncate">
                      <?= htmlspecialchars($t['buyer_name'], ENT_QUOTES) ?> mua từ <?= htmlspecialchars($t['seller_name'], ENT_QUOTES) ?>
                    </div>
                  </div>
                  <?php $isCancelled = ($t['order_status'] ?? '') === 'cancelled'; ?>
                  <span class="font-black text-sm whitespace-nowrap flex-shrink-0 <?= $isCancelled ? 'text-gray-400 line-through' : 'text-emerald-500' ?>">
                    +<?= number_format($t['amount'], 0, ',', '.') ?>đ
                  </span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="animate-[fadeInUp_0.5s_ease-out_0.4s_both]">
    <div class="mb-6">
      <h5 class="font-extrabold text-xl text-gray-800 dark:text-dark-text m-0">Truy cập nhanh</h5>
      <p class="text-sm text-gray-500 mt-1">Điều hướng nhanh đến các tính năng quản trị</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
      <?php
      $actions = [
        ['admin/users',      'bi-people-fill',    'from-indigo-500 to-purple-500',  'shadow-indigo-500/40',  'Người dùng',   'Xem & khóa TK'],
        ['admin/products',   'bi-card-checklist', 'from-amber-500 to-red-500',  'shadow-amber-500/40',  'Kiểm duyệt',  'Duyệt & xóa'],
        ['admin/categories', 'bi-tags-fill',      'from-cyan-500 to-blue-500',  'shadow-cyan-500/40',   'Danh mục',     'CRUD danh mục'],
        ['admin/reports',    'bi-bar-chart-fill', 'from-emerald-500 to-emerald-600',  'shadow-emerald-500/40',  'Báo cáo',    'Thống kê doanh thu'],
        ['admin/giveaways',  'bi-gift-fill',      'from-pink-500 to-orange-500',  'shadow-pink-500/40',  'Giveaway',     'Tổ chức & quay số'],
        ['admin/audit-log',  'bi-journal-text',   'from-slate-500 to-slate-600',  'shadow-slate-500/40','Nhật ký',    'Lịch sử admin'],
      ];
      ?>
      <?php foreach ($actions as $i => [$url, $icon, $bg, $shadow, $title, $desc]): ?>
        <a href="<?= $appUrl ?>/<?= $url ?>" class="block bg-white dark:bg-dark-card rounded-[18px] border-[1.5px] border-gray-200 dark:border-dark-border p-5 text-center no-underline transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_18px_44px_rgba(0,0,0,0.1)] dark:hover:shadow-black/50 hover:border-transparent group">
          <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center text-2xl text-white bg-gradient-to-br <?= $bg ?> shadow-[0_8px_22px] <?= $shadow ?> transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
            <i class="bi <?= $icon ?>"></i>
          </div>
          <div class="font-extrabold text-[15px] text-gray-800 dark:text-dark-text mb-1 truncate"><?= $title ?></div>
          <div class="text-[12px] text-gray-400 truncate"><?= $desc ?></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

</div>
