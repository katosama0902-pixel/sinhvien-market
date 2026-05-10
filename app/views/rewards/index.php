<?php
/**
 * View: Trung tâm Nhận xu & Gamification
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$user   = $_SESSION['user'];
use Core\Controller;
$ctrl = new class extends Controller {};
$csrf = $ctrl->csrfToken();
?>

<div class="container mx-auto px-4 py-8 max-w-2xl">

  <h1 class="text-2xl font-extrabold text-gray-800 dark:text-dark-text text-center mb-8">
    🎁 Trung Tâm Phần Thưởng Sinh Viên
  </h1>

  <!-- ─── Check-in Card ───────────────────────────────────────────── -->
  <div class="rounded-2xl p-6 mb-6" style="background:#fffaf0;border:1.5px solid #ffeeba">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <div>
        <h2 class="text-base font-extrabold text-yellow-600 flex items-center gap-2 mb-0.5">
          <i class="bi bi-calendar-check-fill"></i>Chuỗi điểm danh nhận Xu
        </h2>
        <div class="text-sm text-gray-500">
          Bạn đang có: <strong class="text-gray-800"><?= number_format($coins) ?> xu</strong>
        </div>
      </div>

      <?php if ($canCheckin): ?>
        <form action="<?= $appUrl ?>/coins/checkin" method="POST">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
          <button type="submit"
                  class="px-6 py-2.5 rounded-full font-extrabold text-gray-900 text-sm cursor-pointer border-0 transition-all shadow-sm"
                  style="background:#ffc107"
                  onmouseover="this.style.background='#ffb300'" onmouseout="this.style.background='#ffc107'">
            ✅ Điểm danh ngay
          </button>
        </form>
      <?php else: ?>
        <button disabled
                class="px-6 py-2.5 rounded-full font-bold text-sm bg-gray-200 text-gray-400 cursor-not-allowed border-0">
          Đã điểm danh hôm nay
        </button>
      <?php endif; ?>
    </div>

    <!-- 7-day streak bar -->
    <div class="relative flex justify-between items-center text-center mt-2">
      <!-- Track background -->
      <div class="absolute w-full h-1.5 top-6 rounded-full bg-yellow-100"></div>
      <!-- Progress fill -->
      <div class="absolute h-1.5 top-6 rounded-full bg-yellow-400 transition-all duration-700"
           style="width:<?= min(100, ($streak/7)*100) ?>%"></div>

      <?php for ($i = 1; $i <= 7; $i++):
          $isPassed    = $i <= $streak;
          $isToday     = ($canCheckin && $i == $streak + 1);
          $isRewardDay = ($i == 7);
          $coinVal     = $isRewardDay ? 50 : 10;
      ?>
        <div class="relative flex flex-col items-center flex-1" style="z-index:1">
          <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-1.5 text-xl shadow-sm transition-all"
               style="
                 background:<?= $isPassed ? '#ffc107' : ($isToday ? '#fff' : '#f8f9fa') ?>;
                 border:<?= ($isPassed || $isToday) ? '4px' : '3px' ?> solid <?= ($isPassed || $isToday) ? '#ffc107' : '#dee2e6' ?>;
                 color:<?= $isPassed ? '#fff' : ($isToday ? '#ffc107' : '#adb5bd') ?>;
               ">
            <?php if ($isPassed): ?>
              <i class="bi bi-check-lg font-black"></i>
            <?php elseif ($isRewardDay): ?>
              <i class="bi bi-gift-fill"></i>
            <?php else: ?>
              <i class="bi bi-coin"></i>
            <?php endif; ?>
          </div>

          <?php if ($isPassed): ?>
            <div class="text-xs font-bold text-green-500">Đã nhận</div>
            <div class="text-xs text-gray-400 line-through opacity-75">+<?= $coinVal ?> xu</div>
          <?php else: ?>
            <div class="text-xs font-bold <?= $isToday ? 'text-gray-800' : 'text-gray-400' ?>">Ngày <?= $i ?></div>
            <div class="text-xs font-bold text-red-500">+<?= $coinVal ?> xu</div>
          <?php endif; ?>
        </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- ─── Minigames Coming Soon ───────────────────────────────────── -->
  <div class="text-center py-10 text-gray-300">
    <i class="bi bi-controller text-6xl block mb-3 opacity-30"></i>
    <p class="font-extrabold uppercase tracking-widest text-sm opacity-50 mb-1">Minigames sắp ra mắt</p>
    <p class="text-sm opacity-40">Các trò chơi nhận xu hấp dẫn sẽ sớm xuất hiện tại đây.</p>
  </div>

</div>
