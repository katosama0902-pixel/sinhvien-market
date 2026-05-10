<?php
/**
 * User Public Profile View — Tailwind Edition
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
?>

<div class="max-w-[760px] mx-auto px-4 py-10 font-sans antialiased text-gray-800 dark:text-dark-text">

  <!-- ─── Hero ──────────────────────────────────────────────────── -->
  <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-[24px] p-6 md:p-8 text-white flex flex-col sm:flex-row gap-5 items-start sm:items-center mb-6 shadow-[0_12px_40px_rgba(99,102,241,0.35)] animate-[fadeInUp_0.4s_ease-out_both]">
    <!-- Avatar -->
    <div class="w-20 h-20 rounded-full border-[3px] border-white/40 bg-white/20 flex items-center justify-center text-[2.2rem] font-extrabold flex-shrink-0 overflow-hidden shadow-lg">
      <?php if (!empty($profile['avatar'])): ?>
        <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($profile['avatar'], ENT_QUOTES) ?>" alt="Avatar" class="w-full h-full object-cover">
      <?php else: ?>
        <?= mb_strtoupper(mb_substr($profile['name'], 0, 1)) ?>
      <?php endif; ?>
    </div>

    <!-- Info -->
    <div class="flex-1 min-w-0">
      <div class="flex items-start justify-between gap-3 flex-wrap">
        <h1 class="text-2xl font-black text-white flex items-center gap-2 m-0">
          <?= htmlspecialchars($profile['name'], ENT_QUOTES) ?>
          <?php if (!empty($profile['is_student_verified'])): ?>
            <span title="Sinh viên đã xác thực" class="text-lg">🛡️</span>
          <?php endif; ?>
        </h1>
        <?php if (isset($user) && $user['id'] !== $profile['id']): ?>
          <button onclick="document.getElementById('reportUserModal').classList.remove('hidden'); document.getElementById('reportUserModal').classList.add('flex');"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition-all border border-white/30 cursor-pointer backdrop-blur-sm">
            <i class="bi bi-flag-fill"></i>Tố cáo
          </button>
        <?php endif; ?>
      </div>
      <div class="text-sm text-white/80 mt-1 flex items-center gap-1.5">
        <i class="bi bi-person-badge"></i>Sinh viên · Tham gia <?= date('m/Y', strtotime($profile['created_at'])) ?>
      </div>
      <!-- Stars + Stats -->
      <div class="flex items-center flex-wrap gap-5 mt-4">
        <div class="text-center">
          <div class="text-amber-300 text-lg leading-none mb-0.5">
            <?php
              $avg = $stats['avg'];
              for ($i = 1; $i <= 5; $i++) {
                echo $i <= round($avg)
                  ? '<i class="bi bi-star-fill"></i>'
                  : '<i class="bi bi-star" style="color:rgba(255,255,255,.3)"></i>';
              }
            ?>
          </div>
          <div class="text-xs text-white/70"><?= $stats['avg'] ?> / 5 trung bình</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-black leading-none"><?= $stats['count'] ?></div>
          <div class="text-xs text-white/70">Đánh giá nhận được</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ─── Giới thiệu ───────────────────────────────────────────── -->
  <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border p-6 mb-6 shadow-sm animate-[fadeInUp_0.5s_ease-out_both]">
    <h6 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest mb-4 flex items-center gap-2">
      <i class="bi bi-person-lines-fill"></i>Giới thiệu
    </h6>

    <?php if (!empty($profile['bio'])): ?>
      <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-5 italic border-l-[3px] border-indigo-200 dark:border-indigo-700 pl-4">
        "<?= nl2br(htmlspecialchars($profile['bio'], ENT_QUOTES)) ?>"
      </p>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <?php if (!empty($profile['university'])): ?>
      <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
          <i class="bi bi-building text-indigo-600 dark:text-indigo-400"></i>
        </div>
        <div>
          <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Trường / Khoa</div>
          <div class="text-sm font-semibold text-gray-800 dark:text-dark-text"><?= htmlspecialchars($profile['university'], ENT_QUOTES) ?></div>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($profile['dormitory_address'])): ?>
      <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
          <i class="bi bi-geo-alt text-emerald-600 dark:text-emerald-400"></i>
        </div>
        <div>
          <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Khu vực / KTX</div>
          <div class="text-sm font-semibold text-gray-800 dark:text-dark-text"><?= htmlspecialchars($profile['dormitory_address'], ENT_QUOTES) ?></div>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($profile['social_contact'])): ?>
      <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
          <i class="bi bi-link-45deg text-sky-600 dark:text-sky-400"></i>
        </div>
        <div>
          <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Liên hệ MXH</div>
          <a href="<?= str_starts_with($profile['social_contact'], 'http') ? htmlspecialchars($profile['social_contact'], ENT_QUOTES) : 'https://' . htmlspecialchars($profile['social_contact'], ENT_QUOTES) ?>"
             target="_blank" rel="noopener noreferrer"
             class="text-sm font-semibold text-primary no-underline hover:underline break-all">
            <?= htmlspecialchars($profile['social_contact'], ENT_QUOTES) ?>
          </a>
        </div>
      </div>
      <?php endif; ?>

      <?php if (!empty($profile['available_time'])): ?>
      <div class="flex items-start gap-3">
        <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
          <i class="bi bi-clock text-amber-600 dark:text-amber-400"></i>
        </div>
        <div>
          <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Thời gian online</div>
          <div class="text-sm font-semibold text-gray-800 dark:text-dark-text"><?= htmlspecialchars($profile['available_time'], ENT_QUOTES) ?></div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <?php if (empty($profile['bio']) && empty($profile['university']) && empty($profile['dormitory_address']) && empty($profile['social_contact']) && empty($profile['available_time'])): ?>
      <div class="text-center py-6 text-gray-400 text-sm">Người dùng này chưa cập nhật thông tin cá nhân.</div>
    <?php endif; ?>
  </div>

  <!-- ─── Đánh giá ─────────────────────────────────────────────── -->
  <div class="font-extrabold text-base text-gray-800 dark:text-dark-text mb-4 flex items-center gap-2 animate-[fadeIn_0.6s_ease-out_both]">
    <i class="bi bi-star text-amber-400"></i>Đánh giá từ người mua
  </div>

  <?php if (empty($ratings)): ?>
    <div class="text-center py-16 text-gray-400 flex flex-col items-center gap-3 bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border shadow-sm">
      <i class="bi bi-star text-5xl text-gray-200 dark:text-gray-700"></i>
      <div class="text-sm font-medium">Người bán này chưa có đánh giá nào.</div>
    </div>
  <?php else: ?>
    <div class="space-y-3">
      <?php foreach ($ratings as $r): ?>
        <div class="bg-white dark:bg-dark-card rounded-[16px] border border-light-border dark:border-dark-border p-5 shadow-sm hover:shadow-md transition-shadow animate-[fadeInUp_0.4s_ease-out_both]">
          <div class="flex justify-between items-start mb-2 gap-3">
            <div>
              <div class="font-bold text-sm text-gray-800 dark:text-dark-text"><?= htmlspecialchars($r['rater_name'], ENT_QUOTES) ?></div>
              <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                <i class="bi bi-box-seam"></i><?= htmlspecialchars($r['product_title'], ENT_QUOTES) ?>
              </div>
            </div>
            <div class="text-right flex-shrink-0">
              <div class="text-amber-400 text-sm leading-none">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="bi bi-star<?= $i <= $r['stars'] ? '-fill' : '' ?>"></i>
                <?php endfor; ?>
              </div>
              <div class="text-[11px] text-gray-400 mt-1"><?= date('d/m/Y', strtotime($r['created_at'])) ?></div>
            </div>
          </div>
          <?php if ($r['comment']): ?>
            <div class="text-sm text-gray-600 dark:text-gray-400 border-l-[3px] border-gray-200 dark:border-gray-700 pl-3 leading-relaxed mt-3">
              <?= nl2br(htmlspecialchars($r['comment'], ENT_QUOTES)) ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- ─── Modal Tố Cáo Người Dùng ─────────────────────────────────────── -->
<div id="reportUserModal" class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200"
     onclick="if(event.target===this){this.classList.add('hidden','opacity-0');this.classList.remove('flex');}">
  <div class="bg-white dark:bg-dark-card rounded-[22px] shadow-[0_40px_100px_rgba(0,0,0,0.3)] w-full max-w-[480px] overflow-hidden transform scale-95 opacity-0 transition-all duration-300 m-4"
       id="reportUserModalBox">
    <!-- Header -->
    <div class="bg-gradient-to-r from-red-500 to-red-600 p-6 flex items-center justify-between">
      <h5 class="font-black text-white text-base m-0 flex items-center gap-2">
        <i class="bi bi-shield-exclamation"></i>Báo cáo người dùng
      </h5>
      <button onclick="closeReportModal()" class="text-white/80 hover:text-white transition-colors bg-transparent border-0 cursor-pointer text-xl leading-none">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <form action="<?= $appUrl ?>/reports/store" method="POST">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>">
      <input type="hidden" name="target_user_id" value="<?= $profile['id'] ?>">

      <div class="p-6">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
          Bạn đang tố cáo tài khoản <strong class="text-gray-800 dark:text-dark-text"><?= htmlspecialchars($profile['name'], ENT_QUOTES) ?></strong>. Vui lòng cung cấp chi tiết vi phạm để được xử lý nhanh nhất.
        </p>

        <div class="mb-4">
          <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Lý do báo cáo <span class="text-red-500">*</span></label>
          <select name="reason" required
                  class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-red-500 text-sm font-medium text-gray-800 dark:text-dark-text transition-all">
            <option value="">-- Chọn lý do --</option>
            <option value="Lừa đảo">Người dùng có dấu hiệu lừa đảo</option>
            <option value="Hàng giả / Trái pháp luật">Mua bán hàng giả / cấm</option>
            <option value="Tài khoản giả mạo">Tài khoản giả mạo / Spam</option>
            <option value="Ngôn từ đe dọa">Ngôn từ đe dọa / Quấy rối</option>
            <option value="Khác">Lý do khác</option>
          </select>
        </div>

        <div class="mb-2">
          <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Chi tiết vi phạm <span class="text-red-500">*</span></label>
          <textarea name="description" rows="4" placeholder="Mô tả sự việc..." required
                    class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-red-500 text-sm font-medium text-gray-800 dark:text-dark-text resize-y transition-all"></textarea>
        </div>
      </div>

      <div class="px-6 pb-6 flex gap-3">
        <button type="button" onclick="closeReportModal()"
                class="flex-1 py-3 rounded-xl bg-gray-100 dark:bg-dark-2 text-gray-500 dark:text-gray-400 font-bold text-sm hover:bg-gray-200 dark:hover:bg-dark-border transition-colors border-0 cursor-pointer">
          Hủy
        </button>
        <button type="submit"
                class="flex-1 py-3 rounded-xl bg-gradient-to-r from-red-500 to-red-600 text-white font-extrabold text-sm hover:shadow-[0_8px_24px_rgba(239,68,68,0.4)] hover:-translate-y-0.5 transition-all border-0 cursor-pointer flex items-center justify-center gap-2">
          <i class="bi bi-send-fill"></i>Gửi báo cáo
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function closeReportModal() {
  const m = document.getElementById('reportUserModal');
  const box = document.getElementById('reportUserModalBox');
  m.classList.add('opacity-0'); box.classList.add('scale-95', 'opacity-0');
  setTimeout(() => { m.classList.add('hidden'); m.classList.remove('flex'); }, 300);
}
// Auto-open animation on show
const rm = document.getElementById('reportUserModal');
const observer = new MutationObserver(() => {
  if (!rm.classList.contains('hidden')) {
    const box = document.getElementById('reportUserModalBox');
    void rm.offsetWidth;
    rm.classList.remove('opacity-0');
    box.classList.remove('scale-95', 'opacity-0');
  }
});
observer.observe(rm, { attributes: true, attributeFilter: ['class'] });
</script>
