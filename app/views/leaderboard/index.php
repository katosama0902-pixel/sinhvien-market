<?php
/**
 * View: Bảng Xếp Hạng Top Seller
 * Vars: $topSellers, $myRank, $myStats, $appUrl
 */
$user = $_SESSION['user'] ?? null;
?>

<!-- ─── Hero ─────────────────────────────────────────────────── -->
<div class="relative overflow-hidden py-16 text-center"
     style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 40%,#0f3460 100%)">
  <!-- Radial glow -->
  <div class="absolute inset-0 pointer-events-none"
       style="background:radial-gradient(circle at 30% 40%,rgba(99,102,241,.15) 0%,transparent 50%),radial-gradient(circle at 70% 60%,rgba(168,85,247,.1) 0%,transparent 50%)"></div>
  <div class="relative z-10">
    <div class="text-5xl font-black text-white mb-2" style="text-shadow:0 2px 20px rgba(99,102,241,.5)">
      🏆 Bảng Xếp Hạng
    </div>
    <div class="text-white/60 text-base">Top Người Bán Uy Tín — Khu ĐHQG TP.HCM</div>
  </div>
</div>

<!-- ─── Content ──────────────────────────────────────────────── -->
<div class="container mx-auto px-4 pb-12" style="margin-top:-2rem;position:relative;z-index:2">

  <?php if (count($topSellers) === 0): ?>
    <div class="text-center py-16">
      <div class="text-6xl mb-4">🏪</div>
      <h4 class="text-xl font-bold text-gray-700 dark:text-dark-text mb-2">Chưa có dữ liệu</h4>
      <p class="text-gray-400 mb-4">Hãy là người đầu tiên đăng sản phẩm để lên bảng xếp hạng!</p>
      <a href="<?= $appUrl ?>/products/create"
         class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-primary text-white font-bold text-sm no-underline hover:brightness-110">
        Đăng bán ngay
      </a>
    </div>

  <?php else: ?>

    <!-- ─── Podium Top 3 ─────────────────────────────────────── -->
    <?php
      $top3 = array_slice($topSellers, 0, 3);
      $podiumOrder = [];
      if (isset($top3[1])) $podiumOrder[] = ['rank' => 2, 'user' => $top3[1]];
      if (isset($top3[0])) $podiumOrder[] = ['rank' => 1, 'user' => $top3[0]];
      if (isset($top3[2])) $podiumOrder[] = ['rank' => 3, 'user' => $top3[2]];

      $podiumHeights = [1 => 'h-24', 2 => 'h-16', 3 => 'h-12'];
      $podiumBgs     = [1 => 'linear-gradient(180deg,#fbbf24,#f59e0b)', 2 => 'linear-gradient(180deg,#94a3b8,#64748b)', 3 => 'linear-gradient(180deg,#cd7c3e,#a55c22)'];
      $rankBgs       = [1 => 'linear-gradient(135deg,#f59e0b,#d97706)', 2 => 'linear-gradient(135deg,#94a3b8,#64748b)', 3 => 'linear-gradient(135deg,#cd7c3e,#a55c22)'];
      $avatarSizes   = [1 => 'w-24 h-24', 2 => 'w-20 h-20', 3 => 'w-20 h-20'];
    ?>

    <style>
      @keyframes crownFloat { 0%,100%{transform:translateX(-50%) translateY(0);} 50%{transform:translateX(-50%) translateY(-6px);} }
    </style>

    <div class="flex items-end justify-center gap-5 mb-6 px-4">
      <?php foreach ($podiumOrder as $pod):
        $u = $pod['user']; $r = $pod['rank'];
        $avatarSrc = !empty($u['avatar'])
          ? $appUrl.'/public/uploads/'.htmlspecialchars($u['avatar'])
          : (!empty($u['avatar_url']) ? htmlspecialchars($u['avatar_url'])
            : 'https://ui-avatars.com/api/?name='.urlencode($u['name']).'&background=6366f1&color=fff&size=96');
      ?>
        <div class="flex flex-col items-center">
          <!-- Avatar -->
          <div class="relative mb-2">
            <?php if ($r === 1): ?>
              <span class="absolute left-1/2 text-2xl" style="top:-26px;transform:translateX(-50%);animation:crownFloat 2s ease-in-out infinite">👑</span>
            <?php endif; ?>
            <img src="<?= $avatarSrc ?>" alt="<?= htmlspecialchars($u['name']) ?>"
                 class="rounded-full object-cover border-4 border-white shadow-lg <?= $avatarSizes[$r] ?>">
            <span class="absolute bottom-0 right-0 w-7 h-7 flex items-center justify-center text-xs font-extrabold text-white rounded-full border-2 border-white"
                  style="background:<?= $rankBgs[$r] ?>">
              <?= $r ?>
            </span>
          </div>
          <!-- Name & score -->
          <div class="font-bold text-sm text-gray-800 dark:text-dark-text max-w-[100px] truncate text-center">
            <?= htmlspecialchars($u['name']) ?>
          </div>
          <div class="text-xs text-gray-400 mb-2">
            ⭐ <?= number_format((float)$u['avg_rating'], 1) ?> · <?= (int)$u['sold_count'] ?> đã bán
          </div>
          <!-- Podium box -->
          <div class="w-28 flex items-center justify-center rounded-t-2xl text-2xl font-black text-white/70"
               style="height:<?= [1=>100,2=>70,3=>50][$r] ?>px;background:<?= $podiumBgs[$r] ?>">
            <?= $r ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- ─── Rankings 4–10 ────────────────────────────────────── -->
    <?php $rest = array_slice($topSellers, 3); ?>
    <?php if (count($rest) > 0): ?>
      <div class="rounded-2xl border border-light-border dark:border-dark-border overflow-hidden bg-white dark:bg-dark-card mb-4">
        <?php foreach ($rest as $i => $s): $rank = $i + 4; ?>
          <a href="<?= $appUrl ?>/users/profile?id=<?= $s['id'] ?>"
             class="flex items-center gap-4 px-5 py-4 border-b border-gray-50 dark:border-dark-border no-underline hover:bg-indigo-50/40 dark:hover:bg-dark-2 transition-colors last:border-b-0">

            <!-- Rank -->
            <div class="w-8 text-center font-extrabold text-gray-400 text-base flex-shrink-0"><?= $rank ?></div>

            <!-- Avatar -->
            <img class="w-11 h-11 rounded-full object-cover border-2 border-light-border flex-shrink-0"
                 src="<?= !empty($s['avatar']) ? $appUrl.'/public/uploads/'.htmlspecialchars($s['avatar']) : (!empty($s['avatar_url']) ? htmlspecialchars($s['avatar_url']) : 'https://ui-avatars.com/api/?name='.urlencode($s['name']).'&background=6366f1&color=fff') ?>"
                 alt="">

            <!-- Name & badge -->
            <div class="flex-1 min-w-0">
              <div class="font-bold text-sm text-gray-800 dark:text-dark-text flex items-center gap-1">
                <?= htmlspecialchars($s['name']) ?>
                <?php if ($s['is_student_verified']): ?>
                  <i class="bi bi-patch-check-fill text-primary text-xs"></i>
                <?php endif; ?>
              </div>
              <div class="text-xs text-gray-400">
                <?php $total = (int)$s['product_count'];
                  if ($total >= 5)      echo '<span class="text-yellow-500">⭐ Uy tín</span>';
                  elseif ($total >= 1)  echo '<span class="text-cyan-500">✦ Tích cực</span>';
                  else                  echo 'Tân binh';
                ?>
              </div>
            </div>

            <!-- Stats -->
            <div class="flex gap-5 flex-shrink-0">
              <div class="text-center min-w-[44px]">
                <div class="font-bold text-sm text-gray-800 dark:text-dark-text"><?= (int)$s['sold_count'] ?></div>
                <div class="text-[10px] text-gray-400 uppercase tracking-wide">Đã bán</div>
              </div>
              <div class="text-center min-w-[44px]">
                <div class="font-bold text-sm text-gray-800 dark:text-dark-text"><?= number_format((float)$s['avg_rating'], 1) ?>⭐</div>
                <div class="text-[10px] text-gray-400 uppercase tracking-wide">Đánh giá</div>
              </div>
              <div class="text-center min-w-[44px] hidden md:block">
                <div class="font-bold text-sm text-gray-800 dark:text-dark-text"><?= (int)$s['product_count'] ?></div>
                <div class="text-[10px] text-gray-400 uppercase tracking-wide">Tin đăng</div>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- ─── My Rank Card ─────────────────────────────────────── -->
    <?php if ($user && $myRank > 0): ?>
      <div class="flex items-center gap-4 p-5 rounded-2xl mb-4"
           style="background:linear-gradient(135deg,rgba(99,102,241,.1),rgba(168,85,247,.08));border:2px solid rgba(99,102,241,.4)">
        <div class="text-4xl">🎯</div>
        <div class="flex-1">
          <div class="font-bold text-gray-800 dark:text-dark-text">Thứ hạng của tôi</div>
          <?php if ($myStats): ?>
            <div class="text-xs text-gray-400 mt-0.5">
              <?= (int)$myStats['sold_count'] ?> đã bán
              · ⭐ <?= number_format((float)$myStats['avg_rating'], 1) ?>
              · <?= (int)$myStats['product_count'] ?> tin đăng
            </div>
          <?php endif; ?>
        </div>
        <div class="text-4xl font-black" style="color:#6366f1">#<?= $myRank ?></div>
      </div>

    <?php elseif ($user): ?>
      <div class="text-center p-5 rounded-2xl mb-4"
           style="background:linear-gradient(135deg,rgba(99,102,241,.08),rgba(168,85,247,.06));border:2px solid rgba(99,102,241,.3)">
        <p class="text-sm text-gray-500 mb-3">Bạn chưa có xếp hạng. Hãy đăng sản phẩm và hoàn thành giao dịch để lên bảng!</p>
        <a href="<?= $appUrl ?>/products/create"
           class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-primary text-white font-bold text-sm no-underline hover:brightness-110">
          Đăng bán ngay
        </a>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>
