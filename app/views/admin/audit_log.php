<?php
/**
 * Admin View: Nhật ký hành động Admin (Audit Log) — Tailwind Edition
 * Biến: $logs
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

$actionLabels = [
    'approve_product'  => ['✅ Duyệt SP',         'bg-green-100 text-green-700'],
    'reject_product'   => ['❌ Từ chối SP',        'bg-amber-100 text-amber-700'],
    'delete_product'   => ['🗑️ Xóa SP',            'bg-red-100 text-red-700'],
    'lock_user'        => ['🔒 Khóa user',          'bg-red-100 text-red-700'],
    'unlock_user'      => ['🔓 Mở khóa user',       'bg-green-100 text-green-700'],
    'create_category'  => ['➕ Tạo danh mục',       'bg-indigo-100 text-indigo-700'],
    'update_category'  => ['✏️ Sửa danh mục',        'bg-cyan-100 text-cyan-700'],
    'delete_category'  => ['🗑️ Xóa danh mục',       'bg-red-100 text-red-700'],
];
?>

<div class="font-sans antialiased text-gray-800 dark:text-dark-text">
  <h4 class="text-xl font-extrabold text-gray-800 dark:text-dark-text flex items-center gap-2 mb-6 m-0">
    <i class="bi bi-journal-text text-gray-500"></i>Nhật ký hành động Admin
  </h4>

  <?php if (empty($logs)): ?>
    <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border p-16 text-center shadow-sm flex flex-col items-center gap-3">
      <i class="bi bi-journal-x text-5xl text-gray-200 dark:text-gray-700"></i>
      <p class="m-0 text-sm font-medium text-gray-400">Chưa có hành động nào được ghi lại.</p>
    </div>
  <?php else: ?>
    <!-- Info bar -->
    <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 text-blue-700 dark:text-blue-400 px-5 py-3 rounded-xl flex items-center gap-2.5 mb-5 text-sm font-medium animate-[fadeIn_0.3s_ease-out_both]">
      <i class="bi bi-info-circle-fill flex-shrink-0"></i>
      <span>Hiển thị <strong class="font-black"><?= count($logs) ?></strong> hành động gần nhất. Dữ liệu không thể sửa/xóa.</span>
    </div>

    <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm animate-[fadeInUp_0.4s_ease-out_both]">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[700px]">
          <thead>
            <tr class="bg-gray-50 dark:bg-dark-2 border-b border-light-border dark:border-dark-border text-[11px] font-bold text-gray-500 uppercase tracking-wider">
              <th class="py-3.5 px-5">#</th>
              <th class="py-3.5 px-5">Thời gian</th>
              <th class="py-3.5 px-5">Admin</th>
              <th class="py-3.5 px-5">Hành động</th>
              <th class="py-3.5 px-5">Đối tượng</th>
              <th class="py-3.5 px-5">Ghi chú</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
            <?php foreach ($logs as $i => $log): ?>
              <?php [$actionLabel, $actionCls] = $actionLabels[$log['action']] ?? [$log['action'], 'bg-gray-100 text-gray-600']; ?>
              <tr class="hover:bg-gray-50/50 dark:hover:bg-dark-2/50 transition-colors">
                <td class="py-4 px-5 text-xs font-bold text-gray-400"><?= $i + 1 ?></td>
                <td class="py-4 px-5 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-700 dark:text-gray-300"><?= date('d/m/Y', strtotime($log['created_at'])) ?></div>
                  <div class="text-xs font-medium text-gray-400"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                </td>
                <td class="py-4 px-5">
                  <div class="font-bold text-sm text-gray-800 dark:text-dark-text"><?= htmlspecialchars($log['admin_name'], ENT_QUOTES) ?></div>
                  <div class="text-xs text-gray-400"><?= htmlspecialchars($log['admin_email'], ENT_QUOTES) ?></div>
                </td>
                <td class="py-4 px-5 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold <?= $actionCls ?>">
                    <?= $actionLabel ?>
                  </span>
                </td>
                <td class="py-4 px-5 whitespace-nowrap">
                  <code class="text-xs font-mono font-bold bg-gray-100 dark:bg-dark-2 text-indigo-600 dark:text-indigo-400 px-2.5 py-1 rounded-lg">
                    <?= htmlspecialchars(ucfirst($log['target_type']), ENT_QUOTES) ?> #<?= $log['target_id'] ?>
                  </code>
                </td>
                <td class="py-4 px-5 text-xs font-medium text-gray-600 dark:text-gray-400 max-w-[260px] truncate">
                  <?= htmlspecialchars($log['note'] ?? '—', ENT_QUOTES) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>
