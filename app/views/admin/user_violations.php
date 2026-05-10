<?php
/**
 * Admin View: Lịch sử vi phạm (Strikes) của người dùng
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
?>

<div class="font-sans antialiased text-gray-800 dark:text-dark-text animate-[fadeIn_0.3s_ease-out_both]">
  <!-- Header -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
      <h3 class="text-2xl font-black text-gray-800 dark:text-dark-text mb-1 flex items-center">
        <i class="bi bi-clock-history mr-2 text-amber-500"></i>Lịch sử vi phạm
      </h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 m-0">Tài khoản: <strong class="text-gray-800 dark:text-gray-200"><?= htmlspecialchars($user['name'], ENT_QUOTES) ?></strong> (<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>)</p>
    </div>
    <a href="<?= $appUrl ?>/admin/users" class="inline-flex items-center justify-center bg-white dark:bg-dark-card border-[1.5px] border-gray-200 dark:border-dark-border text-gray-700 dark:text-gray-200 px-5 py-2.5 rounded-full text-sm font-extrabold hover:bg-gray-50 dark:hover:bg-dark-2 hover:shadow-md hover:-translate-y-0.5 transition-all no-underline">
      <i class="bi bi-arrow-left mr-2"></i>Quay lại
    </a>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <!-- Strike Count Card -->
    <div class="md:col-span-1 animate-[fadeInUp_0.4s_ease-out_both]">
      <div class="bg-red-50/50 dark:bg-red-500/5 p-6 rounded-[20px] border border-red-200 dark:border-red-500/20 h-full shadow-sm flex flex-col justify-center relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 text-[6rem] text-red-500/10 pointer-events-none">
          <i class="bi bi-exclamation-triangle"></i>
        </div>
        <h6 class="text-[11px] font-black text-red-600 dark:text-red-500 uppercase tracking-widest mb-3 relative z-10">Tổng số gậy</h6>
        <div class="text-[3.5rem] leading-none font-black text-red-600 dark:text-red-500 mb-4 relative z-10"><?= (int)($user['strike_count'] ?? 0) ?>/3</div>
        <div class="relative z-10">
          <?php if ($user['strike_count'] >= 3): ?>
            <span class="inline-flex items-center bg-red-600 text-white rounded-full px-4 py-2 text-sm font-bold shadow-sm">
              <i class="bi bi-lock-fill mr-1.5"></i>Bị khóa vĩnh viễn
            </span>
          <?php elseif ($user['strike_count'] == 2): ?>
            <span class="inline-flex items-center bg-amber-500 text-white rounded-full px-4 py-2 text-sm font-bold shadow-sm">
              <i class="bi bi-exclamation-triangle-fill mr-1.5"></i>Đang bị khóa 7 ngày
            </span>
          <?php elseif ($user['strike_count'] == 1): ?>
            <span class="inline-flex items-center bg-sky-500 text-white rounded-full px-4 py-2 text-sm font-bold shadow-sm">
              <i class="bi bi-info-circle-fill mr-1.5"></i>Đã cảnh cáo
            </span>
          <?php else: ?>
            <span class="inline-flex items-center bg-green-500 text-white rounded-full px-4 py-2 text-sm font-bold shadow-sm">
              <i class="bi bi-check-circle-fill mr-1.5"></i>Tài khoản sạch
            </span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <!-- User Info Card -->
    <div class="md:col-span-2 animate-[fadeInUp_0.5s_ease-out_both]">
      <div class="bg-white dark:bg-dark-card p-6 rounded-[20px] border border-light-border dark:border-dark-border h-full shadow-sm">
        <h6 class="text-[11px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-5">Thông tin tài khoản</h6>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
          <div class="bg-gray-50 dark:bg-dark-2 rounded-xl p-4 border border-gray-100 dark:border-dark-border">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-bold mb-1">Ngày tham gia</div>
            <div class="text-sm font-extrabold text-gray-800 dark:text-dark-text"><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></div>
          </div>
          
          <div class="bg-gray-50 dark:bg-dark-2 rounded-xl p-4 border border-gray-100 dark:border-dark-border">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-bold mb-1">Trạng thái hiện tại</div>
            <?php if ($user['is_locked']): ?>
              <div class="text-sm font-extrabold text-red-600">Bị khóa (<?= $user['locked_until'] ? 'đến '.date('d/m/Y', strtotime($user['locked_until'])) : 'Vĩnh viễn' ?>)</div>
            <?php else: ?>
              <div class="text-sm font-extrabold text-green-600">Hoạt động</div>
            <?php endif; ?>
          </div>
          
          <?php if (!empty($user['lock_reason'])): ?>
          <div class="sm:col-span-2 bg-red-50 dark:bg-red-500/5 rounded-xl p-4 border border-red-100 dark:border-red-500/10">
            <div class="text-xs text-red-500/80 font-bold mb-1">Lý do khóa hiện tại</div>
            <div class="text-sm font-extrabold text-red-600 dark:text-red-400"><?= htmlspecialchars($user['lock_reason'], ENT_QUOTES) ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Violations Table -->
  <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm animate-[fadeInUp_0.6s_ease-out_both]">
    <div class="px-6 py-4 border-b border-light-border dark:border-dark-border bg-gray-50/50 dark:bg-dark-2/50">
      <h5 class="font-extrabold text-sm text-gray-800 dark:text-dark-text m-0">Nhật ký nhận gậy</h5>
    </div>
    
    <?php if (empty($violations)): ?>
      <div class="p-12 text-center text-gray-400 flex flex-col items-center justify-center">
        <i class="bi bi-shield-check text-green-500/50 text-[3.5rem] mb-3"></i>
        <p class="m-0 text-sm font-bold text-gray-500">Người dùng này chưa có vi phạm nào.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[600px]">
          <thead>
            <tr class="bg-gray-50/30 dark:bg-dark-2/30 text-[11px] font-bold text-gray-500 uppercase tracking-widest border-b border-light-border dark:border-dark-border">
              <th class="py-3.5 px-6">Gậy thứ</th>
              <th class="py-3.5 px-6">Thời gian</th>
              <th class="py-3.5 px-6">Người xử lý</th>
              <th class="py-3.5 px-6">Lý do / Bằng chứng</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
            <?php foreach ($violations as $v): ?>
              <tr class="hover:bg-gray-50/50 dark:hover:bg-dark-2/50 transition-colors">
                <td class="py-4 px-6 whitespace-nowrap">
                  <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-red-100 text-red-700 text-xs font-black">
                    Gậy <?= (int)$v['strike_number'] ?>
                  </span>
                </td>
                <td class="py-4 px-6 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-0.5"><i class="bi bi-calendar3 mr-1.5 text-gray-400"></i><?= date('d/m/Y', strtotime($v['created_at'])) ?></div>
                  <div class="text-xs font-medium text-gray-400"><i class="bi bi-clock mr-1.5"></i><?= date('H:i:s', strtotime($v['created_at'])) ?></div>
                </td>
                <td class="py-4 px-6 whitespace-nowrap">
                  <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-gray-100 dark:bg-dark-2 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold">
                    <i class="bi bi-person-badge-fill mr-1.5 text-gray-400"></i><?= htmlspecialchars($v['admin_name'] ?? 'Admin', ENT_QUOTES) ?>
                  </span>
                </td>
                <td class="py-4 px-6 min-w-[250px]">
                  <div class="text-sm font-bold text-gray-800 dark:text-dark-text mb-2 leading-relaxed">
                    <?= nl2br(htmlspecialchars($v['reason'], ENT_QUOTES)) ?>
                  </div>
                  <?php if (!empty($v['evidence_url'])): ?>
                    <a href="<?= htmlspecialchars($v['evidence_url'], ENT_QUOTES) ?>" target="_blank" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 transition-colors no-underline">
                      <i class="bi bi-box-arrow-up-right mr-1.5"></i>Xem bằng chứng
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
