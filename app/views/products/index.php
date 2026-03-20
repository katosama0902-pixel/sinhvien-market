<?php
/**
 * View: Danh sách sản phẩm
 * Biến nhận: $products, $categories, $keyword, $categoryId, $page
 */
$appUrl  = rtrim($_ENV['APP_URL'] ?? '', '/');
$user    = $_SESSION['user'] ?? null;
use Core\Flash;

function formatPrice(int $price): string {
    return number_format($price, 0, ',', '.') . 'đ';
}

$statusLabel = [
    'pending'   => ['Chờ duyệt',  'warning'],
    'active'    => ['Đang bán',   'success'],
    'sold'      => ['Đã bán',     'secondary'],
    'cancelled' => ['Đã thu hồi','danger'],
];
?>

<div class="container py-4">
  <div class="row g-4">

    <!-- ─── Sidebar lọc ────────────────────────────────── -->
    <div class="col-lg-3 fade-in-up delay-100">
      <div class="card-sv glass-card p-4 sticky-top" style="top:76px; border:2px solid var(--border)">
        <h6 class="fw-800 mb-3"><i class="bi bi-funnel me-2 text-primary"></i>Lọc sản phẩm</h6>
        <form method="GET" action="<?= $appUrl ?>/products">
          <!-- Tìm kiếm -->
          <div class="mb-3">
            <label class="form-label small fw-600">Từ khóa</label>
            <input type="text" name="q" class="form-control form-control-sm"
                   placeholder="Tìm sách, đồ dùng..."
                   value="<?= htmlspecialchars($keyword, ENT_QUOTES) ?>">
          </div>

          <!-- Danh mục -->
          <div class="mb-3">
            <label class="form-label small fw-600">Danh mục</label>
            <select name="category" class="form-select form-select-sm">
              <option value="0">-- Tất cả --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"
                  <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <button class="btn btn-primary btn-sm w-100" type="submit">
            <i class="bi bi-search me-1"></i>Tìm kiếm
          </button>

          <?php if ($keyword || $categoryId): ?>
            <a href="<?= $appUrl ?>/products" class="btn btn-outline-secondary btn-sm w-100 mt-2">
              <i class="bi bi-x me-1"></i>Xóa bộ lọc
            </a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <!-- ─── Danh sách sản phẩm ─────────────────────────── -->
    <div class="col-lg-9">

      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="section-title mb-0">
          <?php if ($keyword): ?>
            Kết quả tìm kiếm: <em>"<?= htmlspecialchars($keyword, ENT_QUOTES) ?>"</em>
          <?php elseif ($categoryId): ?>
            <?php foreach ($categories as $c): ?>
              <?php if ($c['id'] == $categoryId): echo htmlspecialchars($c['name'], ENT_QUOTES); endif; ?>
            <?php endforeach; ?>
          <?php else: ?>
            Tất cả sản phẩm
          <?php endif; ?>
        </h5>
        <?php if ($user): ?>
          <a href="<?= $appUrl ?>/products/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Đăng bán
          </a>
        <?php endif; ?>
      </div>

      <!-- Grid sản phẩm -->
      <?php if (empty($products)): ?>
        <!-- Empty State -->
        <div class="empty-state text-center py-5">
          <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/bag-x.svg"
               width="80" alt="" class="mb-3 opacity-25">
          <h5 class="text-muted">Chưa có sản phẩm nào</h5>
          <p class="text-muted small">
            <?= $keyword ? 'Không tìm thấy kết quả phù hợp. Thử từ khóa khác?' : 'Hãy là người đầu tiên đăng sản phẩm!' ?>
          </p>
          <?php if ($user): ?>
            <a href="<?= $appUrl ?>/products/create" class="btn btn-primary mt-2">
              <i class="bi bi-plus-lg me-1"></i>Đăng bán ngay
            </a>
          <?php endif; ?>
        </div>

      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($products as $idx => $p): ?>
            <div class="col-sm-6 col-xl-4 fade-in-up" style="animation-delay: <?= $idx * 50 ?>ms">
              <a href="<?= $appUrl ?>/products/show?id=<?= $p['id'] ?>" class="text-decoration-none">
                <div class="card-sv h-100 hover-lift">
                  <!-- Ảnh -->
                  <div style="height:180px;overflow:hidden;position:relative;background:#f1f3f9">
                    <?php if ($p['image']): ?>
                      <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($p['image'], ENT_QUOTES) ?>"
                           alt="<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>"
                           style="width:100%;height:100%;object-fit:cover">
                    <?php else: ?>
                      <div class="d-flex align-items-center justify-content-center h-100">
                        <i class="bi bi-image text-muted" style="font-size:3rem;opacity:.3"></i>
                      </div>
                    <?php endif; ?>

                    <!-- Badge loại -->
                    <span class="position-absolute top-0 start-0 m-2">
                      <?php if ($p['type'] === 'auction'): ?>
                        <span class="badge-auction fs-xs px-2 py-1">
                          <i class="bi bi-lightning-fill me-1"></i>Đấu giá
                        </span>
                      <?php elseif ($p['type'] === 'exchange'): ?>
                        <span class="badge bg-info text-white">Trao đổi</span>
                      <?php endif; ?>
                    </span>
                  </div>

                  <!-- Nội dung -->
                  <div class="p-3">
                    <p class="small text-muted mb-1">
                      <i class="bi bi-tag me-1"></i><?= htmlspecialchars($p['category_name'], ENT_QUOTES) ?>
                    </p>
                    <h6 class="fw-600 mb-2 text-dark" style="line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                      <?= htmlspecialchars($p['title'], ENT_QUOTES) ?>
                    </h6>

                    <!-- Giá -->
                    <div class="d-flex justify-content-between align-items-center">
                      <?php if ($p['type'] === 'auction'): ?>
                        <span class="price-badge auction-price" id="price-<?= $p['id'] ?>">
                          <?= formatPrice($p['current_price'] ?? $p['start_price']) ?>
                        </span>
                        <small class="text-muted"><i class="bi bi-arrow-down-circle text-danger me-1"></i>Giảm dần</small>
                      <?php elseif ($p['type'] === 'sale'): ?>
                        <span class="price-badge"><?= formatPrice((int)$p['price']) ?></span>
                      <?php else: ?>
                        <span class="badge bg-info text-white fs-6">Trao đổi</span>
                      <?php endif; ?>
                    </div>

                    <div class="mt-2 small text-muted">
                      <i class="bi bi-person me-1"></i><?= htmlspecialchars($p['seller_name'], ENT_QUOTES) ?>
                      <span class="ms-2"><i class="bi bi-clock me-1"></i><?= date('d/m', strtotime($p['created_at'])) ?></span>
                    </div>
                  </div>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Phân trang đơn giản -->
        <div class="d-flex justify-content-center gap-2 mt-4">
          <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
               class="btn btn-outline-primary btn-sm">← Trước</a>
          <?php endif; ?>
          <?php if (count($products) >= 12): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
               class="btn btn-outline-primary btn-sm">Tiếp →</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>
