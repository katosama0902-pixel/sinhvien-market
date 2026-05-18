<?php
/**
 * Admin View: Chi tiết người dùng
 * Layout: admin
 * Biến: $profile, $products, $transactions, $ratingStats
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$p = $profile;
?>

<div class="font-sans antialiased text-gray-800 dark:text-dark-text">
  <!-- Breadcrumb -->
  <div class="mb-5 flex items-center gap-2 text-sm text-gray-500 font-semibold animate-[fadeIn_0.3s_ease-out_both]">
    <a href="<?= $appUrl ?>/admin/users" class="flex items-center text-primary hover:text-indigo-600 dark:hover:text-indigo-400 no-underline transition-colors">
      <i class="bi bi-people mr-1.5"></i>Người dùng
    </a>
    <i class="bi bi-chevron-right text-[10px]"></i>
    <span class="text-gray-800 dark:text-dark-text font-extrabold"><?= htmlspecialchars($p['name'], ENT_QUOTES) ?></span>
  </div>

  <div class="flex flex-col lg:flex-row gap-6 items-start">

    <!-- ─── CỘT TRÁI: Thông tin cá nhân ─────────────────────────── -->
    <div class="w-full lg:w-[320px] flex-shrink-0 flex flex-col gap-5 animate-[fadeInUp_0.4s_ease-out_both]">

      <!-- Hero card -->
      <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-[20px] p-6 text-white flex flex-col items-center gap-3 text-center shadow-[0_12px_32px_rgba(99,102,241,0.25)] relative overflow-hidden">
        <div class="absolute w-40 h-40 rounded-full bg-white/10 -top-10 -right-10 blur-2xl"></div>
        <div class="absolute w-32 h-32 rounded-full bg-white/10 -bottom-10 -left-10 blur-2xl"></div>
        
        <div class="w-20 h-20 rounded-full bg-white/20 border-4 border-white/40 flex items-center justify-center text-3xl font-black shadow-lg relative z-10 backdrop-blur-md">
          <?= mb_strtoupper(mb_substr($p['name'], 0, 1)) ?>
        </div>
        <div class="relative z-10">
          <div class="text-xl font-extrabold mb-0.5"><?= htmlspecialchars($p['name'], ENT_QUOTES) ?></div>
          <div class="text-sm font-medium text-white/80"><?= htmlspecialchars($p['email'], ENT_QUOTES) ?></div>
        </div>
        <!-- Stars nếu có -->
        <?php if ($ratingStats['count'] > 0): ?>
          <div class="relative z-10 mt-1 flex flex-col items-center">
            <div class="text-amber-400 flex items-center text-sm">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="bi bi-star<?= $i <= round($ratingStats['avg']) ? '-fill' : '' ?>"></i>
              <?php endfor; ?>
            </div>
            <span class="text-xs font-semibold text-white/80 mt-1"><?= $ratingStats['avg'] ?>/5 (<?= $ratingStats['count'] ?> đánh giá)</span>
          </div>
        <?php endif; ?>
      </div>

      <!-- Thông tin tài khoản -->
      <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm">
        <div class="px-5 py-3.5 border-b border-light-border dark:border-dark-border flex items-center gap-2.5 font-extrabold text-sm text-gray-800 dark:text-dark-text bg-gray-50/50 dark:bg-dark-2/50">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-sm shadow-sm flex-shrink-0">
            <i class="bi bi-person-badge"></i>
          </div>
          Thông tin tài khoản
        </div>
        <div class="p-5 flex flex-col gap-3">
          <div class="flex justify-between items-start gap-2 text-sm border-b border-gray-50 dark:border-dark-border pb-3 last:border-0 last:pb-0">
            <div class="text-gray-500 font-bold whitespace-nowrap min-w-[120px]">ID</div>
            <div class="text-gray-800 dark:text-dark-text font-black font-mono">#<?= $p['id'] ?></div>
          </div>
          <div class="flex justify-between items-start gap-2 text-sm border-b border-gray-50 dark:border-dark-border pb-3 last:border-0 last:pb-0">
            <div class="text-gray-500 font-bold whitespace-nowrap min-w-[120px]">Vai trò</div>
            <div class="text-right">
              <?php if ($p['role'] === 'admin'): ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold text-white bg-gradient-to-br from-red-500 to-red-600 shadow-sm"><i class="bi bi-shield-fill"></i>Admin</span>
              <?php else: ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold text-indigo-700 bg-indigo-100">Sinh viên</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="flex justify-between items-start gap-2 text-sm border-b border-gray-50 dark:border-dark-border pb-3 last:border-0 last:pb-0">
            <div class="text-gray-500 font-bold whitespace-nowrap min-w-[120px]">Trạng thái</div>
            <div class="text-right">
              <?php if ($p['is_locked']): ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold text-red-600 bg-red-100"><i class="bi bi-lock-fill"></i>Bị khóa</span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold text-green-700 bg-green-100"><i class="bi bi-check-circle-fill"></i>Hoạt động</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="flex justify-between items-start gap-2 text-sm border-b border-gray-50 dark:border-dark-border pb-3 last:border-0 last:pb-0">
            <div class="text-gray-500 font-bold whitespace-nowrap min-w-[120px]">Xác thực email</div>
            <div class="text-right">
              <?php if ($p['is_verified']): ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold text-blue-700 bg-blue-100"><i class="bi bi-patch-check-fill"></i>Đã xác thực</span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold text-amber-600 bg-amber-100"><i class="bi bi-exclamation-circle"></i>Chưa xác thực</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="flex justify-between items-start gap-2 text-sm border-b border-gray-50 dark:border-dark-border pb-3 last:border-0 last:pb-0">
            <div class="text-gray-500 font-bold whitespace-nowrap min-w-[120px]">Ngày đăng ký</div>
            <div class="text-gray-800 dark:text-dark-text font-medium text-right"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></div>
          </div>
          <?php if ($p['last_verified_at']): ?>
          <div class="flex justify-between items-start gap-2 text-sm border-b border-gray-50 dark:border-dark-border pb-3 last:border-0 last:pb-0">
            <div class="text-gray-500 font-bold whitespace-nowrap min-w-[120px]">Xác thực lần cuối</div>
            <div class="text-gray-800 dark:text-dark-text font-medium text-right"><?= date('d/m/Y H:i', strtotime($p['last_verified_at'])) ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Thông tin cá nhân -->
      <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm">
        <div class="px-5 py-3.5 border-b border-light-border dark:border-dark-border flex items-center gap-2.5 font-extrabold text-sm text-gray-800 dark:text-dark-text bg-gray-50/50 dark:bg-dark-2/50">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-sm shadow-sm flex-shrink-0">
            <i class="bi bi-person-lines-fill"></i>
          </div>
          Thông tin cá nhân
        </div>
        <div class="p-5 flex flex-col gap-3">
          <div class="flex justify-between items-start gap-2 text-sm border-b border-gray-50 dark:border-dark-border pb-3 last:border-0 last:pb-0">
            <div class="text-gray-500 font-bold whitespace-nowrap min-w-[120px]">Họ tên</div>
            <div class="text-gray-800 dark:text-dark-text font-medium text-right break-words"><?= htmlspecialchars($p['name'], ENT_QUOTES) ?></div>
          </div>
          <div class="flex justify-between items-start gap-2 text-sm border-b border-gray-50 dark:border-dark-border pb-3 last:border-0 last:pb-0">
            <div class="text-gray-500 font-bold whitespace-nowrap min-w-[120px]">Email</div>
            <div class="text-gray-800 dark:text-dark-text font-medium text-right break-words"><?= htmlspecialchars($p['email'], ENT_QUOTES) ?></div>
          </div>
          <div class="flex justify-between items-start gap-2 text-sm border-b border-gray-50 dark:border-dark-border pb-3 last:border-0 last:pb-0">
            <div class="text-gray-500 font-bold whitespace-nowrap min-w-[120px]">Số điện thoại</div>
            <div class="text-gray-800 dark:text-dark-text font-medium text-right break-words"><?= htmlspecialchars($p['phone'] ?: '—', ENT_QUOTES) ?></div>
          </div>
          <div class="flex justify-between items-start gap-2 text-sm border-b border-gray-50 dark:border-dark-border pb-3 last:border-0 last:pb-0">
            <div class="text-gray-500 font-bold whitespace-nowrap min-w-[120px]">Câu hỏi bảo mật</div>
            <div class="text-gray-800 dark:text-dark-text font-medium text-right break-words"><?= htmlspecialchars($p['security_question'] ?: '—', ENT_QUOTES) ?></div>
          </div>
        </div>
      </div>

      <!-- Trạng thái khóa nếu bị khóa -->
      <?php if ($p['is_locked']): ?>
      <div class="bg-red-50 dark:bg-red-500/5 rounded-[20px] border border-red-200 dark:border-red-500/20 overflow-hidden shadow-sm">
        <div class="px-5 py-3.5 border-b border-red-200 dark:border-red-500/20 flex items-center gap-2.5 font-extrabold text-sm text-red-600 dark:text-red-500 bg-red-100/50 dark:bg-red-500/10">
          <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center text-white text-sm shadow-[0_4px_12px_rgba(239,68,68,0.3)] flex-shrink-0">
            <i class="bi bi-lock-fill"></i>
          </div>
          Chi tiết khóa tài khoản
        </div>
        <div class="p-5 flex flex-col gap-3">
          <div class="flex justify-between items-start gap-2 text-sm border-b border-red-100 dark:border-red-500/10 pb-3 last:border-0 last:pb-0">
            <div class="text-red-500/80 font-bold whitespace-nowrap min-w-[120px]">Lý do</div>
            <div class="text-red-600 dark:text-red-400 font-bold text-right break-words"><?= htmlspecialchars($p['lock_reason'] ?: '—', ENT_QUOTES) ?></div>
          </div>
          <div class="flex justify-between items-start gap-2 text-sm border-b border-red-100 dark:border-red-500/10 pb-3 last:border-0 last:pb-0">
            <div class="text-red-500/80 font-bold whitespace-nowrap min-w-[120px]">Khóa lúc</div>
            <div class="text-red-600/80 dark:text-red-400/80 font-medium text-right"><?= $p['locked_at'] ? date('d/m/Y H:i', strtotime($p['locked_at'])) : '—' ?></div>
          </div>
          <div class="flex justify-between items-start gap-2 text-sm border-b border-red-100 dark:border-red-500/10 pb-3 last:border-0 last:pb-0">
            <div class="text-red-500/80 font-bold whitespace-nowrap min-w-[120px]">Hết hạn khóa</div>
            <div class="text-right font-medium">
              <?= $p['locked_until'] ? '<span class="text-red-600/80 dark:text-red-400/80">'.date('d/m/Y', strtotime($p['locked_until'])).'</span>' : '<span class="text-red-600 font-black">Vĩnh viễn</span>' ?>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div>

    <!-- ─── CỘT PHẢI: Hoạt động ──────────────────────────────────── -->
    <div class="flex-1 flex flex-col gap-6 w-full animate-[fadeInUp_0.5s_ease-out_both]">

      <!-- Sản phẩm -->
      <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-light-border dark:border-dark-border flex items-center justify-between font-extrabold text-sm text-gray-800 dark:text-dark-text bg-gray-50/50 dark:bg-dark-2/50">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center text-white text-sm shadow-sm flex-shrink-0">
              <i class="bi bi-box-seam"></i>
            </div>
            Sản phẩm đã đăng
          </div>
          <span class="bg-sky-100 text-sky-700 text-[11px] font-bold px-3 py-1 rounded-full"><?= count($products) ?></span>
        </div>
        
        <?php if (empty($products)): ?>
          <div class="p-10 text-center text-gray-400 text-sm font-medium flex flex-col items-center gap-2">
            <i class="bi bi-box-seam text-4xl text-gray-200 dark:text-gray-700"></i>
            Chưa có sản phẩm nào
          </div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px] text-[13px]">
              <thead>
                <tr class="bg-gray-50/30 dark:bg-dark-2/30 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-light-border dark:border-dark-border">
                  <th class="py-3 px-4">Sản phẩm</th>
                  <th class="py-3 px-4">Loại</th>
                  <th class="py-3 px-4">Giá</th>
                  <th class="py-3 px-4">Trạng thái</th>
                  <th class="py-3 px-4">Ngày</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
                <?php foreach ($products as $prod): ?>
                  <tr class="hover:bg-gray-50/50 dark:hover:bg-dark-2/50 transition-colors">
                    <td class="py-3 px-4">
                      <a href="<?= $appUrl ?>/products/show?id=<?= $prod['id'] ?>" class="text-gray-800 dark:text-dark-text font-bold no-underline hover:text-primary transition-colors" target="_blank">
                        <?= htmlspecialchars(mb_strimwidth($prod['title'], 0, 35, '…'), ENT_QUOTES) ?>
                      </a>
                    </td>
                    <td class="py-3 px-4 whitespace-nowrap">
                      <?php
                        $typeMap = ['sale' => ['🏷️', 'bg-indigo-100 text-indigo-700'], 'auction' => ['⚡', 'bg-pink-100 text-pink-700'], 'exchange' => ['🔄', 'bg-green-100 text-green-700']];
                        [$icon, $cls] = $typeMap[$prod['type']] ?? ['?', 'bg-gray-100 text-gray-600'];
                      ?>
                      <span class="inline-flex items-center gap-1 <?= $cls ?> px-2.5 py-0.5 rounded-full text-[11px] font-bold"><?= $icon ?> <?= $prod['type'] ?></span>
                    </td>
                    <td class="py-3 px-4 font-black text-indigo-600 font-mono whitespace-nowrap">
                      <?= $prod['price'] ? number_format($prod['price'], 0, ',', '.') . 'đ' : '—' ?>
                    </td>
                    <td class="py-3 px-4 whitespace-nowrap">
                      <?php
                        $stMap = ['active' => ['Đang hiển thị','bg-green-100 text-green-700'], 'pending' => ['Chờ duyệt','bg-amber-100 text-amber-700'], 'sold' => ['Đã bán','bg-indigo-100 text-indigo-700'], 'cancelled' => ['Từ chối','bg-red-100 text-red-700']];
                        [$stLabel, $stCls] = $stMap[$prod['status']] ?? [$prod['status'], 'bg-gray-100 text-gray-600'];
                      ?>
                      <span class="<?= $stCls ?> px-2.5 py-0.5 rounded-full text-[11px] font-bold"><?= $stLabel ?></span>
                    </td>
                    <td class="py-3 px-4 text-gray-500 font-medium whitespace-nowrap"><?= date('d/m/Y', strtotime($prod['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- Giao dịch -->
      <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-light-border dark:border-dark-border flex items-center justify-between font-extrabold text-sm text-gray-800 dark:text-dark-text bg-gray-50/50 dark:bg-dark-2/50">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center text-white text-sm shadow-sm flex-shrink-0">
              <i class="bi bi-receipt"></i>
            </div>
            Lịch sử giao dịch
          </div>
          <span class="bg-emerald-100 text-emerald-700 text-[11px] font-bold px-3 py-1 rounded-full"><?= count($transactions) ?></span>
        </div>
        
        <?php if (empty($transactions)): ?>
          <div class="p-10 text-center text-gray-400 text-sm font-medium flex flex-col items-center gap-2">
            <i class="bi bi-receipt text-4xl text-gray-200 dark:text-gray-700"></i>
            Chưa có giao dịch nào
          </div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px] text-[13px]">
              <thead>
                <tr class="bg-gray-50/30 dark:bg-dark-2/30 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-light-border dark:border-dark-border">
                  <th class="py-3 px-4">Sản phẩm</th>
                  <th class="py-3 px-4">Vai trò</th>
                  <th class="py-3 px-4">Số tiền</th>
                  <th class="py-3 px-4">Thanh toán</th>
                  <th class="py-3 px-4">Ngày</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
                <?php foreach ($transactions as $tx): ?>
                  <tr class="hover:bg-gray-50/50 dark:hover:bg-dark-2/50 transition-colors">
                    <td class="py-3 px-4 font-bold text-gray-800 dark:text-dark-text">
                      <?= htmlspecialchars(mb_strimwidth($tx['product_title'] ?? '—', 0, 35, '…'), ENT_QUOTES) ?>
                    </td>
                    <td class="py-3 px-4 whitespace-nowrap">
                      <?php if ((int)$tx['buyer_id'] === (int)$p['id']): ?>
                        <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 px-2.5 py-0.5 rounded-full text-[11px] font-bold">🛍️ Người mua</span>
                      <?php else: ?>
                        <span class="inline-flex items-center gap-1 bg-pink-100 text-pink-700 px-2.5 py-0.5 rounded-full text-[11px] font-bold">🏷️ Người bán</span>
                      <?php endif; ?>
                    </td>
                    <td class="py-3 px-4 font-black text-emerald-600 font-mono whitespace-nowrap"><?= number_format((float)($tx['amount'] ?? 0), 0, ',', '.') ?>đ</td>
                    <td class="py-3 px-4 text-[12px] font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">
                      <?php $pmMap = ['cod' => 'COD', 'banking' => 'Chuyển khoản', 'zalopay' => 'ZaloPay']; ?>
                      <?= $pmMap[$tx['payment_method']] ?? $tx['payment_method'] ?>
                    </td>
                    <td class="py-3 px-4 text-gray-500 font-medium whitespace-nowrap"><?= date('d/m/Y', strtotime($tx['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- Nhật ký bị tố cáo -->
      <div class="bg-red-50/50 dark:bg-red-500/5 rounded-[20px] border border-red-200 dark:border-red-500/20 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-red-200 dark:border-red-500/20 flex items-center justify-between font-extrabold text-sm text-red-700 dark:text-red-500 bg-red-100/50 dark:bg-red-500/10">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-600 to-rose-700 flex items-center justify-center text-white text-sm shadow-sm flex-shrink-0">
              <i class="bi bi-shield-exclamation"></i>
            </div>
            Nhật ký bị tố cáo
          </div>
          <span class="bg-red-100 text-red-700 text-[11px] font-bold px-3 py-1 rounded-full"><?= count($reports) ?></span>
        </div>
        
        <?php if (empty($reports)): ?>
          <div class="p-10 text-center text-green-600/70 text-sm font-medium flex flex-col items-center gap-2">
            <i class="bi bi-shield-check text-4xl text-green-500/50"></i>
            Người dùng này chưa từng bị tố cáo.
          </div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px] text-[13px]">
              <thead>
                <tr class="bg-red-100/30 dark:bg-red-500/10 text-[11px] font-bold text-red-800/60 dark:text-red-400/80 uppercase tracking-wider border-b border-red-200 dark:border-red-500/20">
                  <th class="py-3 px-4">Người tố cáo</th>
                  <th class="py-3 px-4">Lý do</th>
                  <th class="py-3 px-4">Trạng thái</th>
                  <th class="py-3 px-4">Ngày</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-red-100 dark:divide-red-500/10">
                <?php foreach ($reports as $rpt): ?>
                  <tr class="hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                    <td class="py-3 px-4 font-bold text-gray-800 dark:text-gray-200 whitespace-nowrap"><?= htmlspecialchars($rpt['reporter_name'] ?? '—', ENT_QUOTES) ?></td>
                    <td class="py-3 px-4">
                      <div class="font-bold text-red-600 text-[12px] mb-0.5"><?= htmlspecialchars($rpt['reason'] ?? '—', ENT_QUOTES) ?></div>
                      <div class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-2 leading-relaxed">
                        <?= htmlspecialchars($rpt['description'] ?? '—', ENT_QUOTES) ?>
                      </div>
                      <?php if (!empty($rpt['evidence_url'])): ?>
                        <a href="<?= $appUrl . $rpt['evidence_url'] ?>" target="_blank" class="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-[10px] font-bold no-underline hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                          <i class="bi bi-paperclip"></i> Bằng chứng
                        </a>
                      <?php endif; ?>
                    </td>
                    <td class="py-3 px-4 whitespace-nowrap">
                      <?php
                        $rptStMap = ['pending' => ['Chờ duyệt','bg-amber-100 text-amber-700'], 'resolved' => ['Đã xử lý','bg-green-100 text-green-700'], 'ignored' => ['Bỏ qua','bg-gray-100 text-gray-600']];
                        [$rptLabel, $rptCls] = $rptStMap[$rpt['status']] ?? [$rpt['status'], 'bg-gray-100 text-gray-600'];
                      ?>
                      <span class="<?= $rptCls ?> px-2.5 py-0.5 rounded-full text-[11px] font-bold"><?= $rptLabel ?></span>
                    </td>
                    <td class="py-3 px-4 text-gray-500 font-medium whitespace-nowrap"><?= date('d/m/Y', strtotime($rpt['created_at'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
