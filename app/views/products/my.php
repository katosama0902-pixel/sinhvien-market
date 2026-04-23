<?php
/**
 * View: Sản phẩm của tôi
 * Biến nhận: $products
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
use Core\Controller;
use Core\Flash;

$ctrl = new class extends Controller {};
$csrf = $ctrl->csrfToken();

$statusMap = [
    'pending'   => ['Chờ duyệt',   'warning',   'bi-hourglass-split'],
    'active'    => ['Đang bán',    'success',   'bi-check-circle'],
    'sold'      => ['Đã bán',      'dark',      'bi-bag-check-fill'],
    'cancelled' => ['Đã thu hồi', 'secondary', 'bi-x-circle'],
];

$typeMap = [
    'sale'     => ['Bán thường',    'primary'],
    'exchange' => ['Trao đổi',      'info'],
    'auction'  => ['Đấu giá ngược', 'danger'],
];
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="section-title mb-0"><i class="bi bi-grid me-2 text-primary"></i>Sản phẩm của tôi</h4>
    <a href="<?= $appUrl ?>/products/create" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i>Đăng bán mới
    </a>
  </div>

  <?= Flash::render() ?>

  <?php if (empty($products)): ?>
    <div class="empty-state text-center py-5">
      <img src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/bag-plus.svg"
           width="80" alt="" class="mb-3 opacity-25">
      <h5 class="text-muted">Bạn chưa đăng sản phẩm nào</h5>
      <p class="text-muted small">Hãy bắt đầu bằng cách đăng sản phẩm đầu tiên của bạn!</p>
      <a href="<?= $appUrl ?>/products/create" class="btn btn-primary mt-2">
        <i class="bi bi-plus-lg me-1"></i>Đăng bán ngay
      </a>
    </div>

  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead style="background:var(--bg)">
          <tr>
            <th style="width:50px">#</th>
            <th>Sản phẩm</th>
            <th>Loại</th>
            <th>Giá</th>
            <th>Trạng thái</th>
            <th>Ngày đăng</th>
            <th style="width:130px">Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($products as $i => $p): ?>
            <?php
              [$statusLabel, $statusColor, $statusIcon] = $statusMap[$p['status']] ?? ['?', 'secondary', 'bi-dash'];
              [$typeLabel,   $typeColor]                = $typeMap[$p['type']]     ?? ['?', 'secondary'];
            ?>
            <tr>
              <!-- STT -->
              <td class="text-muted small"><?= $i + 1 ?></td>

              <!-- Ảnh + Tên -->
              <td>
                <div class="d-flex align-items-center gap-3">
                  <div style="width:52px;height:52px;flex-shrink:0;border-radius:10px;overflow:hidden;background:var(--img-placeholder)">
                    <?php if ($p['image']): ?>
                      <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($p['image'], ENT_QUOTES) ?>"
                           style="width:100%;height:100%;object-fit:cover">
                    <?php else: ?>
                      <div class="d-flex align-items-center justify-content-center h-100">
                        <i class="bi bi-image text-muted" style="font-size:1.4rem;opacity:.4"></i>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div>
                    <a href="<?= $appUrl ?>/products/show?id=<?= $p['id'] ?>"
                       class="fw-600 text-decoration-none d-block"
                       style="max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text)">
                      <?= htmlspecialchars($p['title'], ENT_QUOTES) ?>
                    </a>
                    <small class="text-muted"><?= htmlspecialchars($p['category_name'], ENT_QUOTES) ?></small>
                  </div>
                </div>
              </td>

              <!-- Loại -->
              <td><span class="badge bg-<?= $typeColor ?>"><?= $typeLabel ?></span></td>

              <!-- Giá -->
              <td class="fw-600 text-nowrap">
                <?php if ($p['type'] === 'sale'): ?>
                  <?= number_format((int)$p['price'], 0, ',', '.') ?>đ
                <?php elseif ($p['type'] === 'auction'): ?>
                  <?= $p['start_price'] ? number_format((int)$p['start_price'], 0, ',', '.') . 'đ' : '—' ?>
                  <br><small class="text-muted">→ Sàn: <?= $p['floor_price'] ? number_format((int)$p['floor_price'], 0, ',', '.') . 'đ' : '—' ?></small>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>

              <!-- Trạng thái -->
              <td>
                <span class="badge bg-<?= $statusColor ?>">
                  <i class="bi <?= $statusIcon ?> me-1"></i><?= $statusLabel ?>
                </span>
                <?php if ($p['status'] === 'pending'): ?>
                  <br><small class="text-muted d-block mt-1">Đang chờ Admin duyệt</small>
                <?php endif; ?>
              </td>

              <!-- Ngày đăng -->
              <td class="small text-muted text-nowrap">
                <?= date('d/m/Y', strtotime($p['created_at'])) ?>
              </td>

              <!-- Hành động -->
              <td>
                <div class="d-flex flex-column gap-2">
                  <div class="d-flex gap-1 justify-content-center">
                    <a href="<?= $appUrl ?>/products/show?id=<?= $p['id'] ?>"
                       class="btn btn-sm btn-outline-primary px-2" title="Xem">
                      <i class="bi bi-eye"></i>
                    </a>
                    <?php if (!in_array($p['status'], ['sold', 'cancelled'])): ?>
                      <button type="button" class="btn btn-sm btn-outline-danger px-2" title="Thu hồi"
                              onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>')">
                        <i class="bi bi-trash"></i>
                      </button>
                    <?php endif; ?>
                  </div>
                  <?php if ($p['status'] === 'active'): ?>
                    <button type="button" class="btn btn-sm text-white px-2 py-1 flex-grow-1"
                            style="background:linear-gradient(135deg, #f59e0b, #ea580c); font-size:0.75rem; font-weight:600; border:none; border-radius:6px; box-shadow:0 2px 4px rgba(234,88,12,0.2)"
                            title="Tốn 50 xu để đẩy tin lên đầu"
                            onclick="confirmBump(<?= $p['id'] ?>, '<?= htmlspecialchars($p['title'], ENT_QUOTES) ?>')">
                      <i class="bi bi-rocket-takeoff me-1"></i>Đẩy tin (50 xu)
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

<!-- Form xóa ẩn -->
<form id="deleteForm" action="<?= $appUrl ?>/products/delete" method="POST" style="display:none">
  <input type="hidden" name="_csrf"       value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
  <input type="hidden" name="product_id"  id="deleteProductId" value="">
</form>

<!-- Form đẩy tin ẩn -->
<form id="bumpForm" action="<?= $appUrl ?>/coins/bump" method="POST" style="display:none">
  <input type="hidden" name="_csrf"       value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
  <input type="hidden" name="product_id"  id="bumpProductId" value="">
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
