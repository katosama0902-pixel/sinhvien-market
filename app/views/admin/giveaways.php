<?php
/**
 * Admin View: Quản lý Giveaways — Tailwind Edition
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
?>

<div class="font-sans antialiased text-gray-800 dark:text-dark-text">
  <div class="flex items-center justify-between mb-6">
    <h4 class="text-xl font-extrabold text-gray-800 dark:text-dark-text flex items-center gap-2 m-0">
      <i class="bi bi-gift text-indigo-500"></i>Quản lý Giveaways
    </h4>
    <button onclick="openModal('createModal')"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-white font-bold text-sm hover:brightness-110 hover:-translate-y-0.5 hover:shadow-md transition-all border-0 cursor-pointer shadow-sm">
      <i class="bi bi-plus-circle"></i>Tạo Sự Kiện Mới
    </button>
  </div>

  <!-- Table Card -->
  <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm animate-[fadeInUp_0.4s_ease-out_both]">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse min-w-[650px]">
        <thead>
          <tr class="bg-gray-50 dark:bg-dark-2 border-b border-light-border dark:border-dark-border text-[11px] font-bold text-gray-500 uppercase tracking-wider">
            <th class="py-3.5 px-5">Tên Sự Kiện</th>
            <th class="py-3.5 px-5">Trạng thái</th>
            <th class="py-3.5 px-5">Hết Hạn</th>
            <th class="py-3.5 px-5">Người Trúng</th>
            <th class="py-3.5 px-5 text-right">Thao tác</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
          <?php if (empty($giveaways)): ?>
            <tr>
              <td colspan="5" class="py-16 text-center text-gray-400 text-sm font-medium">
                <i class="bi bi-gift text-4xl text-gray-200 dark:text-gray-700 block mb-3"></i>
                Chưa có sự kiện nào
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($giveaways as $ga):
              $isActive = $ga['status'] === 'active' && strtotime($ga['end_time']) > time();
              $isEndedWithoutWinner = $ga['status'] === 'active' && strtotime($ga['end_time']) <= time();
            ?>
              <tr class="hover:bg-gray-50/50 dark:hover:bg-dark-2/50 transition-colors">
                <td class="py-4 px-5">
                  <div class="flex items-center gap-3">
                    <?php if ($ga['image']): ?>
                      <img src="<?= $appUrl ?>/giveaways/image?id=<?= (int)$ga['id'] ?>"
                           class="w-10 h-10 rounded-xl object-cover flex-shrink-0 shadow-sm">
                    <?php else: ?>
                      <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center text-indigo-500 flex-shrink-0">
                        <i class="bi bi-gift"></i>
                      </div>
                    <?php endif; ?>
                    <span class="font-bold text-sm text-gray-800 dark:text-dark-text"><?= htmlspecialchars($ga['title']) ?></span>
                  </div>
                </td>
                <td class="py-4 px-5 whitespace-nowrap">
                  <?php if ($ga['status'] === 'ended'): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold text-gray-600 bg-gray-100">Đã kết thúc</span>
                  <?php elseif ($isEndedWithoutWinner): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold text-amber-700 bg-amber-100"><i class="bi bi-clock-history"></i> Chờ xổ số</span>
                  <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold text-green-700 bg-green-100"><i class="bi bi-broadcast"></i> Đang diễn ra</span>
                  <?php endif; ?>
                </td>
                <td class="py-4 px-5 text-xs font-medium text-gray-500 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($ga['end_time'])) ?></td>
                <td class="py-4 px-5">
                  <?php if ($ga['winner_id']): ?>
                    <span class="font-bold text-sm text-emerald-600 flex items-center gap-1.5">
                      <i class="bi bi-trophy-fill text-amber-500"></i><?= htmlspecialchars($ga['winner_name']) ?>
                    </span>
                  <?php else: ?>
                    <span class="text-xs text-gray-400">Chưa có</span>
                  <?php endif; ?>
                </td>
                <td class="py-4 px-5 text-right whitespace-nowrap">
                  <?php if ($ga['status'] === 'active'): ?>
                    <a href="<?= $appUrl ?>/admin/giveaway_spin?id=<?= $ga['id'] ?>"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-indigo-600 border-2 border-indigo-200 dark:border-indigo-700 hover:bg-indigo-500 hover:text-white hover:border-indigo-500 hover:-translate-y-0.5 transition-all no-underline">
                      <i class="bi bi-play-circle-fill"></i>Quay Số
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Thêm Mới -->
<div id="createModal" class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200">
  <div class="bg-white dark:bg-dark-card rounded-[22px] shadow-[0_40px_100px_rgba(0,0,0,0.3)] p-8 w-full max-w-[480px] transform scale-95 opacity-0 transition-all duration-300 m-4">
    <div class="w-14 h-14 rounded-[16px] bg-gradient-to-br from-indigo-500 to-pink-500 flex items-center justify-center text-white text-2xl shadow-[0_8px_24px_rgba(99,102,241,0.4)] mb-4">
      <i class="bi bi-gift-fill"></i>
    </div>
    <div class="text-xl font-black text-gray-800 dark:text-dark-text mb-1">Tạo Sự Kiện Giveaway Mới</div>
    <div class="text-sm font-medium text-gray-500 mb-6">Tổ chức quay số may mắn cho sinh viên</div>

    <form action="<?= $appUrl ?>/admin/giveaways/store" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($this->csrfToken()) ?>">
      <div class="mb-5">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Tiêu đề phần thưởng</label>
        <input type="text" name="title" required placeholder="Ví dụ: Giveaway: Bàn phím cơ DareU..."
               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
      </div>
      <div class="mb-5">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Mô tả chi tiết</label>
        <textarea name="description" rows="3"
                  class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 text-sm font-medium text-gray-800 dark:text-dark-text resize-y transition-all"></textarea>
      </div>
      <div class="mb-5">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Thời gian kết thúc</label>
        <input type="datetime-local" name="end_time" required min="<?= date('Y-m-d\TH:i') ?>"
               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-indigo-500 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
      </div>
      <div class="mb-6">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Hình ảnh quà tặng</label>
        <input type="file" name="image" accept="image/*"
               class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
      </div>
      <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-indigo-500 to-pink-500 text-white font-extrabold text-sm hover:shadow-[0_8px_24px_rgba(99,102,241,0.4)] hover:-translate-y-0.5 transition-all border-0 cursor-pointer mb-2">
        <i class="bi bi-check-lg mr-2"></i>Lưu Sự Kiện
      </button>
      <button type="button" class="w-full py-3 rounded-xl bg-gray-100 dark:bg-dark-2 text-gray-500 dark:text-gray-400 font-bold text-sm hover:bg-gray-200 dark:hover:bg-dark-border transition-colors border-0 cursor-pointer" onclick="closeModal('createModal')">Hủy</button>
    </form>
  </div>
</div>

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
document.querySelectorAll('[id$="Modal"]').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) closeModal(m.id); });
});
</script>
