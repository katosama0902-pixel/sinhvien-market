<?php
/**
 * Wishlist Index View — Danh sách sản phẩm yêu thích
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
?>

<div class="container mx-auto px-4 py-8">

  <h1 class="text-2xl font-extrabold text-gray-800 dark:text-dark-text mb-6 flex items-center gap-2">
    <i class="bi bi-heart-fill text-red-500"></i>Danh sách yêu thích
  </h1>

  <?php if (empty($products)): ?>
    <!-- Empty state -->
    <div class="text-center py-20">
      <i class="bi bi-heart text-6xl text-gray-200 dark:text-gray-600 block mb-4"></i>
      <div class="text-lg font-bold text-gray-400 dark:text-gray-500 mb-3">Chưa có sản phẩm yêu thích</div>
      <a href="<?= $appUrl ?>/products"
         class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-primary text-white font-bold text-sm no-underline hover:brightness-110 transition-all shadow-lg shadow-primary/30">
        <i class="bi bi-search"></i>Khám phá sản phẩm
      </a>
    </div>

  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($products as $p): ?>
        <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col">

          <!-- Product image -->
          <?php if ($p['image']): ?>
            <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($p['image'], ENT_QUOTES) ?>"
                 class="w-full object-cover" style="height:180px"
                 alt="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>">
          <?php else: ?>
            <div class="flex items-center justify-center bg-gray-50 dark:bg-dark-2" style="height:180px">
              <i class="bi bi-image text-4xl text-gray-300 dark:text-gray-600"></i>
            </div>
          <?php endif; ?>

          <!-- Card body -->
          <div class="p-4 flex flex-col flex-1">
            <!-- Category badge -->
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold mb-2 self-start"
                  style="background:rgba(99,102,241,.1);color:#6366f1">
              <?= htmlspecialchars($p['category_name'], ENT_QUOTES) ?>
            </span>

            <!-- Title -->
            <h5 class="text-sm font-bold text-gray-800 dark:text-dark-text leading-snug mb-2 flex-1">
              <a href="<?= $appUrl ?>/products/show?id=<?= $p['product_id'] ?>"
                 class="no-underline hover:text-primary transition-colors">
                <?= htmlspecialchars($p['title'], ENT_QUOTES) ?>
              </a>
            </h5>

            <!-- Price -->
            <div class="font-extrabold text-primary text-base mb-1">
              <?php if ($p['type'] === 'auction'): ?>
                <i class="bi bi-graph-down-arrow mr-1 text-sm"></i>
                <?= number_format($p['current_price'] ?? 0, 0, ',', '.') ?>đ
                <span class="text-xs font-normal text-gray-400 ml-1">(hiện tại)</span>
              <?php else: ?>
                <?= number_format($p['current_price'], 0, ',', '.') ?>đ
              <?php endif; ?>
            </div>

            <!-- Seller -->
            <div class="text-xs text-gray-400 mb-4">
              <i class="bi bi-person mr-1"></i><?= htmlspecialchars($p['seller_name'], ENT_QUOTES) ?>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 mt-auto">
              <a href="<?= $appUrl ?>/products/show?id=<?= $p['product_id'] ?>"
                 class="flex-1 text-center py-2 rounded-full bg-primary text-white text-xs font-bold no-underline hover:brightness-110 transition-all">
                Xem chi tiết
              </a>
              <button class="wishlist-remove-btn w-9 h-9 flex items-center justify-center rounded-full border-2 border-red-200 text-red-400 hover:bg-red-50 hover:border-red-400 hover:text-red-500 transition-all bg-white dark:bg-dark-card cursor-pointer"
                      data-id="<?= $p['product_id'] ?>"
                      data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>"
                      title="Xóa khỏi yêu thích">
                <i class="bi bi-heart-fill text-sm"></i>
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
document.querySelectorAll('.wishlist-remove-btn').forEach(btn => {
  btn.addEventListener('click', async function() {
    const productId = this.dataset.id;
    const csrf      = this.dataset.csrf;
    const card      = this.closest('.bg-white, .dark\\:bg-dark-card') ?? this.closest('[class*="rounded-2xl"]');
    try {
      const res  = await fetch('<?= $appUrl ?>/wishlist/toggle', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `product_id=${productId}&_csrf=${csrf}`
      });
      const data = await res.json();
      if (data.success) {
        const col = card?.closest('[class*="grid"] > div') ?? card;
        col?.remove();
      }
    } catch(e) { console.error(e); }
  });
});
</script>
