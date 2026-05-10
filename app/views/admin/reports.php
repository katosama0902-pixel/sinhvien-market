<?php
/**
 * Admin View: Báo cáo giao dịch — Tailwind Edition
 * Biến: $transactions, $fromDate, $toDate, $totalAmount
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

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

<div class="font-sans antialiased text-gray-800 dark:text-dark-text">
  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <h4 class="text-xl font-extrabold text-gray-800 dark:text-dark-text flex items-center gap-2 m-0">
      <i class="bi bi-bar-chart-fill text-emerald-500"></i>Báo cáo giao dịch
    </h4>
    <a href="<?= $appUrl ?>/admin/export?type=transactions&from=<?= urlencode($fromDate) ?>&to=<?= urlencode($toDate) ?>"
       class="inline-flex items-center justify-center px-5 py-2.5 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm shadow-sm hover:-translate-y-0.5 hover:shadow-md transition-all no-underline">
      <i class="bi bi-file-earmark-excel-fill mr-2"></i> Xuất Excel (CSV)
    </a>
  </div>

  <!-- Bộ lọc ngày -->
  <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border p-6 mb-6 shadow-sm animate-[fadeInUp_0.3s_ease-out_both]">
    <form class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end" method="GET" action="<?= $appUrl ?>/admin/reports" id="filterForm">
      <div>
        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wider">Từ ngày</label>
        <input type="date" name="from" id="fromDate"
               class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 text-sm font-medium text-gray-800 dark:text-dark-text transition-all"
               value="<?= htmlspecialchars($fromDate) ?>">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-600 dark:text-gray-400 mb-2 uppercase tracking-wider">Đến ngày</label>
        <input type="date" name="to" id="toDate"
               class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 text-sm font-medium text-gray-800 dark:text-dark-text transition-all"
               value="<?= htmlspecialchars($toDate) ?>">
      </div>
      <div class="flex gap-3">
        <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 rounded-xl bg-primary text-white font-bold text-sm hover:brightness-110 hover:-translate-y-0.5 transition-all border-0 cursor-pointer shadow-sm">
          <i class="bi bi-search"></i>Lọc
        </button>
        <a href="<?= $appUrl ?>/admin/reports"
           class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-bold text-sm hover:bg-gray-50 dark:hover:bg-dark-2 transition-all no-underline">
          Reset
        </a>
      </div>
    </form>
    <script>
      document.getElementById('filterForm').addEventListener('submit', function(e) {
          var from = document.getElementById('fromDate').value;
          var to = document.getElementById('toDate').value;
          if (from && to && from > to) { e.preventDefault(); alert('Lỗi: "Từ ngày" không được lớn hơn "Đến ngày"!'); }
      });
      document.getElementById('fromDate').addEventListener('change', function() { document.getElementById('toDate').min = this.value; });
      document.getElementById('toDate').addEventListener('change', function() { document.getElementById('fromDate').max = this.value; });
    </script>
  </div>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
    <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border p-6 text-center shadow-sm hover:-translate-y-1 transition-transform animate-[fadeInUp_0.4s_ease-out_both]">
      <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Số giao dịch</div>
      <div class="text-4xl font-black text-primary mb-1"><?= number_format($totalCount) ?></div>
      <div class="text-xs text-gray-400">giao dịch</div>
    </div>
    <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border p-6 text-center shadow-sm hover:-translate-y-1 transition-transform animate-[fadeInUp_0.4s_ease-out_0.1s_both]">
      <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tổng giá trị</div>
      <div class="text-4xl font-black text-emerald-600 mb-1"><?= number_format($totalAmount, 0, ',', '.') ?>đ</div>
      <div class="text-xs text-gray-400">tổng cộng</div>
    </div>
    <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border p-6 text-center shadow-sm hover:-translate-y-1 transition-transform animate-[fadeInUp_0.4s_ease-out_0.2s_both]">
      <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Giá trung bình</div>
      <div class="text-4xl font-black text-cyan-600 mb-1"><?= number_format($avgAmount, 0, ',', '.') ?>đ</div>
      <div class="text-xs text-gray-400">mỗi giao dịch</div>
    </div>
  </div>

  <!-- Biểu đồ theo ngày -->
  <?php if (!empty($byDay)): ?>
    <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border p-6 mb-6 shadow-sm animate-[fadeInUp_0.5s_ease-out_both]">
      <h6 class="font-extrabold text-base text-gray-800 dark:text-dark-text mb-4 flex items-center gap-2">
        <i class="bi bi-graph-up-arrow text-indigo-500"></i>Doanh số theo ngày (đ)
      </h6>
      <canvas id="myChart" height="80"></canvas>
    </div>
  <?php endif; ?>

  <!-- Bảng giao dịch -->
  <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm animate-[fadeInUp_0.6s_ease-out_both]">
    <div class="px-6 py-4 border-b border-light-border dark:border-dark-border bg-gray-50/50 dark:bg-dark-2/50 flex items-center">
      <span class="text-sm text-gray-500 dark:text-gray-400">
        <strong class="text-gray-800 dark:text-dark-text font-extrabold"><?= $totalCount ?></strong> giao dịch từ
        <strong class="text-gray-700 dark:text-gray-200"><?= $fromDate ?></strong> đến
        <strong class="text-gray-700 dark:text-gray-200"><?= $toDate ?></strong>
      </span>
    </div>

    <?php if (empty($transactions)): ?>
      <div class="p-16 text-center text-gray-400">
        <i class="bi bi-inbox text-5xl text-gray-200 dark:text-gray-700 block mb-3"></i>
        <p class="m-0 text-sm font-medium">Không có giao dịch nào trong khoảng thời gian này</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[750px]">
          <thead>
            <tr class="bg-gray-50/30 dark:bg-dark-2/30 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-light-border dark:border-dark-border">
              <th class="py-3.5 px-5">#</th>
              <th class="py-3.5 px-5">Sản phẩm</th>
              <th class="py-3.5 px-5">Người mua</th>
              <th class="py-3.5 px-5">Người bán</th>
              <th class="py-3.5 px-5">Loại</th>
              <th class="py-3.5 px-5 text-right">Giá (đ)</th>
              <th class="py-3.5 px-5">Thời gian</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
            <?php foreach ($transactions as $i => $t): ?>
              <tr class="hover:bg-gray-50/50 dark:hover:bg-dark-2/50 transition-colors">
                <td class="py-4 px-5 text-xs font-bold text-gray-400"><?= $i + 1 ?></td>
                <td class="py-4 px-5">
                  <a href="<?= $appUrl ?>/products/show?id=<?= $t['product_id'] ?>" target="_blank"
                     class="text-sm font-bold text-gray-800 dark:text-dark-text no-underline hover:text-primary transition-colors block max-w-[200px] truncate">
                    <?= htmlspecialchars($t['product_title'], ENT_QUOTES) ?>
                  </a>
                </td>
                <td class="py-4 px-5 text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap"><?= htmlspecialchars($t['buyer_name'], ENT_QUOTES) ?></td>
                <td class="py-4 px-5 text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap"><?= htmlspecialchars($t['seller_name'], ENT_QUOTES) ?></td>
                <td class="py-4 px-5 whitespace-nowrap">
                  <?php if ($t['type'] === 'auction'): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700">⚡ Đấu giá</span>
                  <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-700">💰 Trực tiếp</span>
                  <?php endif; ?>
                </td>
                <td class="py-4 px-5 font-black text-sm text-emerald-600 text-right whitespace-nowrap">
                  <?= number_format((int)$t['amount'], 0, ',', '.') ?>đ
                </td>
                <td class="py-4 px-5 text-xs font-medium text-gray-400 whitespace-nowrap">
                  <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot class="bg-gray-50/70 dark:bg-dark-2/70 border-t-2 border-light-border dark:border-dark-border">
            <tr>
              <td colspan="5" class="py-4 px-5 text-sm font-extrabold text-right text-gray-700 dark:text-gray-200">Tổng cộng:</td>
              <td class="py-4 px-5 text-sm font-black text-emerald-600 text-right whitespace-nowrap"><?= number_format($totalAmount, 0, ',', '.') ?>đ</td>
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
      borderRadius: 8,
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
