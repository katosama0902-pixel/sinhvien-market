<?php
/**
 * View: Danh sách sản phẩm — Feature 3: Bộ lọc nâng cao + huy hiệu
 * Biến nhận: $products, $categories, $keyword, $categoryId, $condition, $priceMin, $priceMax, $page
 */
$appUrl  = rtrim($_ENV['APP_URL'] ?? '', '/');
$user    = $_SESSION['user'] ?? null;
use Core\Flash;

function formatPrice(int $price): string {
    return number_format($price, 0, ',', '.') . 'đ';
}

$conditionMap = [
    'new'      => ['🟢 Mới 100%',            'success'],
    'like_new' => ['🔵 Như mới (90%+)',       'primary'],
    'used'     => ['🟡 Đã qua sử dụng',       'warning'],
    'worn'     => ['🔴 Cũ & có dấu vết',      'danger'],
];
$statusLabel = [
    'pending'   => ['Chờ duyệt',  'warning'],
    'active'    => ['Đang bán',   'success'],
    'sold'      => ['Đã bán',     'secondary'],
    'cancelled' => ['Đã thu hồi','danger'],
];
// Detect active filters
$hasFilter = $keyword || $categoryId || $condition || $priceMin || $priceMax;
?>

<div class="container py-4">
  <div class="row g-4">

    <!-- ─── Sidebar lọc ────────────────────────────────── -->
    <div class="col-lg-3 fade-in-up delay-100">
      <div class="card-sv glass-card p-4 sticky-top" style="top:76px; border:2px solid var(--border)">
        <h6 class="fw-800 mb-3"><i class="bi bi-funnel me-2 text-primary"></i>Lọc sản phẩm</h6>
        <form method="GET" action="<?= $appUrl ?>/products" id="filterForm">
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

          <!-- Feature 3: Tình trạng sản phẩm -->
          <div class="mb-3">
            <label class="form-label small fw-600">Tình trạng</label>
            <div class="d-flex flex-column gap-1">
              <?php foreach ($conditionMap as $val => [$label, $color]): ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="condition"
                         id="cond_<?= $val ?>" value="<?= $val ?>"
                         <?= ($condition ?? '') === $val ? 'checked' : '' ?>
                         onchange="this.form.submit()">
                  <label class="form-check-label small" for="cond_<?= $val ?>">
                    <?= $label ?>
                  </label>
                </div>
              <?php endforeach; ?>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="condition"
                       id="cond_all" value=""
                       <?= ($condition ?? '') === '' ? 'checked' : '' ?>
                       onchange="this.form.submit()">
                <label class="form-check-label small" for="cond_all">⚪ Tất cả</label>
              </div>
            </div>
          </div>

          <!-- Feature 3: Khoảng giá -->
          <div class="mb-3">
            <label class="form-label small fw-600">Khoảng giá (VNĐ)</label>
            <div class="row g-1">
              <div class="col-6">
                <input type="number" name="price_min" class="form-control form-control-sm"
                       placeholder="Từ" min="0" step="1000"
                       value="<?= $priceMin > 0 ? $priceMin : '' ?>">
              </div>
              <div class="col-6">
                <input type="number" name="price_max" class="form-control form-control-sm"
                       placeholder="Đến" min="0" step="1000"
                       value="<?= $priceMax > 0 ? $priceMax : '' ?>">
              </div>
            </div>
          </div>

          <button class="btn btn-primary btn-sm w-100" type="submit">
            <i class="bi bi-search me-1"></i>Tìm kiếm
          </button>

          <?php if ($hasFilter): ?>
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
          <?php if (!empty($condition) && is_string($condition) && isset($conditionMap[$condition])): ?>
            <span class="badge bg-<?= $conditionMap[$condition][1] ?> ms-2 fs-xs"><?= $conditionMap[$condition][0] ?></span>
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

                    <!-- Feature 3: Badge tình trạng -->
                    <?php if (!empty($p['condition']) && isset($conditionMap[$p['condition']])): ?>
                      <span class="position-absolute top-0 end-0 m-2">
                        <span class="badge bg-<?= $conditionMap[$p['condition']][1] ?>" style="font-size:.65rem">
                          <?= $conditionMap[$p['condition']][0] ?>
                        </span>
                      </span>
                    <?php endif; ?>
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

                    <!-- Feature 1: Tên người bán + badge xác thực -->
                    <div class="mt-2 small text-muted d-flex align-items-center gap-1">
                      <i class="bi bi-person me-1"></i>
                      <?= htmlspecialchars($p['seller_name'], ENT_QUOTES) ?>
                      <?php if (!empty($p['seller_verified'])): ?>
                        <span title="Sinh viên đã xác thực" style="color:#22c55e;font-size:.8rem">🛡️</span>
                      <?php endif; ?>
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



