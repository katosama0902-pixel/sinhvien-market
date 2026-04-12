<?php
/**
 * View: Dashboard sinh viên — bài đăng + giao dịch của tôi
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$user   = $_SESSION['user'];
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

$totalBuyAmt  = 0;
$totalSellAmt = 0;
foreach ($transactions as $t) {
    if ((int)$t['buyer_id']  === (int)$user['id']) $totalBuyAmt  += $t['amount'];
    if ((int)$t['seller_id'] === (int)$user['id']) $totalSellAmt += $t['amount'];
}
?>

<div class="container py-4">

  <!-- Welcome banner -->
  <div class="welcome-card mb-4 p-4 rounded-4 d-flex align-items-center gap-4"
       style="background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff">
    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
         style="width:72px;height:72px;background:rgba(255,255,255,.2);font-size:2rem;font-weight:900">
      <?= mb_strtoupper(mb_substr($user['name'], 0, 1)) ?>
    </div>
    <div class="flex-grow-1">
      <div style="opacity:.8;font-size:.9rem">Xin chào,</div>
      <h4 class="fw-800 mb-1"><?= htmlspecialchars($user['name'], ENT_QUOTES) ?></h4>
      <div style="opacity:.75;font-size:.85rem"><?= htmlspecialchars($user['email'], ENT_QUOTES) ?></div>
    </div>
    <div class="d-none d-md-block text-end">
      <a href="<?= $appUrl ?>/products/create" class="btn btn-light fw-700">
        <i class="bi bi-plus-circle me-1"></i>Đăng bán mới
      </a>
    </div>
  </div>



  <!-- Stats -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card-sv p-3 text-center">
        <div class="small text-muted">Bài đăng</div>
        <div class="fw-800 fs-3 text-primary"><?= count($products) ?></div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card-sv p-3 text-center">
        <div class="small text-muted">Đã bán được</div>
        <div class="fw-800 fs-4 text-success"><?= number_format($totalSellAmt, 0, ',', '.') ?>đ</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card-sv p-3 text-center">
        <div class="small text-muted">Đã mua</div>
        <div class="fw-800 fs-4 text-danger"><?= number_format($totalBuyAmt, 0, ',', '.') ?>đ</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card-sv p-3 text-center">
        <div class="small text-muted">Giao dịch</div>
        <div class="fw-800 fs-3 text-info"><?= count($transactions) ?></div>
      </div>
    </div>
  </div>

  <div class="row g-4">

    <!-- ─── Sản phẩm của tôi ─────────────────────────────── -->
    <div class="col-lg-7">
      <div class="card-sv">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
          <h6 class="fw-700 mb-0"><i class="bi bi-bag me-2 text-primary"></i>Bài đăng của tôi</h6>
          <a href="<?= $appUrl ?>/products/my" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
        </div>

        <?php if (empty($products)): ?>
          <div class="p-4 text-center text-muted small">
            <i class="bi bi-bag-x d-block fs-2 mb-2 opacity-25"></i>
            Chưa có bài đăng. <a href="<?= $appUrl ?>/products/create">Đăng bán ngay!</a>
          </div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach (array_slice($products, 0, 5) as $p): ?>
              <?php [$statusLabel, $statusColor, $statusIcon] = $statusMap[$p['status']] ?? ['?','secondary','bi-dash']; ?>
              <div class="list-group-item d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;border-radius:8px;overflow:hidden;flex-shrink:0;background:#f1f3f9">
                  <?php if ($p['image']): ?>
                    <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($p['image'], ENT_QUOTES) ?>"
                         style="width:100%;height:100%;object-fit:cover">
                  <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center h-100">
                      <i class="bi bi-image opacity-25"></i>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="flex-grow-1 min-w-0">
                  <a href="<?= $appUrl ?>/products/show?id=<?= $p['id'] ?>"
                     class="fw-600 text-dark text-decoration-none d-block text-truncate">
                    <?= htmlspecialchars($p['title'], ENT_QUOTES) ?>
                  </a>
                  <div class="small text-muted"><?= date('d/m/Y', strtotime($p['created_at'])) ?></div>
                </div>
                <span class="badge bg-<?= $statusColor ?> flex-shrink-0">
                  <i class="bi <?= $statusIcon ?> me-1"></i><?= $statusLabel ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ─── Giao dịch gần đây ────────────────────────────── -->
    <div class="col-lg-5">
      <div class="card-sv">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
          <h6 class="fw-700 mb-0"><i class="bi bi-receipt me-2 text-success"></i>Giao dịch gần đây</h6>
          <a href="<?= $appUrl ?>/transactions/history" class="btn btn-sm btn-outline-success">Xem tất cả</a>
        </div>

        <?php if (empty($transactions)): ?>
          <div class="p-4 text-center text-muted small">
            <i class="bi bi-receipt-cutoff d-block fs-2 mb-2 opacity-25"></i>
            Chưa có giao dịch nào.
          </div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach (array_slice($transactions, 0, 6) as $t): ?>
              <?php $isBuyer = (int)$t['buyer_id'] === (int)$user['id']; ?>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div class="min-w-0">
                  <div class="small fw-600 text-truncate"
                       style="max-width:180px">
                    <?= htmlspecialchars($t['product_title'], ENT_QUOTES) ?>
                  </div>
                  <div class="small text-muted">
                    <?= $isBuyer ? 'Mua từ ' . htmlspecialchars($t['seller_name'], ENT_QUOTES)
                                 : 'Bán cho ' . htmlspecialchars($t['buyer_name'], ENT_QUOTES) ?>
                  </div>
                </div>
                <span class="fw-700 text-nowrap <?= $isBuyer ? 'text-danger' : 'text-success' ?>">
                  <?= $isBuyer ? '−' : '+' ?><?= number_format($t['amount'], 0, ',', '.') ?>đ
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Quick links -->
      <div class="mt-3 d-grid gap-2">
        <a href="<?= $appUrl ?>/products/create" class="btn btn-primary">
          <i class="bi bi-plus-circle me-2"></i>Đăng bán sản phẩm mới
        </a>
        <a href="<?= $appUrl ?>/products" class="btn btn-outline-secondary">
          <i class="bi bi-bag me-2"></i>Mua sắm thêm
        </a>
      </div>
    </div>

  </div>
</div>
