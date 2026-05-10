<?php
/**
 * Notifications View — Trang xem tất cả thông báo
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

$typeIcons = [
    'product_approved' => ['icon' => 'bi-check-circle-fill', 'color' => '#22c55e', 'bg' => 'rgba(34,197,94,.12)'],
    'product_rejected' => ['icon' => 'bi-x-circle-fill',     'color' => '#ef4444', 'bg' => 'rgba(239,68,68,.12)'],
    'item_sold'        => ['icon' => 'bi-bag-check-fill',     'color' => '#8b5cf6', 'bg' => 'rgba(139,92,246,.12)'],
    'wishlist_drop'    => ['icon' => 'bi-graph-down-arrow',   'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.12)'],
    'new_message'      => ['icon' => 'bi-chat-dots-fill',     'color' => '#3b82f6', 'bg' => 'rgba(59,130,246,.12)'],
];
?>

<div class="max-w-2xl mx-auto px-4 py-8">

  <!-- Page title -->
  <h1 class="text-2xl font-extrabold text-gray-800 dark:text-dark-text mb-6 flex items-center gap-2">
    <i class="bi bi-bell text-primary"></i>Thông báo của tôi
  </h1>

  <?php if (empty($notifications)): ?>
    <!-- Empty state -->
    <div class="text-center py-20">
      <i class="bi bi-bell-slash text-6xl text-gray-200 dark:text-gray-600 block mb-4"></i>
      <div class="text-lg font-bold text-gray-400 dark:text-gray-500 mb-1">Chưa có thông báo nào</div>
      <div class="text-sm text-gray-400">Khi có cập nhật, chúng sẽ xuất hiện ở đây.</div>
    </div>

  <?php else: ?>
    <div class="space-y-2">
      <?php foreach ($notifications as $n): ?>
        <?php
          $meta   = $typeIcons[$n['type']] ?? ['icon' => 'bi-bell-fill', 'color' => '#6b7280', 'bg' => 'rgba(107,114,128,.12)'];
          $target = $n['link'] ? htmlspecialchars($n['link'], ENT_QUOTES) : '#';
          $unread = !$n['is_read'];

          // Time diff
          $diff = time() - strtotime($n['created_at']);
          if ($diff < 60)       $timeStr = 'Vừa xong';
          elseif ($diff < 3600) $timeStr = floor($diff/60)  . ' phút trước';
          elseif ($diff < 86400) $timeStr = floor($diff/3600) . ' giờ trước';
          else                   $timeStr = date('d/m/Y H:i', strtotime($n['created_at']));
        ?>
        <a href="<?= $target ?>"
           class="flex items-start gap-4 p-4 rounded-2xl border-2 no-underline transition-all group
                  <?= $unread
                      ? 'bg-indigo-50/60 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-800/40'
                      : 'bg-white dark:bg-dark-card border-light-border dark:border-dark-border' ?>"
           style="hover:border-primary">

          <!-- Icon -->
          <div class="w-11 h-11 rounded-full flex items-center justify-center text-lg flex-shrink-0"
               style="background:<?= $meta['bg'] ?>;color:<?= $meta['color'] ?>">
            <i class="<?= $meta['icon'] ?>"></i>
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="text-sm font-bold text-gray-800 dark:text-dark-text mb-0.5">
              <?= htmlspecialchars($n['title'], ENT_QUOTES) ?>
            </div>
            <?php if ($n['body']): ?>
              <div class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                <?= htmlspecialchars($n['body'], ENT_QUOTES) ?>
              </div>
            <?php endif; ?>
            <div class="text-[11px] text-gray-400 mt-1.5">
              <i class="bi bi-clock mr-1"></i><?= $timeStr ?>
            </div>
          </div>

          <!-- Unread dot -->
          <?php if ($unread): ?>
            <div class="w-2.5 h-2.5 rounded-full bg-primary flex-shrink-0 mt-1.5"></div>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
