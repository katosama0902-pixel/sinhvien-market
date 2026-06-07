<?php
/**
 * View: Sản phẩm của tôi
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
use Core\Controller;
use Core\Flash;

$ctrl = new class extends Controller {};
$csrf = $ctrl->csrfToken();

$statusMap = [
    'pending'   => ['Chờ duyệt',  'bg-yellow-100 text-yellow-700 border-yellow-200',  'bi-hourglass-split'],
    'active'    => ['Đang bán',   'bg-green-100 text-green-700 border-green-200',     'bi-check-circle'],
    'sold'      => ['Đã bán',     'bg-gray-100 text-gray-600 border-gray-200',        'bi-bag-check-fill'],
    'cancelled' => ['Đã thu hồi','bg-red-50 text-red-500 border-red-200',            'bi-x-circle'],
];

$typeMap = [
    'sale'     => ['Bán thường',    'bg-indigo-100 text-indigo-700'],
    'exchange' => ['Trao đổi',      'bg-cyan-100 text-cyan-700'],
    'auction'  => ['Đấu giá ngược', 'bg-red-100 text-red-600'],
];
?>

<div class="container mx-auto px-4 py-8">

  <!-- Page header -->
  <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <h1 class="text-2xl font-extrabold text-gray-800 dark:text-dark-text flex items-center gap-2">
      <i class="bi bi-grid text-primary"></i>Sản phẩm của tôi
    </h1>
    <a href="<?= $appUrl ?>/products/create"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-white font-bold text-sm no-underline hover:brightness-110 transition-all shadow shadow-primary/30">
      <i class="bi bi-plus-lg"></i>Đăng bán mới
    </a>
  </div>

  <?= Flash::render() ?>

  <?php if (empty($products)): ?>
    <!-- Empty state -->
    <div class="text-center py-20">
      <i class="bi bi-bag-plus text-6xl text-gray-200 dark:text-gray-600 block mb-4"></i>
      <div class="text-lg font-bold text-gray-400 mb-1">Bạn chưa đăng sản phẩm nào</div>
      <p class="text-sm text-gray-400 mb-4">Hãy bắt đầu bằng cách đăng sản phẩm đầu tiên của bạn!</p>
      <a href="<?= $appUrl ?>/products/create"
         class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-primary text-white font-bold text-sm no-underline hover:brightness-110 transition-all">
        <i class="bi bi-plus-lg"></i>Đăng bán ngay
      </a>
    </div>

  <?php else: ?>
    <!-- Table -->
    <div class="overflow-x-auto rounded-2xl border border-light-border dark:border-dark-border">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-dark-2 border-b border-light-border dark:border-dark-border">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider w-10">#</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Sản phẩm</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Loại</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Giá</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Trạng thái</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Ngày đăng</th>
            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider w-32">Hành động</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
          <?php foreach ($products as $i => $p): ?>
            <?php
              [$statusLabel, $statusCls, $statusIcon] = $statusMap[$p['status']] ?? ['?', 'bg-gray-100 text-gray-500 border-gray-200', 'bi-dash'];
              [$typeLabel,   $typeCls]                = $typeMap[$p['type']]     ?? ['?', 'bg-gray-100 text-gray-500'];
            ?>
            <tr class="bg-white dark:bg-dark-card hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors">

              <!-- STT -->
              <td class="px-4 py-3 text-gray-400"><?= $i + 1 ?></td>

              <!-- Ảnh + Tên -->
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-13 h-13 rounded-xl overflow-hidden flex-shrink-0 bg-gray-100 dark:bg-dark-2"
                       style="width:52px;height:52px">
                    <?php if ($p['image']): ?>
                      <img src="<?= $appUrl ?>/products/image?id=<?= (int)$p['id'] ?>"
                           class="w-full h-full object-cover">
                    <?php else: ?>
                      <div class="w-full h-full flex items-center justify-center">
                        <i class="bi bi-image text-gray-300 dark:text-gray-600 text-xl"></i>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div>
                    <a href="<?= $appUrl ?>/products/show?id=<?= $p['id'] ?>"
                       class="font-semibold text-gray-800 dark:text-dark-text no-underline hover:text-primary transition-colors block max-w-xs truncate">
                      <?= htmlspecialchars($p['title'], ENT_QUOTES) ?>
                    </a>
                    <span class="text-xs text-gray-400"><?= htmlspecialchars($p['category_name'], ENT_QUOTES) ?></span>
                  </div>
                </div>
              </td>

              <!-- Loại -->
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold <?= $typeCls ?>">
                  <?= $typeLabel ?>
                </span>
              </td>

              <!-- Giá -->
              <td class="px-4 py-3 font-semibold text-gray-800 dark:text-dark-text whitespace-nowrap">
                <?php if ($p['type'] === 'sale'): ?>
                  <?= number_format((int)$p['price'], 0, ',', '.') ?>đ
                <?php elseif ($p['type'] === 'auction'): ?>
                  <?= $p['start_price'] ? number_format((int)$p['start_price'], 0, ',', '.').'đ' : '—' ?>
                  <br><span class="text-xs text-gray-400">→ Sàn: <?= $p['floor_price'] ? number_format((int)$p['floor_price'], 0, ',', '.').'đ' : '—' ?></span>
                <?php else: ?>
                  <span class="text-gray-400">—</span>
                <?php endif; ?>
              </td>

              <!-- Trạng thái -->
              <td class="px-4 py-3">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold border <?= $statusCls ?>">
                  <i class="bi <?= $statusIcon ?>"></i><?= $statusLabel ?>
                </span>
                <?php if ($p['status'] === 'pending'): ?>
                  <div class="text-xs text-gray-400 mt-1">Đang chờ Admin duyệt</div>
                <?php endif; ?>
              </td>

              <!-- Ngày đăng -->
              <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">
                <?= date('d/m/Y', strtotime($p['created_at'])) ?>
              </td>

              <!-- Hành động -->
              <td class="px-4 py-3">
                <div class="flex flex-col gap-1.5">
                  <div class="flex gap-1">
                    <a href="<?= $appUrl ?>/products/show?id=<?= $p['id'] ?>"
                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-primary text-primary hover:bg-primary hover:text-white transition-all no-underline"
                       title="Xem">
                      <i class="bi bi-eye text-sm"></i>
                    </a>
                    <?php if (!in_array($p['status'], ['sold', 'cancelled'])): ?>
                      <button onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>')"
                              class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-red-300 text-red-400 hover:bg-red-500 hover:text-white hover:border-red-500 transition-all cursor-pointer bg-transparent"
                              title="Thu hồi">
                        <i class="bi bi-trash text-sm"></i>
                      </button>
                    <?php endif; ?>
                  </div>
                  <?php if ($p['status'] === 'active'): ?>
                    <button onclick="confirmBump(<?= $p['id'] ?>, '<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>')"
                            class="flex items-center justify-center gap-1 px-2 py-1 rounded-lg text-white text-xs font-bold cursor-pointer border-0 transition-all"
                            style="background:linear-gradient(135deg,#f59e0b,#ea580c);box-shadow:0 2px 4px rgba(234,88,12,.2)"
                            title="Tốn 50 xu để đẩy tin lên đầu"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                      <i class="bi bi-rocket-takeoff"></i>Đẩy tin (50 xu)
                    </button>
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

<!-- Hidden forms -->
<form id="deleteForm" action="<?= $appUrl ?>/products/delete" method="POST" class="hidden">
  <input type="hidden" name="_csrf"      value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
  <input type="hidden" name="product_id" id="deleteProductId" value="">
</form>
<form id="bumpForm" action="<?= $appUrl ?>/coins/bump" method="POST" class="hidden">
  <input type="hidden" name="_csrf"      value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
  <input type="hidden" name="product_id" id="bumpProductId" value="">
</form>

<script>
function confirmDelete(id, title) {
  if (!confirm('Thu hồi bài đăng "' + title + '"?\n\nBài đăng sẽ bị ẩn khỏi danh sách sản phẩm.')) return;
  document.getElementById('deleteProductId').value = id;
  document.getElementById('deleteForm').submit();
}
function confirmBump(id, title) {
  if (!confirm('Đẩy tin "' + title + '" lên đầu?\n\nHành động này sẽ tiêu tốn 50 xu.')) return;
  document.getElementById('bumpProductId').value = id;
  document.getElementById('bumpForm').submit();
}
</script>
