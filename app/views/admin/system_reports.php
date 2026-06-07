<?php
/**
 * Admin View: Quản lý Báo cáo / Tố cáo vi phạm — Tailwind Edition
 * Biến: $reports, $status
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

$pendingCount = 0;
foreach ($reports as $r) {
    if ($r['status'] === 'pending') $pendingCount++;
}
?>

<div class="font-sans antialiased text-gray-800 dark:text-dark-text">
  <!-- Page Header + Filter Tabs -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <h4 class="text-xl font-extrabold text-gray-800 dark:text-dark-text flex items-center gap-2 m-0">
      <i class="bi bi-shield-exclamation text-red-500"></i>Tố cáo vi phạm
    </h4>
    <div class="flex items-center gap-2 flex-wrap">
      <a href="<?= $appUrl ?>/admin/system-reports"
         class="px-4 py-2 rounded-full text-xs font-bold no-underline transition-all border <?= $status === '' ? 'bg-gray-800 text-white border-gray-800 shadow-sm' : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-gray-400' ?>">
        Tất cả
      </a>
      <a href="<?= $appUrl ?>/admin/system-reports?status=pending"
         class="relative px-4 py-2 rounded-full text-xs font-bold no-underline transition-all border <?= $status === 'pending' ? 'bg-amber-500 text-white border-amber-500 shadow-sm' : 'border-amber-300 dark:border-amber-700 text-amber-600 dark:text-amber-400 hover:border-amber-400' ?>">
        Chưa xử lý
        <?php if ($pendingCount > 0): ?>
          <span class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] flex items-center justify-center bg-red-500 text-white text-[9px] font-black rounded-full px-1 shadow">
            <?= $pendingCount ?>
          </span>
        <?php endif; ?>
      </a>
      <a href="<?= $appUrl ?>/admin/system-reports?status=resolved"
         class="px-4 py-2 rounded-full text-xs font-bold no-underline transition-all border <?= $status === 'resolved' ? 'bg-green-500 text-white border-green-500 shadow-sm' : 'border-green-300 dark:border-green-700 text-green-600 dark:text-green-400 hover:border-green-400' ?>">
        Đã giải quyết
      </a>
      <a href="<?= $appUrl ?>/admin/system-reports?status=ignored"
         class="px-4 py-2 rounded-full text-xs font-bold no-underline transition-all border <?= $status === 'ignored' ? 'bg-gray-500 text-white border-gray-500 shadow-sm' : 'border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:border-gray-400' ?>">
        Bỏ qua
      </a>
    </div>
  </div>

  <!-- Table Card -->
  <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm animate-[fadeInUp_0.4s_ease-out_both]">
    <?php if (empty($reports)): ?>
      <div class="p-16 text-center flex flex-col items-center gap-3">
        <i class="bi bi-shield-check text-5xl text-green-500/60"></i>
        <p class="m-0 text-sm font-semibold text-gray-400">Tuyệt vời! Không có báo cáo vi phạm nào cần xử lý.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[900px]">
          <thead>
            <tr class="bg-gray-50 dark:bg-dark-2 border-b border-light-border dark:border-dark-border text-[11px] font-bold text-gray-500 uppercase tracking-wider">
              <th class="py-3.5 px-5">#</th>
              <th class="py-3.5 px-5">Người tố cáo</th>
              <th class="py-3.5 px-5">Đối tượng bị Tố cáo</th>
              <th class="py-3.5 px-5">Nội dung vi phạm</th>
              <th class="py-3.5 px-5">Trạng thái</th>
              <th class="py-3.5 px-5">Ngày tạo</th>
              <th class="py-3.5 px-5 text-right">Hành động</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
            <?php foreach ($reports as $i => $r): ?>
              <tr class="hover:bg-gray-50/50 dark:hover:bg-dark-2/50 transition-colors">
                <td class="py-4 px-5 text-xs font-bold text-gray-400"><?= $r['id'] ?></td>
                <td class="py-4 px-5">
                  <a href="<?= $appUrl ?>/admin/users/detail?id=<?= $r['reporter_id'] ?>"
                     class="font-bold text-sm text-gray-800 dark:text-dark-text no-underline hover:text-primary transition-colors">
                    <?= htmlspecialchars($r['reporter_name'], ENT_QUOTES) ?>
                  </a>
                </td>
                <td class="py-4 px-5">
                  <?php if ($r['target_user_id']): ?>
                    <div class="flex items-center gap-1.5 mb-1">
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 dark:bg-dark-2 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                        <i class="bi bi-person"></i>User
                      </span>
                      <a href="<?= $appUrl ?>/admin/users/detail?id=<?= $r['target_user_id'] ?>"
                         class="font-bold text-sm text-primary no-underline hover:brightness-110">
                        <?= htmlspecialchars($r['target_name'] ?? '', ENT_QUOTES) ?>
                      </a>
                    </div>
                  <?php endif; ?>
                  <?php if ($r['product_id']): ?>
                    <div class="flex items-center gap-1.5">
                      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 dark:bg-dark-2 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                        <i class="bi bi-box"></i>Sản phẩm
                      </span>
                      <a href="<?= $appUrl ?>/products/show?id=<?= $r['product_id'] ?>" target="_blank"
                         class="text-xs font-medium text-cyan-600 dark:text-cyan-400 no-underline hover:underline"
                         title="<?= htmlspecialchars($r['product_title'] ?? '', ENT_QUOTES) ?>">
                        #<?= $r['product_id'] ?> (Xem)
                      </a>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="py-4 px-5 min-w-[220px]">
                  <div class="text-xs font-black text-red-600 dark:text-red-400 mb-1"><?= htmlspecialchars($r['reason'], ENT_QUOTES) ?></div>
                  <div class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed line-clamp-2">
                    <?= nl2br(htmlspecialchars($r['description'], ENT_QUOTES)) ?>
                  </div>
                  <?php if ($r['admin_note']): ?>
                    <div class="mt-2 bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 text-indigo-700 dark:text-indigo-400 px-2.5 py-1.5 rounded-lg text-[11px] font-medium">
                      <i class="bi bi-info-circle mr-1"></i><?= htmlspecialchars($r['admin_note']) ?>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($r['evidence_url'])): ?>
                    <a href="<?= $appUrl ?>/admin/reports/evidence?id=<?= (int)$r['id'] ?>" target="_blank"
                       class="inline-flex items-center gap-1 mt-1.5 px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-dark-2 text-gray-600 dark:text-gray-300 text-[10px] font-bold no-underline hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                      <i class="bi bi-paperclip"></i> Xem bằng chứng đính kèm
                    </a>
                  <?php endif; ?>
                </td>
                <td class="py-4 px-5 whitespace-nowrap">
                  <?php if ($r['status'] === 'pending'): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold text-amber-700 bg-amber-100"><i class="bi bi-hourglass-split"></i>Chờ xử lý</span>
                  <?php elseif ($r['status'] === 'resolved'): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold text-green-700 bg-green-100"><i class="bi bi-check-circle"></i>Đã xử lý</span>
                  <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold text-gray-600 bg-gray-100"><i class="bi bi-x-circle"></i>Bỏ qua</span>
                  <?php endif; ?>
                </td>
                <td class="py-4 px-5 text-xs font-medium text-gray-400 whitespace-nowrap">
                  <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
                </td>
                <td class="py-4 px-5 text-right whitespace-nowrap">
                  <?php if ($r['status'] === 'pending'): ?>
                    <div class="flex items-center justify-end gap-2">
                      <button onclick="openModal('resolveModal<?= $r['id'] ?>')"
                              class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-green-200 dark:border-green-800 text-green-600 hover:bg-green-500 hover:text-white hover:border-green-500 transition-all bg-transparent cursor-pointer" title="Xử lý">
                        <i class="bi bi-check2-all text-sm"></i>
                      </button>
                      <form action="<?= $appUrl ?>/admin/system-reports/resolve" method="POST" class="m-0">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <input type="hidden" name="status" value="ignored">
                        <button type="submit" onclick="return confirm('Bạn chắc chắn muốn bỏ qua tố cáo này chứ?');"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-400 hover:bg-gray-500 hover:text-white hover:border-gray-500 transition-all bg-transparent cursor-pointer" title="Bỏ qua">
                          <i class="bi bi-x text-sm"></i>
                        </button>
                      </form>
                    </div>
                  <?php else: ?>
                    <span class="text-xs text-gray-400 font-medium">Đã đóng</span>
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

<!-- Modals Xử lý Report -->
<?php if (!empty($reports)): ?>
  <?php foreach ($reports as $r): ?>
    <?php if ($r['status'] === 'pending'): ?>
    <div id="resolveModal<?= $r['id'] ?>" class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200">
      <div class="bg-white dark:bg-dark-card rounded-[22px] shadow-[0_40px_100px_rgba(0,0,0,0.3)] p-8 w-full max-w-[480px] transform scale-95 opacity-0 transition-all duration-300 m-4">
        <div class="w-14 h-14 rounded-[16px] bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white text-2xl shadow-[0_8px_24px_rgba(16,185,129,0.4)] mb-4">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="text-xl font-black text-gray-800 dark:text-dark-text mb-1">Xử lý Tố cáo #<?= $r['id'] ?></div>
        <p class="text-sm text-gray-500 mb-6">Tố cáo này sẽ được đánh dấu là <strong class="text-green-600">Đã xử lý</strong>. Bạn có thể để lại chú thích bên dưới để tham khảo về sau.</p>

        <form action="<?= $appUrl ?>/admin/system-reports/resolve" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <input type="hidden" name="status" value="resolved">

          <div class="mb-5">
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Đính kèm bằng chứng (Tùy chọn)</label>
            <input type="file" name="evidence" accept="image/*"
                   class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all cursor-pointer">
            <div class="text-xs text-gray-400 mt-1.5">Upload ảnh chụp màn hình bằng chứng (nếu có).</div>
          </div>

          <div class="mb-6">
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Ghi chú xử lý (Tùy chọn)</label>
            <textarea name="admin_note" rows="3"
                      placeholder="Ví dụ: Đã xóa bài đăng và cảnh cáo User via Email..."
                      class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-green-500 focus:ring-4 focus:ring-green-500/10 text-sm font-medium text-gray-800 dark:text-dark-text resize-y transition-all"></textarea>
          </div>

          <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 text-white font-extrabold text-sm hover:shadow-[0_8px_24px_rgba(16,185,129,0.4)] hover:-translate-y-0.5 transition-all border-0 cursor-pointer mb-2">
            Xác nhận Đã Xử Lý
          </button>
          <button type="button" class="w-full py-3 rounded-xl bg-gray-100 dark:bg-dark-2 text-gray-500 dark:text-gray-400 font-bold text-sm hover:bg-gray-200 dark:hover:bg-dark-border transition-colors border-0 cursor-pointer"
                  onclick="closeModal('resolveModal<?= $r['id'] ?>')">Hủy</button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  <?php endforeach; ?>
<?php endif; ?>

<script>
function openModal(id) {
  const modal = document.getElementById(id);
  const box = modal.querySelector('div.transform');
  modal.classList.remove('hidden'); modal.classList.add('flex');
  void modal.offsetWidth;
  modal.classList.remove('opacity-0'); box.classList.remove('scale-95', 'opacity-0');
}
function closeModal(id) {
  const modal = document.getElementById(id);
  const box = modal.querySelector('div.transform');
  modal.classList.add('opacity-0'); box.classList.add('scale-95', 'opacity-0');
  setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 300);
}
document.querySelectorAll('[id^="resolveModal"]').forEach(modal => {
  modal.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); });
});
</script>
