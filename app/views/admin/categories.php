<?php
/**
 * Admin View: Quản lý danh mục (CRUD) — Tailwind Edition
 * Biến: $categories
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
use Core\Flash;

$icons = [
  'bi-book'       => '📖 Sách / Giáo trình',
  'bi-laptop'     => '💻 Điện tử',
  'bi-bicycle'    => '🚲 Phương tiện',
  'bi-backpack'   => '🎒 Đồ dùng học tập',
  'bi-house'      => '🏠 Đồ gia dụng',
  'bi-music-note' => '🎵 Nhạc cụ',
  'bi-brush'      => '🎨 Dụng cụ nghệ thuật',
  'bi-controller' => '🎮 Giải trí',
  'bi-bag'        => '👜 Thời trang',
  'bi-box-seam'   => '📦 Khác',
];
?>

<div class="font-sans antialiased text-gray-800 dark:text-dark-text">
  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <h4 class="text-xl font-extrabold text-gray-800 dark:text-dark-text flex items-center gap-2 m-0">
      <i class="bi bi-tags text-cyan-500"></i>Quản lý danh mục
    </h4>
    <button onclick="openModal('addModal')"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-primary text-white font-bold text-sm hover:brightness-110 hover:-translate-y-0.5 hover:shadow-md transition-all border-0 cursor-pointer shadow-sm">
      <i class="bi bi-plus-lg"></i>Thêm danh mục
    </button>
  </div>

  <?= Flash::render() ?>

  <!-- Table Card -->
  <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm animate-[fadeInUp_0.4s_ease-out_both]">
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse min-w-[600px]">
        <thead>
          <tr class="bg-gray-50 dark:bg-dark-2 border-b border-light-border dark:border-dark-border text-[11px] font-bold text-gray-500 uppercase tracking-wider">
            <th class="py-3.5 px-5">#</th>
            <th class="py-3.5 px-5">Icon</th>
            <th class="py-3.5 px-5">Tên danh mục</th>
            <th class="py-3.5 px-5">Slug</th>
            <th class="py-3.5 px-5">Ngày tạo</th>
            <th class="py-3.5 px-5 text-right" style="width:180px">Hành động</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
          <?php foreach ($categories as $i => $cat): ?>
            <tr class="hover:bg-gray-50/50 dark:hover:bg-dark-2/50 transition-colors">
              <td class="py-4 px-5 text-xs font-bold text-gray-400"><?= $i + 1 ?></td>
              <td class="py-4 px-5">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center">
                  <i class="bi <?= htmlspecialchars($cat['icon'] ?? 'bi-tag', ENT_QUOTES) ?> text-indigo-600 dark:text-indigo-400 text-lg"></i>
                </div>
              </td>
              <td class="py-4 px-5 font-bold text-sm text-gray-800 dark:text-dark-text"><?= htmlspecialchars($cat['name'], ENT_QUOTES) ?></td>
              <td class="py-4 px-5">
                <code class="text-xs font-mono font-bold bg-gray-100 dark:bg-dark-2 text-indigo-600 dark:text-indigo-400 px-2.5 py-1 rounded-lg">
                  <?= htmlspecialchars($cat['slug'], ENT_QUOTES) ?>
                </code>
              </td>
              <td class="py-4 px-5 text-xs font-medium text-gray-400 whitespace-nowrap"><?= date('d/m/Y', strtotime($cat['created_at'])) ?></td>
              <td class="py-4 px-5">
                <div class="flex items-center justify-end gap-2">
                  <!-- Sửa -->
                  <button onclick="openModal('editModal<?= $cat['id'] ?>')"
                          class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-indigo-200 dark:border-indigo-800 text-indigo-500 hover:bg-indigo-500 hover:text-white hover:border-indigo-500 transition-all bg-transparent cursor-pointer" title="Sửa">
                    <i class="bi bi-pencil text-sm"></i>
                  </button>
                  <!-- Xóa -->
                  <form method="POST" action="<?= $appUrl ?>/admin/categories/delete" class="m-0"
                        onsubmit="return confirm('Xóa danh mục «<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>»?\nDanh mục có sản phẩm sẽ không xóa được.')">
                    <input type="hidden" name="_csrf"        value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                    <input type="hidden" name="category_id"  value="<?= $cat['id'] ?>">
                    <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-200 dark:border-red-800 text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all bg-transparent cursor-pointer" title="Xóa">
                      <i class="bi bi-trash text-sm"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ─── Modal Thêm mới ─────────────────────────────────────────────────── -->
<div id="addModal" class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200">
  <div class="bg-white dark:bg-dark-card rounded-[22px] shadow-[0_40px_100px_rgba(0,0,0,0.3)] p-8 w-full max-w-[460px] transform scale-95 opacity-0 transition-all duration-300 m-4">
    <div class="w-14 h-14 rounded-[16px] bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-2xl shadow-[0_8px_24px_rgba(99,102,241,0.4)] mb-4">
      <i class="bi bi-plus-circle"></i>
    </div>
    <div class="text-xl font-black text-gray-800 dark:text-dark-text mb-1">Thêm danh mục mới</div>
    <div class="text-sm font-medium text-gray-500 mb-6">Tạo danh mục sản phẩm cho marketplace</div>

    <form method="POST" action="<?= $appUrl ?>/admin/categories/store">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
      <div class="mb-5">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">
          Tên danh mục <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" required placeholder="VD: Giáo trình"
               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
      </div>
      <div class="mb-6">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Icon Bootstrap</label>
        <select name="icon"
                class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-indigo-500 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
          <?php foreach ($icons as $cls => $label): ?>
            <option value="<?= $cls ?>"><?= $label ?> (<?= $cls ?>)</option>
          <?php endforeach; ?>
        </select>
        <div class="text-xs text-gray-400 mt-2">Xem thêm tại <a href="https://icons.getbootstrap.com" target="_blank" class="text-indigo-500 hover:underline">icons.getbootstrap.com</a></div>
      </div>
      <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-extrabold text-sm hover:shadow-[0_8px_24px_rgba(99,102,241,0.4)] hover:-translate-y-0.5 transition-all border-0 cursor-pointer mb-2">
        <i class="bi bi-plus-lg mr-2"></i>Tạo danh mục
      </button>
      <button type="button" class="w-full py-3 rounded-xl bg-gray-100 dark:bg-dark-2 text-gray-500 dark:text-gray-400 font-bold text-sm hover:bg-gray-200 dark:hover:bg-dark-border transition-colors border-0 cursor-pointer" onclick="closeModal('addModal')">
        Hủy
      </button>
    </form>
  </div>
</div>

<!-- ─── Modals Sửa danh mục ───────────────────────────────────────────── -->
<?php if (!empty($categories)): ?>
  <?php foreach ($categories as $cat): ?>
    <div id="editModal<?= $cat['id'] ?>" class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200">
      <div class="bg-white dark:bg-dark-card rounded-[22px] shadow-[0_40px_100px_rgba(0,0,0,0.3)] p-8 w-full max-w-[460px] transform scale-95 opacity-0 transition-all duration-300 m-4">
        <div class="w-14 h-14 rounded-[16px] bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center text-white text-2xl shadow-[0_8px_24px_rgba(6,182,212,0.4)] mb-4">
          <i class="bi bi-pencil-square"></i>
        </div>
        <div class="text-xl font-black text-gray-800 dark:text-dark-text mb-1">Sửa danh mục</div>
        <div class="text-sm font-medium text-gray-500 mb-6"><?= htmlspecialchars($cat['name'], ENT_QUOTES) ?></div>

        <form method="POST" action="<?= $appUrl ?>/admin/categories/update">
          <input type="hidden" name="_csrf"        value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
          <input type="hidden" name="category_id"  value="<?= $cat['id'] ?>">
          <div class="mb-5">
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Tên danh mục</label>
            <input type="text" name="name" required
                   value="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                   class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
          </div>
          <div class="mb-6">
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Icon Bootstrap</label>
            <select name="icon"
                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-cyan-500 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
              <?php foreach ($icons as $cls => $label): ?>
                <option value="<?= $cls ?>" <?= $cat['icon'] === $cls ? 'selected' : '' ?>>
                  <?= $label ?> (<?= $cls ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 text-white font-extrabold text-sm hover:shadow-[0_8px_24px_rgba(6,182,212,0.4)] hover:-translate-y-0.5 transition-all border-0 cursor-pointer mb-2">
            Lưu thay đổi
          </button>
          <button type="button" class="w-full py-3 rounded-xl bg-gray-100 dark:bg-dark-2 text-gray-500 dark:text-gray-400 font-bold text-sm hover:bg-gray-200 dark:hover:bg-dark-border transition-colors border-0 cursor-pointer" onclick="closeModal('editModal<?= $cat['id'] ?>')">
            Hủy
          </button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<script>
function openModal(id) {
  const modal = document.getElementById(id);
  const box = modal.querySelector('div.transform');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  void modal.offsetWidth;
  modal.classList.remove('opacity-0');
  box.classList.remove('scale-95', 'opacity-0');
}
function closeModal(id) {
  const modal = document.getElementById(id);
  const box = modal.querySelector('div.transform');
  modal.classList.add('opacity-0');
  box.classList.add('scale-95', 'opacity-0');
  setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 300);
}
// Close on backdrop click
document.querySelectorAll('[id$="Modal"]').forEach(modal => {
  modal.addEventListener('click', function(e) {
    if (e.target === this) closeModal(this.id);
  });
});
</script>
