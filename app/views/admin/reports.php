<?php
/**
 * Admin View: Báo cáo giao dịch
 * Biến: $transactions, $fromDate, $toDate, $totalAmount
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

// Tổng hợp theo ngày cho biểu đồ
$byDay = [];
foreach ($transactions as $t) {
    $day = date('d/m', strtotime($t['created_at']));
    $byDay[$day] = ($byDay[$day] ?? 0) + (int)$t['amount'];
}
$chartLabels = json_encode(array_keys($byDay));
$chartData   = json_encode(array_values($byDay));

$totalCount = count($transactions);
$avgAmount  = $totalCount ? (int)($totalAmount / $totalCount) : 0;
?>
<div class="container-fluid py-4">
  <h4 class="fw-700 mb-4"><i class="bi bi-bar-chart-fill me-2 text-success"></i>Báo cáo giao dịch</h4>

  <!-- Bộ lọc ngày -->
  <div class="card-sv p-3 mb-4">
    <form class="row g-3 align-items-end" method="GET" action="<?= $appUrl ?>/admin/reports">
      <div class="col-sm-4">
        <label class="form-label small fw-600">Từ ngày</label>
        <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($fromDate) ?>">
      </div>
      <div class="col-sm-4">
        <label class="form-label small fw-600">Đến ngày</label>
        <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($toDate) ?>">
      </div>
      <div class="col-sm-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Lọc</button>
        <a href="<?= $appUrl ?>/admin/reports" class="btn btn-outline-secondary">Reset</a>
      </div>
    </form>
  </div>

  <!-- Stats -->
  <div class="row g-3 mb-4">
    <div class="col-sm-4">
      <div class="card-sv p-3 text-center">
        <div class="small text-muted">Số giao dịch</div>
        <div class="fw-800 fs-3 text-primary"><?= number_format($totalCount) ?></div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card-sv p-3 text-center">
        <div class="small text-muted">Tổng giá trị</div>
        <div class="fw-800 fs-3 text-success"><?= number_format($totalAmount, 0, ',', '.') ?>đ</div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card-sv p-3 text-center">
        <div class="small text-muted">Giá trung bình</div>
        <div class="fw-800 fs-3 text-info"><?= number_format($avgAmount, 0, ',', '.') ?>đ</div>
      </div>
    </div>
  </div>

  <!-- Biểu đồ theo ngày -->
  <?php if (!empty($byDay)): ?>
    <div class="card-sv p-4 mb-4">
      <h6 class="fw-700 mb-3">Doanh số theo ngày (đ)</h6>
      <canvas id="myChart" height="80"></canvas>
    </div>
  <?php endif; ?>

  <!-- Bảng giao dịch -->
  <div class="card-sv">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
      <span class="small text-muted"><?= $totalCount ?> giao dịch từ <strong><?= $fromDate ?></strong> đến <strong><?= $toDate ?></strong></span>
    </div>
    <?php if (empty($transactions)): ?>
      <div class="p-5 text-center text-muted">Không có giao dịch nào trong khoảng thời gian này</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Sản phẩm</th>
              <th>Người mua</th>
              <th>Người bán</th>
              <th>Loại</th>
              <th class="text-end">Giá (đ)</th>
              <th>Thời gian</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($transactions as $i => $t): ?>
              <tr>
                <td class="small text-muted"><?= $i + 1 ?></td>
                <td>
                  <a href="<?= $appUrl ?>/products/show?id=<?= $t['product_id'] ?>" class="text-dark fw-600 text-decoration-none"
                     style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block">
                    <?= htmlspecialchars($t['product_title'], ENT_QUOTES) ?>
                  </a>
                </td>
                <td class="small"><?= htmlspecialchars($t['buyer_name'], ENT_QUOTES) ?></td>
                <td class="small"><?= htmlspecialchars($t['seller_name'], ENT_QUOTES) ?></td>
                <td>
                  <?php if ($t['type'] === 'auction'): ?>
                    <span class="badge bg-danger">⚡ Đấu giá</span>
                  <?php else: ?>
                    <span class="badge bg-primary">💰 Trực tiếp</span>
                  <?php endif; ?>
                </td>
                <td class="text-end fw-700 text-success text-nowrap">
                  <?= number_format((int)$t['amount'], 0, ',', '.') ?>đ
                </td>
                <td class="small text-muted text-nowrap">
                  <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot class="table-light fw-bold">
            <tr>
              <td colspan="5" class="text-end">Tổng cộng:</td>
              <td class="text-end text-success text-nowrap"><?= number_format($totalAmount, 0, ',', '.') ?>đ</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($byDay)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('myChart'), {
  type: 'bar',
  data: {
    labels: <?= $chartLabels ?>,
    datasets: [{
      label: 'Doanh số (đ)',
      data:  <?= $chartData ?>,
      backgroundColor: 'rgba(99, 102, 241, 0.7)',
      borderColor:     'rgba(99, 102, 241, 1)',
      borderWidth: 1,
      borderRadius: 6,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: {
        beginAtZero: true,
        ticks: { callback: v => v.toLocaleString('vi-VN') + 'đ' }
      }
    }
  }
});
</script>
<?php endif; ?>
