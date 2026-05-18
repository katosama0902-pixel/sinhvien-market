<?php
/**
 * Admin View: Kiểm duyệt bài đăng — Tailwind Edition
 * Biến: $products, $tab
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
use Core\Controller;
use Core\Flash;
$ctrl = new class extends Controller {};
$csrf = $ctrl->csrfToken();

$statusMap = [
    'pending'   => ['Chờ duyệt',   'bg-amber-100 text-amber-700'],
    'active'    => ['Đang bán',    'bg-green-100 text-green-700'],
    'sold'      => ['Đã bán',      'bg-gray-100 text-gray-600'],
    'cancelled' => ['Đã từ chối', 'bg-red-100 text-red-600'],
];
$typeMap = ['sale' => '💰 Bán', 'exchange' => '🔄 Trao đổi', 'auction' => '⚡ Đấu giá'];
?>

<div class="font-sans antialiased text-gray-800 dark:text-dark-text">
  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <h4 class="text-xl font-extrabold text-gray-800 dark:text-dark-text flex items-center gap-2 m-0">
      <i class="bi bi-bag-check text-amber-500"></i>Kiểm duyệt bài đăng
    </h4>
    <button type="button"
            class="inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-white dark:bg-dark-card border border-gray-200 dark:border-dark-border text-green-600 font-bold text-sm shadow-sm hover:bg-gray-50 dark:hover:bg-dark-2 hover:-translate-y-0.5 hover:shadow-md transition-all border-0 cursor-pointer"
            onclick="openExportModal('products')">
      <i class="bi bi-file-earmark-excel-fill mr-2"></i> Xuất Excel (CSV)
    </button>
  </div>

  <?= Flash::render() ?>

  <!-- Tabs -->
  <div class="flex gap-1 mb-6 bg-gray-100 dark:bg-dark-2 p-1 rounded-xl w-fit">
    <a href="<?= $appUrl ?>/admin/products?tab=pending"
       class="px-5 py-2 rounded-lg text-sm font-bold no-underline transition-all <?= $tab === 'pending' ? 'bg-white dark:bg-dark-card text-gray-800 dark:text-dark-text shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' ?>">
      Chờ duyệt
      <?php if ($tab === 'pending'): ?>
        <span class="ml-1.5 bg-amber-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full"><?= count($products) ?></span>
      <?php endif; ?>
    </a>
    <a href="<?= $appUrl ?>/admin/products?tab=all"
       class="px-5 py-2 rounded-lg text-sm font-bold no-underline transition-all <?= $tab === 'all' ? 'bg-white dark:bg-dark-card text-gray-800 dark:text-dark-text shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' ?>">
      Tất cả bài đăng
    </a>
  </div>

  <!-- Table Card -->
  <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm animate-[fadeInUp_0.4s_ease-out_both]">
    <?php if (empty($products)): ?>
      <div class="p-16 text-center text-gray-400 flex flex-col items-center gap-3">
        <i class="bi bi-check-circle-fill text-green-500 text-5xl"></i>
        <p class="m-0 text-sm font-semibold">Không có bài đăng nào chờ duyệt ✅</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[900px]">
          <thead>
            <tr class="bg-gray-50 dark:bg-dark-2 border-b border-light-border dark:border-dark-border text-[11px] font-bold text-gray-500 uppercase tracking-wider">
              <th class="py-3.5 px-5">Sản phẩm</th>
              <th class="py-3.5 px-5">Người đăng</th>
              <th class="py-3.5 px-5">Loại</th>
              <th class="py-3.5 px-5">Giá</th>
              <th class="py-3.5 px-5">Trạng thái</th>
              <th class="py-3.5 px-5">Ngày đăng</th>
              <th class="py-3.5 px-5 min-w-[200px] text-right">Hành động</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
            <?php foreach ($products as $p): ?>
              <?php [$statusLabel, $statusCls] = $statusMap[$p['status']] ?? ['?', 'bg-gray-100 text-gray-500']; ?>
              <tr class="hover:bg-gray-50/50 dark:hover:bg-dark-2/50 transition-colors">
                <!-- Sản phẩm -->
                <td class="py-4 px-5">
                  <a href="<?= $appUrl ?>/products/show?id=<?= $p['id'] ?>" target="_blank"
                     class="font-bold text-sm text-gray-800 dark:text-dark-text no-underline hover:text-primary transition-colors block max-w-[240px] truncate">
                    <?= htmlspecialchars($p['title'], ENT_QUOTES) ?>
                  </a>
                  <div class="text-xs text-gray-400 mt-0.5 truncate max-w-[240px]"><?= htmlspecialchars($p['category_name'], ENT_QUOTES) ?></div>
                </td>
                <!-- Người đăng -->
                <td class="py-4 px-5">
                  <div class="text-sm font-bold text-gray-700 dark:text-gray-200"><?= htmlspecialchars($p['seller_name'], ENT_QUOTES) ?></div>
                  <div class="text-xs text-gray-400"><?= htmlspecialchars($p['seller_email'], ENT_QUOTES) ?></div>
                </td>
                <!-- Loại -->
                <td class="py-4 px-5 whitespace-nowrap">
                  <?php
                    $typeCls = ['sale' => 'bg-indigo-100 text-indigo-700', 'exchange' => 'bg-cyan-100 text-cyan-700', 'auction' => 'bg-amber-100 text-amber-700'];
                  ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $typeCls[$p['type']] ?? 'bg-gray-100 text-gray-600' ?>">
                    <?= $typeMap[$p['type']] ?? '?' ?>
                  </span>
                </td>
                <!-- Giá -->
                <td class="py-4 px-5 font-bold text-sm text-gray-700 dark:text-gray-200 whitespace-nowrap">
                  <?= $p['price'] ? number_format((int)$p['price'], 0, ',', '.') . 'đ' : '—' ?>
                </td>
                <!-- Trạng thái -->
                <td class="py-4 px-5 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $statusCls ?>">
                    <?= $statusLabel ?>
                  </span>
                </td>
                <!-- Ngày -->
                <td class="py-4 px-5 text-xs font-medium text-gray-400 whitespace-nowrap">
                  <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>
                </td>
                <!-- Actions -->
                <td class="py-4 px-5">
                  <div class="flex items-center justify-end gap-2 flex-wrap">
                    <?php if ($p['status'] === 'pending'): ?>
                      <!-- Duyệt -->
                      <form method="POST" action="<?= $appUrl ?>/admin/products/approve" class="m-0">
                        <input type="hidden" name="_csrf"      value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-gradient-to-br from-green-500 to-emerald-600 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgba(16,185,129,0.3)] transition-all border-0 cursor-pointer">
                          <i class="bi bi-check-lg"></i>Duyệt
                        </button>
                      </form>
                      <!-- Từ chối -->
                      <form method="POST" action="<?= $appUrl ?>/admin/products/reject" class="m-0"
                            onsubmit="return confirm('Từ chối bài này?')">
                        <input type="hidden" name="_csrf"      value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-gradient-to-br from-amber-500 to-orange-500 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgba(245,158,11,0.3)] transition-all border-0 cursor-pointer">
                          <i class="bi bi-x-lg"></i>Từ chối
                        </button>
                      </form>
                    <?php endif; ?>
                    <?php if ($p['status'] !== 'sold'): ?>
                      <!-- Xóa -->
                      <form method="POST" action="<?= $appUrl ?>/admin/products/delete" class="m-0"
                            onsubmit="return confirm('Xóa bài đăng này? Hành động được ghi vào audit log.')">
                        <input type="hidden" name="_csrf"      value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-400 border border-red-200 dark:border-red-800 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all bg-transparent cursor-pointer" title="Xóa">
                          <i class="bi bi-trash text-sm"></i>
                        </button>
                      </form>
                    <?php endif; ?>
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

<!-- ─── EXPORT MODAL ──────────────────────────────────────────────── -->
<div id="exportModal" class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200">
  <div class="bg-white dark:bg-dark-card rounded-[22px] shadow-[0_40px_100px_rgba(0,0,0,0.3)] p-8 w-full max-w-[460px] transform scale-95 opacity-0 transition-all duration-300 m-4">
    <div class="w-14 h-14 rounded-[16px] bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center text-white text-2xl shadow-[0_8px_24px_rgba(16,185,129,0.4)] mb-4">
      <i class="bi bi-file-earmark-excel-fill"></i>
    </div>
    <div class="text-xl font-black text-gray-800 dark:text-dark-text mb-1">Xuất Dữ Liệu</div>
    <div class="text-sm font-medium text-gray-500 mb-6">Chọn khoảng thời gian để xuất báo cáo</div>

    <form method="GET" action="<?= $appUrl ?>/admin/export">
      <input type="hidden" name="type" id="exportType" value="products">
      <div class="grid grid-cols-2 gap-4 mb-5">
        <div>
          <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Từ ngày</label>
          <input type="date" name="from" class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-emerald-500 text-sm font-medium transition-colors">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Đến ngày</label>
          <input type="date" name="to" class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-emerald-500 text-sm font-medium transition-colors">
        </div>
      </div>
      <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 text-blue-700 dark:text-blue-400 px-4 py-3 rounded-xl text-sm mb-6 flex items-start gap-2">
        <i class="bi bi-info-circle-fill mt-0.5 flex-shrink-0"></i>
        <span>Để trống nếu muốn xuất toàn bộ dữ liệu.</span>
      </div>
      <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white font-extrabold text-sm hover:shadow-[0_8px_24px_rgba(16,185,129,0.4)] hover:-translate-y-0.5 transition-all border-0 cursor-pointer mb-2">
        <i class="bi bi-download mr-2"></i>Tải file CSV
      </button>
      <button type="button" class="w-full py-3 rounded-xl bg-gray-100 dark:bg-dark-2 text-gray-500 dark:text-gray-400 font-bold text-sm hover:bg-gray-200 dark:hover:bg-dark-border transition-colors border-0 cursor-pointer" onclick="closeExportModal()">
        Hủy bỏ
      </button>
    </form>
  </div>
</div>

<script>
function openExportModal(type) {
  document.getElementById('exportType').value = type;
  const modal = document.getElementById('exportModal');
  const box = modal.querySelector('div.transform');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  void modal.offsetWidth;
  modal.classList.remove('opacity-0');
  box.classList.remove('scale-95', 'opacity-0');
}
function closeExportModal() {
  const modal = document.getElementById('exportModal');
  const box = modal.querySelector('div.transform');
  modal.classList.add('opacity-0');
  box.classList.add('scale-95', 'opacity-0');
  setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 300);
}
document.getElementById('exportModal').addEventListener('click', function(e) {
  if (e.target === this) closeExportModal();
});
</script>
