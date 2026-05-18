<?php
/**
 * Admin View: Quản lý Đánh giá — Tailwind Edition
 * Biến: $ratings
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
?>

<div class="font-sans antialiased text-gray-800 dark:text-dark-text">
  <div class="flex items-center justify-between mb-6">
    <h4 class="text-xl font-extrabold text-gray-800 dark:text-dark-text flex items-center gap-2 m-0">
      <i class="bi bi-star-half text-amber-500"></i>Kiểm duyệt Đánh giá
    </h4>
  </div>

  <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm animate-[fadeInUp_0.4s_ease-out_both]">
    <?php if (empty($ratings)): ?>
      <div class="p-16 text-center text-gray-400 flex flex-col items-center gap-3">
        <i class="bi bi-star text-5xl text-gray-200 dark:text-gray-700"></i>
        <p class="m-0 text-sm font-semibold">Chưa có đánh giá nào trên hệ thống.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[800px]">
          <thead>
            <tr class="bg-gray-50 dark:bg-dark-2 border-b border-light-border dark:border-dark-border text-[11px] font-bold text-gray-500 uppercase tracking-wider">
              <th class="py-3.5 px-4">#</th>
              <th class="py-3.5 px-4">Người đánh giá</th>
              <th class="py-3.5 px-4">Người bán</th>
              <th class="py-3.5 px-4">Sản phẩm</th>
              <th class="py-3.5 px-4">Đánh giá & Bình luận</th>
              <th class="py-3.5 px-4">Trạng thái</th>
              <th class="py-3.5 px-4 text-right">Hành động</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
            <?php foreach ($ratings as $r): ?>
              <tr class="hover:bg-gray-50/50 dark:hover:bg-dark-2/50 transition-colors <?= $r['is_hidden'] ? 'bg-red-50/30 dark:bg-red-500/5' : '' ?>">
                <td class="py-4 px-4 text-xs font-bold text-gray-400"><?= $r['id'] ?></td>
                <td class="py-4 px-4">
                  <a href="<?= $appUrl ?>/admin/users/detail?id=<?= $r['rater_id'] ?>"
                     class="font-bold text-sm text-gray-800 dark:text-dark-text no-underline hover:text-primary transition-colors block">
                    <?= htmlspecialchars($r['rater_name'], ENT_QUOTES) ?>
                  </a>
                  <div class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($r['rater_email'], ENT_QUOTES) ?></div>
                </td>
                <td class="py-4 px-4 whitespace-nowrap">
                  <a href="<?= $appUrl ?>/admin/users/detail?id=<?= $r['ratee_id'] ?>"
                     class="font-bold text-sm text-primary no-underline hover:brightness-110 transition-all">
                    <?= htmlspecialchars($r['ratee_name'], ENT_QUOTES) ?>
                  </a>
                </td>
                <td class="py-4 px-4">
                  <a href="<?= $appUrl ?>/products/show?id=<?= $r['product_id'] ?>" target="_blank"
                     class="text-xs font-medium text-cyan-600 dark:text-cyan-400 no-underline hover:underline block max-w-[150px] truncate">
                    <?= htmlspecialchars(mb_strimwidth($r['product_title'], 0, 30, '...'), ENT_QUOTES) ?>
                  </a>
                </td>
                <td class="py-4 px-4">
                  <div class="flex items-center gap-0.5 text-amber-400 mb-1.5 text-sm">
                    <?php for ($i=1; $i<=5; $i++): ?>
                      <i class="bi bi-star<?= $i <= $r['stars'] ? '-fill' : '' ?>"></i>
                    <?php endfor; ?>
                  </div>
                  <div class="text-xs font-medium leading-relaxed max-w-[220px] line-clamp-3 <?= $r['is_hidden'] ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-300' ?>">
                    <?= nl2br(htmlspecialchars($r['comment'] ?: '(Không có bình luận)', ENT_QUOTES)) ?>
                  </div>
                </td>
                <td class="py-4 px-4 whitespace-nowrap">
                  <?php if ($r['is_hidden']): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold text-red-700 bg-red-100">
                      <i class="bi bi-eye-slash"></i>Đã Ẩn
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold text-green-700 bg-green-100">
                      <i class="bi bi-eye"></i>Hiển thị
                    </span>
                  <?php endif; ?>
                  <div class="text-[10px] text-gray-400 mt-1"><?= date('d/m/Y', strtotime($r['created_at'])) ?></div>
                </td>
                <td class="py-4 px-4 text-right whitespace-nowrap">
                  <form action="<?= $appUrl ?>/admin/ratings/toggle" method="POST" class="m-0"
                        onsubmit="return confirm('Bạn có chắc chắn muốn thay đổi trạng thái bình luận này? Nếu ẩn, một email cảnh cáo sẽ được gửi đi.');">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all border cursor-pointer
                      <?= $r['is_hidden']
                          ? 'border-green-200 dark:border-green-800 text-green-600 hover:bg-green-500 hover:text-white hover:border-green-500 bg-transparent'
                          : 'border-red-200 dark:border-red-800 text-red-500 hover:bg-red-500 hover:text-white hover:border-red-500 bg-transparent' ?>">
                      <?= $r['is_hidden']
                          ? '<i class="bi bi-eye"></i> Hiện lại'
                          : '<i class="bi bi-eye-slash"></i> Ẩn' ?>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
