<?php
/**
 * Admin View: Quản lý Banner Trang chủ — Tailwind Edition
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

$uploadPath = __DIR__ . '/../../../public/uploads/banners/';
if (!is_dir($uploadPath)) {
    @mkdir($uploadPath, 0777, true);
}

use Core\Flash;
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="font-sans antialiased text-gray-800 dark:text-dark-text">
  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h3 class="text-2xl font-extrabold text-gray-800 dark:text-dark-text flex items-center gap-2 mb-1 m-0">
        <i class="bi bi-images text-primary"></i>Quản lý Banner
      </h3>
      <p class="text-sm text-gray-500 dark:text-gray-400 m-0">Tùy chỉnh Slider hiển thị trên đầu trang chủ.</p>
    </div>
    <button onclick="openModal('addBannerModal')"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-white font-bold text-sm hover:brightness-110 hover:-translate-y-0.5 hover:shadow-md transition-all border-0 cursor-pointer shadow-sm whitespace-nowrap">
      <i class="bi bi-plus-lg"></i>Thêm Banner Mới
    </button>
  </div>

  <?= Flash::render() ?>

  <!-- Table Card -->
  <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm animate-[fadeInUp_0.4s_ease-out_both]">
    <div class="px-6 py-4 border-b border-light-border dark:border-dark-border bg-gray-50/50 dark:bg-dark-2/50">
      <h5 class="font-extrabold text-sm text-gray-800 dark:text-dark-text m-0">Danh sách Banner</h5>
    </div>

    <?php if (empty($banners)): ?>
      <div class="p-16 text-center flex flex-col items-center gap-3">
        <i class="bi bi-image text-5xl text-gray-200 dark:text-gray-700"></i>
        <p class="m-0 text-sm font-medium text-gray-400">Chưa có banner nào. Hãy thêm một banner mới để trang chủ sinh động hơn.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[700px]">
          <thead>
            <tr class="bg-gray-50/30 dark:bg-dark-2/30 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-light-border dark:border-dark-border">
              <th class="py-3.5 px-5" style="width:80px">Sắp xếp</th>
              <th class="py-3.5 px-5" style="width:220px">Hình ảnh</th>
              <th class="py-3.5 px-5">Thông tin</th>
              <th class="py-3.5 px-5" style="width:130px">Trạng thái</th>
              <th class="py-3.5 px-5 text-right" style="width:130px">Hành động</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
            <?php foreach ($banners as $b): ?>
              <tr class="hover:bg-gray-50/50 dark:hover:bg-dark-2/50 transition-colors">
                <td class="py-4 px-5 text-center">
                  <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-gray-100 dark:bg-dark-2 text-gray-700 dark:text-gray-200 font-black text-base">
                    <?= (int)$b['display_order'] ?>
                  </span>
                </td>
                <td class="py-4 px-5">
                  <div class="w-[180px] h-[68px] rounded-xl overflow-hidden bg-gray-100 dark:bg-dark-2 relative shadow-sm">
                    <img src="<?= htmlspecialchars($appUrl . $b['image'], ENT_QUOTES) ?>"
                         alt="Banner"
                         class="w-full h-full object-cover">
                  </div>
                </td>
                <td class="py-4 px-5">
                  <div class="font-bold text-sm text-gray-800 dark:text-dark-text mb-1"><?= htmlspecialchars($b['title'] ?? 'Không tên', ENT_QUOTES) ?></div>
                  <?php if (!empty($b['link'])): ?>
                    <a href="<?= htmlspecialchars($b['link'], ENT_QUOTES) ?>" target="_blank"
                       class="text-xs font-medium text-indigo-500 no-underline hover:underline flex items-center gap-1">
                      <i class="bi bi-link-45deg"></i><?= htmlspecialchars($b['link'], ENT_QUOTES) ?>
                    </a>
                  <?php else: ?>
                    <span class="text-xs text-gray-400 italic">Không có link đính kèm</span>
                  <?php endif; ?>
                </td>
                <td class="py-4 px-5">
                  <form method="POST" action="<?= $appUrl ?>/admin/banners/toggle" class="m-0">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all border-0 cursor-pointer w-[100px] justify-center
                              <?= $b['is_active']
                                  ? 'bg-green-500 text-white hover:bg-green-600 shadow-[0_4px_12px_rgba(16,185,129,0.3)]'
                                  : 'bg-gray-100 dark:bg-dark-2 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-dark-border border border-gray-200 dark:border-dark-border' ?>">
                      <?= $b['is_active']
                          ? '<i class="bi bi-eye-fill"></i>Hiển thị'
                          : '<i class="bi bi-eye-slash-fill"></i>Đang Ẩn' ?>
                    </button>
                  </form>
                </td>
                <td class="py-4 px-5 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button type="button" title="Sửa"
                            onclick="openEditModal(<?= htmlspecialchars(json_encode([
                              'id' => $b['id'],
                              'title' => $b['title'],
                              'link' => $b['link'],
                              'display_order' => $b['display_order']
                            ]), ENT_QUOTES) ?>)"
                            class="w-9 h-9 rounded-xl border border-indigo-200 dark:border-indigo-800 text-indigo-500 hover:bg-indigo-500 hover:text-white hover:border-indigo-500 transition-all bg-transparent cursor-pointer flex items-center justify-center">
                      <i class="bi bi-pencil-square text-sm"></i>
                    </button>
                    <form method="POST" action="<?= $appUrl ?>/admin/banners/delete" class="m-0"
                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn Banner này?');">
                      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                      <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                      <button type="submit" title="Xóa"
                              class="w-9 h-9 rounded-xl border border-red-200 dark:border-red-800 text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all bg-transparent cursor-pointer flex items-center justify-center">
                        <i class="bi bi-trash-fill text-sm"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- ─── Modal Thêm Banner ──────────────────────────────────────────────── -->
<div id="addBannerModal" class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200">
  <div class="bg-white dark:bg-dark-card rounded-[22px] shadow-[0_40px_100px_rgba(0,0,0,0.3)] p-8 w-full max-w-[500px] transform scale-95 opacity-0 transition-all duration-300 m-4">
    <div class="w-14 h-14 rounded-[16px] bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-2xl shadow-[0_8px_24px_rgba(99,102,241,0.4)] mb-4">
      <i class="bi bi-plus-circle"></i>
    </div>
    <div class="text-xl font-black text-gray-800 dark:text-dark-text mb-1">Thêm Banner Mới</div>
    <div class="text-sm font-medium text-gray-500 mb-6">Upload hình ảnh slider trang chủ</div>

    <form method="POST" action="<?= $appUrl ?>/admin/banners/store" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <div class="mb-4">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Hình ảnh (Tỉ lệ khuyên dùng 2:1) <span class="text-red-500">*</span></label>
        <input type="file" name="image" accept="image/*" required
               class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
      </div>
      <div class="mb-4">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Tiêu đề nội bộ</label>
        <input type="text" name="title" placeholder="Ví dụ: Sự kiện giảm giá Mùa Hè"
               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-indigo-500 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
      </div>
      <div class="mb-4">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Link đích (Khi click vào banner)</label>
        <input type="text" name="link_url" placeholder="https://... hoặc /products"
               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-indigo-500 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
      </div>
      <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
          <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Thứ tự (Số nhỏ xếp trước)</label>
          <input type="number" name="display_order" value="0"
                 class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-indigo-500 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Trạng thái</label>
          <select name="is_active"
                  class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-indigo-500 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
            <option value="1">Hiển thị ngay</option>
            <option value="0">Tạm ẩn</option>
          </select>
        </div>
      </div>
      <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-extrabold text-sm hover:shadow-[0_8px_24px_rgba(99,102,241,0.4)] hover:-translate-y-0.5 transition-all border-0 cursor-pointer mb-2">
        Tải lên
      </button>
      <button type="button" class="w-full py-3 rounded-xl bg-gray-100 dark:bg-dark-2 text-gray-500 dark:text-gray-400 font-bold text-sm hover:bg-gray-200 dark:hover:bg-dark-border transition-colors border-0 cursor-pointer" onclick="closeModal('addBannerModal')">Hủy</button>
    </form>
  </div>
</div>

<!-- ─── Modal Sửa Banner ───────────────────────────────────────────────── -->
<div id="editBannerModal" class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200">
  <div class="bg-white dark:bg-dark-card rounded-[22px] shadow-[0_40px_100px_rgba(0,0,0,0.3)] p-8 w-full max-w-[460px] transform scale-95 opacity-0 transition-all duration-300 m-4">
    <div class="w-14 h-14 rounded-[16px] bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center text-white text-2xl shadow-[0_8px_24px_rgba(6,182,212,0.4)] mb-4">
      <i class="bi bi-pencil-square"></i>
    </div>
    <div class="text-xl font-black text-gray-800 dark:text-dark-text mb-6">Chỉnh sửa thông tin Banner</div>

    <form method="POST" action="<?= $appUrl ?>/admin/banners/update">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="id" id="edit_id">
      <div class="mb-4">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Tiêu đề nội bộ</label>
        <input type="text" name="title" id="edit_title"
               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-cyan-500 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
      </div>
      <div class="mb-4">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Link đích</label>
        <input type="text" name="link_url" id="edit_link"
               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-cyan-500 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
      </div>
      <div class="mb-6">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Thứ tự</label>
        <input type="number" name="display_order" id="edit_display_order"
               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-cyan-500 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
      </div>
      <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-extrabold text-sm hover:shadow-[0_8px_24px_rgba(6,182,212,0.4)] hover:-translate-y-0.5 transition-all border-0 cursor-pointer mb-2">
        Lưu thay đổi
      </button>
      <button type="button" class="w-full py-3 rounded-xl bg-gray-100 dark:bg-dark-2 text-gray-500 dark:text-gray-400 font-bold text-sm hover:bg-gray-200 dark:hover:bg-dark-border transition-colors border-0 cursor-pointer" onclick="closeModal('editBannerModal')">Hủy</button>
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
function openEditModal(data) {
  document.getElementById('edit_id').value = data.id;
  document.getElementById('edit_title').value = data.title || '';
  document.getElementById('edit_link').value = data.link || '';
  document.getElementById('edit_display_order').value = data.display_order || 0;
  openModal('editBannerModal');
}
document.querySelectorAll('[id$="Modal"]').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) closeModal(m.id); });
});
</script>
