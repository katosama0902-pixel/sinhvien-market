<?php
/**
 * View: Danh sách sản phẩm
 */
$appUrl  = rtrim($_ENV['APP_URL'] ?? '', '/');
$user    = $_SESSION['user'] ?? null;
use Core\Flash;

function formatPrice(int $price): string {
    return number_format($price, 0, ',', '.') . 'đ';
}

$conditionMap = [
    'new'      => ['🟢 Mới 100%',            'text-green-600 bg-green-100'],
    'like_new' => ['🔵 Như mới (90%+)',       'text-blue-600 bg-blue-100'],
    'used'     => ['🟡 Đã qua sử dụng',       'text-yellow-600 bg-yellow-100'],
    'worn'     => ['🔴 Cũ & có dấu vết',      'text-red-600 bg-red-100'],
];

// Detect active filters
$hasFilter = $keyword || $categoryId || $condition || $priceMin || $priceMax;
?>

<div class="container mx-auto px-4 py-8">
  <div class="flex flex-col lg:flex-row gap-6">

    <!-- ─── Sidebar lọc ────────────────────────────────── -->
    <div class="lg:w-1/4 flex-shrink-0 animate-[fadeInUp_0.5s_ease-out_0.1s_both]">
      <div class="bg-white/80 dark:bg-dark-card/80 backdrop-blur-xl rounded-2xl p-5 sticky top-24 border-2 border-light-border dark:border-dark-border shadow-sm">
        <h6 class="font-extrabold text-gray-800 dark:text-dark-text mb-4 flex items-center gap-2">
          <i class="bi bi-funnel text-primary"></i>Lọc sản phẩm
        </h6>
        
        <form method="GET" action="<?= $appUrl ?>/products" id="filterForm">
          <!-- Tìm kiếm -->
          <div class="mb-4">
            <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Từ khóa</label>
            <input type="text" name="q" placeholder="Tìm sách, đồ dùng..."
                   value="<?= htmlspecialchars($keyword, ENT_QUOTES) ?>"
                   class="w-full px-3 py-2 text-sm rounded-xl border border-light-border dark:border-dark-border bg-gray-50 dark:bg-dark-2 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all">
          </div>

          <!-- Danh mục -->
          <div class="mb-4">
            <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Danh mục</label>
            <select name="category" class="w-full px-3 py-2 text-sm rounded-xl border border-light-border dark:border-dark-border bg-gray-50 dark:bg-dark-2 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all">
              <option value="0">-- Tất cả --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Tình trạng -->
          <div class="mb-4">
            <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Tình trạng</label>
            <div class="flex flex-col gap-2">
              <?php foreach ($conditionMap as $val => [$label, $color]): ?>
                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer group">
                  <input type="radio" name="condition" value="<?= $val ?>"
                         <?= ($condition ?? '') === $val ? 'checked' : '' ?>
                         onchange="this.form.submit()"
                         class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary focus:ring-2 cursor-pointer">
                  <span class="group-hover:text-primary transition-colors"><?= $label ?></span>
                </label>
              <?php endforeach; ?>
              <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer group">
                <input type="radio" name="condition" value=""
                       <?= ($condition ?? '') === '' ? 'checked' : '' ?>
                       onchange="this.form.submit()"
                       class="w-4 h-4 text-primary bg-gray-100 border-gray-300 focus:ring-primary focus:ring-2 cursor-pointer">
                <span class="group-hover:text-primary transition-colors">⚪ Tất cả</span>
              </label>
            </div>
          </div>

          <!-- Khoảng giá -->
          <div class="mb-5">
            <label class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wide">Khoảng giá (VNĐ)</label>
            <div class="flex gap-2">
              <input type="number" name="price_min" placeholder="Từ" min="0" step="1000"
                     value="<?= $priceMin > 0 ? $priceMin : '' ?>"
                     class="w-full px-3 py-2 text-sm rounded-xl border border-light-border dark:border-dark-border bg-gray-50 dark:bg-dark-2 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all">
              <input type="number" name="price_max" placeholder="Đến" min="0" step="1000"
                     value="<?= $priceMax > 0 ? $priceMax : '' ?>"
                     class="w-full px-3 py-2 text-sm rounded-xl border border-light-border dark:border-dark-border bg-gray-50 dark:bg-dark-2 outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all">
            </div>
          </div>

          <button type="submit" class="w-full py-2.5 rounded-xl bg-primary text-white font-bold text-sm hover:brightness-110 transition-all shadow-md shadow-primary/20">
            <i class="bi bi-search mr-1"></i>Tìm kiếm
          </button>

          <?php if ($hasFilter): ?>
            <a href="<?= $appUrl ?>/products" class="block w-full text-center py-2 mt-2 rounded-xl border-2 border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 font-bold text-sm no-underline hover:bg-gray-50 dark:hover:bg-dark-2 transition-all">
              <i class="bi bi-x mr-1"></i>Xóa bộ lọc
            </a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <!-- ─── Danh sách sản phẩm ─────────────────────────── -->
    <div class="lg:w-3/4">

      <!-- Header -->
      <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <h1 class="text-xl font-extrabold text-gray-800 dark:text-dark-text m-0 flex items-center flex-wrap gap-2">
          <?php if ($keyword): ?>
            Kết quả tìm kiếm: <em class="text-primary font-normal">"<?= htmlspecialchars($keyword, ENT_QUOTES) ?>"</em>
          <?php elseif ($categoryId): ?>
            <?php foreach ($categories as $c): ?>
              <?php if ($c['id'] == $categoryId): echo htmlspecialchars($c['name'], ENT_QUOTES); endif; ?>
            <?php endforeach; ?>
          <?php else: ?>
            Tất cả sản phẩm
          <?php endif; ?>

          <?php if (!empty($condition) && is_string($condition) && isset($conditionMap[$condition])): ?>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?= $conditionMap[$condition][1] ?>">
              <?= $conditionMap[$condition][0] ?>
            </span>
          <?php endif; ?>
        </h1>
        <?php if ($user): ?>
          <a href="<?= $appUrl ?>/products/create" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-white font-bold text-sm no-underline hover:brightness-110 transition-all shadow shadow-primary/20">
            <i class="bi bi-plus-lg"></i>Đăng bán
          </a>
        <?php endif; ?>
      </div>

      <!-- Grid sản phẩm -->
      <?php if (empty($products)): ?>
        <!-- Empty State -->
        <div class="text-center py-20 rounded-2xl bg-white dark:bg-dark-card border border-light-border dark:border-dark-border">
          <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/bag-x.svg"
               width="80" alt="" class="mb-4 opacity-25 mx-auto dark:invert">
          <h5 class="text-lg font-bold text-gray-500 dark:text-gray-400 mb-2">Chưa có sản phẩm nào</h5>
          <p class="text-sm text-gray-400 mb-4">
            <?= $keyword ? 'Không tìm thấy kết quả phù hợp. Thử từ khóa khác?' : 'Hãy là người đầu tiên đăng sản phẩm!' ?>
          </p>
          <?php if ($user): ?>
            <a href="<?= $appUrl ?>/products/create" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-primary text-white font-bold text-sm no-underline hover:brightness-110 transition-all">
              <i class="bi bi-plus-lg"></i>Đăng bán ngay
            </a>
          <?php endif; ?>
        </div>

      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
          <?php foreach ($products as $idx => $p): ?>
            <div class="animate-[fadeInUp_0.5s_ease-out_both]" style="animation-delay: <?= $idx * 50 ?>ms">
              <a href="<?= $appUrl ?>/products/show?id=<?= $p['id'] ?>" class="block h-full no-underline group">
                <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border overflow-hidden h-full transition-all duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-primary/5 flex flex-col">
                  
                  <!-- Ảnh -->
                  <div class="relative h-44 overflow-hidden bg-gray-50 dark:bg-dark-2">
                    <?php if ($p['image']): ?>
                      <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($p['image'], ENT_QUOTES) ?>"
                           alt="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>"
                           class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <?php else: ?>
                      <div class="w-full h-full flex items-center justify-center">
                        <i class="bi bi-image text-gray-300 dark:text-gray-600 text-5xl opacity-30"></i>
                      </div>
                    <?php endif; ?>

                    <!-- Type badges -->
                    <div class="absolute top-2 left-2">
                      <?php if ($p['type'] === 'auction'): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider text-white bg-gradient-to-r from-red-500 to-orange-500 shadow-md">
                          <i class="bi bi-lightning-fill"></i>Đấu giá
                        </span>
                      <?php elseif ($p['type'] === 'exchange'): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider text-white bg-cyan-500 shadow-md">
                          Trao đổi
                        </span>
                      <?php endif; ?>
                    </div>

                    <!-- Condition badge -->
                    <?php if (!empty($p['condition']) && isset($conditionMap[$p['condition']])): ?>
                      <div class="absolute top-2 right-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold shadow-sm <?= $conditionMap[$p['condition']][1] ?>">
                          <?= $conditionMap[$p['condition']][0] ?>
                        </span>
                      </div>
                    <?php endif; ?>
                  </div>

                  <!-- Nội dung -->
                  <div class="p-4 flex flex-col flex-1">
                    <p class="text-xs text-gray-400 mb-1.5 flex items-center gap-1">
                      <i class="bi bi-tag"></i><?= htmlspecialchars($p['category_name'], ENT_QUOTES) ?>
                    </p>
                    <h6 class="font-bold text-sm text-gray-800 dark:text-dark-text leading-snug mb-3 line-clamp-2 flex-1 group-hover:text-primary transition-colors">
                      <?= htmlspecialchars($p['title'], ENT_QUOTES) ?>
                    </h6>

                    <!-- Giá -->
                    <div class="flex justify-between items-end mb-3">
                      <?php if ($p['type'] === 'auction'): ?>
                        <div>
                          <span class="text-lg font-black text-red-600 dark:text-red-400 font-mono" id="price-<?= $p['id'] ?>">
                            <?= formatPrice($p['current_price'] ?? $p['start_price']) ?>
                          </span>
                          <div class="text-[10px] text-gray-400 flex items-center gap-1 mt-0.5"><i class="bi bi-arrow-down-circle text-red-500"></i>Giảm dần</div>
                        </div>
                      <?php elseif ($p['type'] === 'sale'): ?>
                        <span class="text-lg font-extrabold text-primary font-mono">
                          <?= formatPrice((int)$p['price']) ?>
                        </span>
                      <?php else: ?>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-cyan-100 text-cyan-700 text-xs font-bold">Trao đổi</span>
                      <?php endif; ?>
                    </div>

                    <!-- Người bán -->
                    <div class="mt-auto pt-3 border-t border-gray-50 dark:border-dark-border flex items-center justify-between text-xs text-gray-400">
                      <div class="flex items-center gap-1.5 truncate pr-2">
                        <i class="bi bi-person"></i>
                        <span class="truncate"><?= htmlspecialchars($p['seller_name'], ENT_QUOTES) ?></span>
                        <?php if (!empty($p['seller_verified'])): ?>
                          <span title="Sinh viên đã xác thực" class="text-green-500">🛡️</span>
                        <?php endif; ?>
                      </div>
                      <div class="flex items-center gap-1 flex-shrink-0">
                        <i class="bi bi-clock"></i><?= date('d/m', strtotime($p['created_at'])) ?>
                      </div>
                    </div>
                  </div>

                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Phân trang -->
        <div class="flex justify-center gap-2 mt-8">
          <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
               class="px-4 py-2 rounded-xl border border-primary text-primary font-bold text-sm hover:bg-primary hover:text-white transition-colors no-underline">
              ← Trước
            </a>
          <?php endif; ?>
          <?php if (count($products) >= 12): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
               class="px-4 py-2 rounded-xl border border-primary text-primary font-bold text-sm hover:bg-primary hover:text-white transition-colors no-underline">
              Tiếp →
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>
