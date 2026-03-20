<?php
/**
 * Admin View: Kiểm duyệt bài đăng
 * Biến: $products, $tab
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
use Core\Controller;
use Core\Flash;
$ctrl = new class extends Controller {};
$csrf = $ctrl->csrfToken();

$statusMap = [
    'pending'   => ['Chờ duyệt',   'warning'],
    'active'    => ['Đang bán',    'success'],
    'sold'      => ['Đã bán',      'dark'],
    'cancelled' => ['Đã từ chối', 'secondary'],
];
$typeMap = ['sale' => '💰 Bán', 'exchange' => '🔄 Trao đổi', 'auction' => '⚡ Đấu giá'];
?>
<div class="container-fluid py-4">
  <h4 class="fw-700 mb-4"><i class="bi bi-bag-check me-2 text-warning"></i>Kiểm duyệt bài đăng</h4>
  <?= Flash::render() ?>

  <!-- Tab -->
  <ul class="nav nav-tabs mb-3">
    <li class="nav-item">
      <a class="nav-link <?= $tab === 'pending' ? 'active' : '' ?>" href="<?= $appUrl ?>/admin/products?tab=pending">
        Chờ duyệt <?php if ($tab === 'pending'): ?><span class="badge bg-warning text-dark ms-1"><?= count($products) ?></span><?php endif; ?>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?= $tab === 'all' ? 'active' : '' ?>" href="<?= $appUrl ?>/admin/products?tab=all">
        Tất cả bài đăng
      </a>
    </li>
  </ul>

  <div class="card-sv">
    <?php if (empty($products)): ?>
      <div class="p-5 text-center text-muted">
        <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
        Không có bài đăng nào chờ duyệt ✅
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Sản phẩm</th>
              <th>Người đăng</th>
              <th>Loại</th>
              <th>Giá</th>
              <th>Trạng thái</th>
              <th>Ngày đăng</th>
              <th style="min-width:190px">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
              <?php [$statusLabel, $statusColor] = $statusMap[$p['status']] ?? ['?', 'secondary']; ?>
              <tr>
                <!-- SP -->
                <td>
                  <div class="fw-600" style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <a href="<?= $appUrl ?>/products/show?id=<?= $p['id'] ?>" class="text-dark text-decoration-none" target="_blank">
                      <?= htmlspecialchars($p['title'], ENT_QUOTES) ?>
                    </a>
                  </div>
                  <div class="small text-muted"><?= htmlspecialchars($p['category_name'], ENT_QUOTES) ?></div>
                </td>
                <!-- Người đăng -->
                <td>
                  <div class="small fw-600"><?= htmlspecialchars($p['seller_name'], ENT_QUOTES) ?></div>
                  <div class="small text-muted"><?= htmlspecialchars($p['seller_email'], ENT_QUOTES) ?></div>
                </td>
                <!-- Loại -->
                <td><span class="badge bg-secondary"><?= $typeMap[$p['type']] ?? '?' ?></span></td>
                <!-- Giá -->
                <td class="small fw-600 text-nowrap">
                  <?= $p['price'] ? number_format((int)$p['price'], 0, ',', '.') . 'đ' : '—' ?>
                </td>
                <!-- Trạng thái -->
                <td><span class="badge bg-<?= $statusColor ?>"><?= $statusLabel ?></span></td>
                <!-- Ngày -->
                <td class="small text-muted text-nowrap"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                <!-- Actions -->
                <td>
                  <div class="d-flex gap-1 flex-wrap">
                    <?php if ($p['status'] === 'pending'): ?>
                      <!-- Duyệt -->
                      <form method="POST" action="<?= $appUrl ?>/admin/products/approve">
                        <input type="hidden" name="_csrf"       value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                        <input type="hidden" name="product_id"  value="<?= $p['id'] ?>">
                        <button class="btn btn-sm btn-success" title="Duyệt">
                          <i class="bi bi-check-lg me-1"></i>Duyệt
                        </button>
                      </form>
                      <!-- Từ chối -->
                      <form method="POST" action="<?= $appUrl ?>/admin/products/reject"
                            onsubmit="return confirm('Từ chối bài này?')">
                        <input type="hidden" name="_csrf"       value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                        <input type="hidden" name="product_id"  value="<?= $p['id'] ?>">
                        <button class="btn btn-sm btn-warning">
                          <i class="bi bi-x-lg me-1"></i>Từ chối
                        </button>
                      </form>
                    <?php endif; ?>
                    <?php if ($p['status'] !== 'sold'): ?>
                      <!-- Xóa -->
                      <form method="POST" action="<?= $appUrl ?>/admin/products/delete"
                            onsubmit="return confirm('Xóa bài đăng này? Hành động được ghi vào audit log.')">
                        <input type="hidden" name="_csrf"       value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                        <input type="hidden" name="product_id"  value="<?= $p['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" title="Xóa">
                          <i class="bi bi-trash"></i>
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
