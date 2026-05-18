<?php
/**
 * View: Trang chủ — Premium Edition
 * Hero + Giveaway + Đấu giá hot + Danh mục + Sản phẩm mới + CTA
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$user   = $_SESSION['user'] ?? null;

function hp(int $price): string {
    return number_format($price, 0, ',', '.') . 'đ';
}
?>

<style>
@keyframes blobFloat {
  from { transform: translate(0,0) scale(1); }
  to   { transform: translate(40px,30px) scale(1.12); }
}
@keyframes particleFloat {
  from { transform: translateY(0); opacity:.3; }
  to   { transform: translateY(-30px); opacity:.9; }
}
@keyframes cardFloat {
  from { transform: translateY(0); }
  to   { transform: translateY(-12px); }
}
@keyframes bounceDown {
  from { transform:translateY(0); }
  to   { transform:translateY(5px); }
}
@keyframes badgePulse {
  0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.4);}
  50%{box-shadow:0 0 0 6px rgba(239,68,68,0);}
}
</style>

<!-- ─── BANNERS SLIDER ────────────────────────────────────────────── -->
<?php if (!empty($banners)): ?>
<section class="pt-6 bg-gray-50 dark:bg-dark-bg">
  <div class="container mx-auto px-4">
    <div id="homeBannerSlider" class="relative rounded-2xl overflow-hidden shadow-2xl shadow-primary/10 group">
      <!-- Slides -->
      <div id="sliderTrack" class="flex transition-transform duration-700 ease-in-out">
        <?php foreach ($banners as $index => $b): ?>
          <div class="min-w-full relative">
            <?php if (!empty($b['link'])): ?>
              <a href="<?= htmlspecialchars($b['link'], ENT_QUOTES) ?>" class="block w-full">
                <img src="<?= htmlspecialchars($appUrl . $b['image'], ENT_QUOTES) ?>" class="block w-full aspect-[21/9] object-cover max-h-[400px]" alt="<?= htmlspecialchars($b['title'] ?? 'Banner', ENT_QUOTES) ?>">
              </a>
            <?php else: ?>
              <img src="<?= htmlspecialchars($appUrl . $b['image'], ENT_QUOTES) ?>" class="block w-full aspect-[21/9] object-cover max-h-[400px]" alt="<?= htmlspecialchars($b['title'] ?? 'Banner', ENT_QUOTES) ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <!-- Prev/Next controls -->
      <button onclick="sliderPrev()" class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/30 hover:bg-black/50 text-white flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 border-0 cursor-pointer backdrop-blur-sm z-10">
        <i class="bi bi-chevron-left text-lg"></i>
      </button>
      <button onclick="sliderNext()" class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/30 hover:bg-black/50 text-white flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 border-0 cursor-pointer backdrop-blur-sm z-10">
        <i class="bi bi-chevron-right text-lg"></i>
      </button>
      <!-- Dots -->
      <div id="sliderDots" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2 z-10">
        <?php foreach ($banners as $index => $b): ?>
          <button onclick="sliderGoTo(<?= $index ?>)" class="slider-dot w-2 h-2 rounded-full transition-all border-0 cursor-pointer <?= $index === 0 ? 'bg-white w-6' : 'bg-white/50 hover:bg-white/70' ?>" data-dot="<?= $index ?>"></button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<script>
(function() {
  const track = document.getElementById('sliderTrack');
  const dots = document.querySelectorAll('.slider-dot');
  const total = <?= count($banners) ?>;
  let current = 0;
  let timer;
  function goTo(idx) {
    current = (idx + total) % total;
    track.style.transform = `translateX(-${current * 100}%)`;
    dots.forEach((d, i) => {
      d.classList.toggle('bg-white', i === current);
      d.classList.toggle('w-6', i === current);
      d.classList.toggle('bg-white/50', i !== current);
      d.classList.toggle('w-2', i !== current);
    });
  }
  window.sliderNext = () => { goTo(current + 1); resetTimer(); };
  window.sliderPrev = () => { goTo(current - 1); resetTimer(); };
  window.sliderGoTo = (i) => { goTo(i); resetTimer(); };
  function resetTimer() { clearInterval(timer); timer = setInterval(() => goTo(current + 1), 4000); }
  resetTimer();
})();
</script>
<?php endif; ?>

<!-- ─── HERO ─────────────────────────────────────────────────────── -->
<section class="relative min-h-[640px] bg-gradient-to-br from-[#0f0c29] via-[#302b63] to-[#24243e] overflow-hidden">
  <!-- Animated blobs background -->
  <div class="absolute rounded-full blur-[90px] opacity-50 bg-indigo-500 w-[600px] h-[600px] -top-[200px] -left-[150px] animate-[blobFloat_14s_ease-in-out_infinite_alternate]"></div>
  <div class="absolute rounded-full blur-[90px] opacity-50 bg-pink-500 w-[450px] h-[450px] -bottom-[180px] -right-[100px] animate-[blobFloat_11s_ease-in-out_infinite_alternate_-4s]"></div>
  <div class="absolute rounded-full blur-[90px] opacity-50 bg-purple-500 w-[300px] h-[300px] top-[40%] left-[40%] animate-[blobFloat_9s_ease-in-out_infinite_alternate_-7s]"></div>
  
  <!-- Floating particles -->
  <div class="absolute w-2 h-2 bg-white/35 rounded-full animate-[particleFloat_6s_ease-in-out_infinite_alternate] top-[20%] left-[8%]"></div>
  <div class="absolute w-2 h-2 bg-white/35 rounded-full animate-[particleFloat_6s_ease-in-out_infinite_alternate_1.5s] top-[60%] left-[15%]"></div>
  <div class="absolute w-2 h-2 bg-white/35 rounded-full animate-[particleFloat_6s_ease-in-out_infinite_alternate_0.8s] top-[35%] right-[10%]"></div>
  <div class="absolute w-2 h-2 bg-white/35 rounded-full animate-[particleFloat_6s_ease-in-out_infinite_alternate_2.2s] top-[75%] right-[20%]"></div>

  <div class="container mx-auto px-4 relative z-10">
    <div class="flex flex-col lg:flex-row items-center min-h-[580px] pt-20 pb-16 gap-12">
      <!-- Left: Text -->
      <div class="lg:w-1/2 text-center lg:text-left animate-[fadeInUp_0.5s_ease-out_both]">
        <div class="inline-block bg-white/10 backdrop-blur-md text-purple-300 px-4 py-1.5 rounded-full text-xs font-bold border border-white/20 tracking-wider mb-6">
          <i class="bi bi-lightning-fill mr-1"></i>Đấu giá ngược &middot; Mua bán &middot; Giveaway
        </div>
        <h1 class="text-[clamp(2.4rem,5.5vw,4rem)] font-black text-white leading-tight tracking-tight mb-4">
          Chợ Sinh Viên<br>
          <span class="bg-gradient-to-r from-purple-400 via-pink-400 to-orange-400 bg-clip-text text-transparent">KTX Đại học Quốc gia</span>
        </h1>
        <p class="text-lg text-white/75 leading-relaxed max-w-lg mx-auto lg:mx-0 mb-8">
          Mua bán sách giáo trình, đồ dùng với giá hợp lý nhất.<br>
          Tham gia đấu giá ngược — <strong class="text-purple-300">giá tự giảm</strong>, mua ngay khi ưng!
        </p>
        <div class="flex flex-wrap gap-4 justify-center lg:justify-start">
          <a href="<?= $appUrl ?>/products" class="group relative overflow-hidden inline-flex items-center bg-gradient-to-br from-indigo-500 to-purple-500 text-white font-extrabold text-base px-7 py-3 rounded-xl border-0 shadow-[0_8px_28px_rgba(99,102,241,0.5)] transition-all hover:-translate-y-1 hover:shadow-[0_14px_38px_rgba(99,102,241,0.6)] no-underline">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-500 ease-in-out"></div>
            <i class="bi bi-bag-fill mr-2"></i>Mua sắm ngay
          </a>
          <?php if ($user): ?>
            <a href="<?= $appUrl ?>/products/create" class="inline-flex items-center bg-white/10 backdrop-blur-md text-white font-bold text-base px-7 py-3 rounded-xl border-2 border-white/30 transition-all hover:bg-white/20 hover:-translate-y-1 no-underline">
              <i class="bi bi-plus-circle mr-2"></i>Đăng bán
            </a>
          <?php else: ?>
            <a href="<?= $appUrl ?>/register" class="inline-flex items-center bg-white/10 backdrop-blur-md text-white font-bold text-base px-7 py-3 rounded-xl border-2 border-white/30 transition-all hover:bg-white/20 hover:-translate-y-1 no-underline">
              <i class="bi bi-person-plus mr-2"></i>Đăng ký miễn phí
            </a>
          <?php endif; ?>
        </div>

        <!-- Stats row -->
        <div class="inline-flex items-center gap-0 bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl py-4 px-7 mt-10 animate-[fadeInUp_0.5s_ease-out_0.2s_both]">
          <div class="text-center px-5">
            <span class="block text-2xl font-black text-white leading-none"><?= number_format($stats['products']) ?>+</span>
            <span class="block text-xs text-white/60 mt-1">Sản phẩm</span>
          </div>
          <div class="w-px h-10 bg-white/20"></div>
          <div class="text-center px-5">
            <span class="block text-2xl font-black text-white leading-none"><?= number_format($stats['users']) ?>+</span>
            <span class="block text-xs text-white/60 mt-1">Sinh viên</span>
          </div>
          <div class="w-px h-10 bg-white/20"></div>
          <div class="text-center px-5">
            <span class="block text-2xl font-black text-white leading-none"><?= number_format($stats['tx']) ?>+</span>
            <span class="block text-xs text-white/60 mt-1">GD hôm nay</span>
          </div>
        </div>
      </div>

      <!-- Right: Feature Card -->
      <div class="hidden lg:flex lg:w-1/2 justify-center animate-[fadeInUp_0.5s_ease-out_0.3s_both]">
        <div class="w-full max-w-[380px] bg-white/5 backdrop-blur-xl border border-white/15 rounded-3xl p-8 animate-[cardFloat_5s_ease-in-out_infinite_alternate]">
          <div class="flex items-center gap-4 py-4 border-b border-white/10">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl text-white flex-shrink-0 bg-gradient-to-br from-indigo-500 to-purple-500">
              <i class="bi bi-lightning-fill"></i>
            </div>
            <div>
              <div class="font-bold text-white text-sm">Đấu giá ngược</div>
              <div class="text-xs text-white/50 mt-0.5">Giá giảm dần — mua ngay khi ưng</div>
            </div>
          </div>
          <div class="flex items-center gap-4 py-4 border-b border-white/10">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl text-white flex-shrink-0 bg-gradient-to-br from-pink-500 to-orange-500">
              <i class="bi bi-gift-fill"></i>
            </div>
            <div>
              <div class="font-bold text-white text-sm">Sự kiện Giveaway</div>
              <div class="text-xs text-white/50 mt-0.5">Quay số trúng thưởng mỗi tuần</div>
            </div>
          </div>
          <div class="flex items-center gap-4 py-4 border-b border-white/10">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl text-white flex-shrink-0 bg-gradient-to-br from-cyan-500 to-blue-500">
              <i class="bi bi-credit-card-2-front-fill"></i>
            </div>
            <div>
              <div class="font-bold text-white text-sm">Thanh toán đa dạng</div>
              <div class="text-xs text-white/50 mt-0.5">ZaloPay, chuyển khoản, COD</div>
            </div>
          </div>
          <div class="flex items-center gap-4 py-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl text-white flex-shrink-0 bg-gradient-to-br from-emerald-500 to-green-600">
              <i class="bi bi-shield-fill-check"></i>
            </div>
            <div>
              <div class="font-bold text-white text-sm">Bảo mật & An toàn</div>
              <div class="text-xs text-white/50 mt-0.5">Tài khoản xác thực & CSRF protect</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ─── GIVEAWAY EVENT ───────────────────────────────────────────── -->
<?php if (!empty($giveaway)): ?>
<section class="relative overflow-hidden py-20 bg-gradient-to-br from-[#0f0c29] via-[#302b63] to-[#1a055c]" id="giveaway">
  <div class="absolute w-[450px] h-[450px] rounded-full top-[-150px] right-[-80px] bg-[radial-gradient(circle,rgba(139,92,246,0.3)_0%,transparent_65%)] pointer-events-none"></div>
  <div class="absolute w-[350px] h-[350px] rounded-full bottom-[-120px] left-[-50px] bg-[radial-gradient(circle,rgba(236,72,153,0.25)_0%,transparent_65%)] pointer-events-none"></div>
  
  <div class="container mx-auto px-4 relative z-10">
    <div class="flex flex-col md:flex-row items-center justify-center gap-8 text-center md:text-left">
      <div class="flex-shrink-0">
        <?php if ($giveaway['image']): ?>
          <div class="relative inline-block">
            <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($giveaway['image']) ?>" alt="" 
                 class="w-44 h-44 object-cover rounded-2xl shadow-[0_24px_64px_rgba(0,0,0,0.55),0_0_0_3px_rgba(251,191,36,0.35)]">
            <div class="absolute -top-3 -right-3 w-10 h-10 bg-gradient-to-br from-amber-500 to-red-500 rounded-full flex items-center justify-center text-lg">🎁</div>
          </div>
        <?php else: ?>
          <div class="w-40 h-40 bg-white/10 rounded-2xl flex items-center justify-center text-6xl text-white/40">
            <i class="bi bi-gift-fill"></i>
          </div>
        <?php endif; ?>
      </div>
      <div class="md:w-[58%]">
        <div class="inline-block bg-amber-400/10 border border-amber-400/30 text-amber-400 text-xs font-extrabold px-4 py-1.5 rounded-full tracking-wider mb-3">
          <i class="bi bi-stars mr-1"></i>SỰ KIỆN ĐẶC BIỆT
        </div>
        <h2 class="text-[clamp(1.5rem,3.5vw,2.2rem)] font-black text-white mb-3">
          <i class="bi bi-gift-fill text-amber-400 mr-2"></i><?= htmlspecialchars($giveaway['title']) ?>
        </h2>
        <p class="text-white/65 text-base max-w-[520px] leading-relaxed mb-4 mx-auto md:mx-0">
          <?= nl2br(htmlspecialchars($giveaway['description'])) ?>
        </p>
        <div class="text-sm text-white/50 mb-6">
          <i class="bi bi-clock-history mr-1"></i>
          Kết thúc: <strong class="text-white/90"><?= date('d/m/Y H:i', strtotime($giveaway['end_time'])) ?></strong>
        </div>
        
        <?php if (!$user): ?>
          <a href="<?= $appUrl ?>/login" class="inline-flex items-center bg-gradient-to-br from-amber-500 to-red-500 text-white font-extrabold px-8 py-3.5 rounded-xl text-base shadow-[0_8px_28px_rgba(245,158,11,0.45)] hover:-translate-y-1 hover:shadow-[0_14px_36px_rgba(245,158,11,0.55)] transition-all no-underline border-0 cursor-pointer">
            <i class="bi bi-box-arrow-in-right mr-2"></i>Đăng nhập để tham gia
          </a>
        <?php elseif ($hasJoinedGiveaway): ?>
          <button class="inline-flex items-center bg-emerald-500/10 border-2 border-emerald-500/40 text-emerald-400 font-extrabold px-8 py-3.5 rounded-xl text-base cursor-not-allowed">
            <i class="bi bi-check-circle-fill mr-2"></i>Đã đăng ký tham gia ✓
          </button>
        <?php else: ?>
          <button id="btnJoinGiveaway" data-id="<?= $giveaway['id'] ?>" class="inline-flex items-center bg-gradient-to-br from-indigo-500 to-purple-500 text-white font-extrabold px-8 py-3.5 rounded-xl text-base shadow-[0_8px_28px_rgba(99,102,241,0.45)] hover:-translate-y-1 hover:shadow-[0_14px_36px_rgba(99,102,241,0.55)] transition-all border-0 cursor-pointer">
            <i class="bi bi-controller mr-2"></i>Tham gia vòng quay ngay!
          </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('btnJoinGiveaway');
  if (btn) {
    btn.addEventListener('click', async () => {
      btn.disabled = true;
      btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Đang xử lý...';
      try {
        const formData = new FormData();
        formData.append('giveaway_id', btn.dataset.id);
        formData.append('_csrf', '<?= htmlspecialchars($this->csrfToken() ?? "") ?>');
        const response = await fetch('<?= $appUrl ?>/api/giveaways/join', { method: 'POST', body: formData });
        const result = await response.json();
        if (result.success) { alert(result.message || 'Đăng ký thành công!'); window.location.reload(); }
        else {
          alert((result.error && result.error.message) || 'Có lỗi xảy ra.');
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-controller mr-2"></i>Tham gia vòng quay ngay!';
        }
      } catch (err) {
        alert('Lỗi mạng, vui lòng thử lại.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-controller mr-2"></i>Tham gia vòng quay ngay!';
      }
    });
  }
});
</script>
<?php endif; ?>

<!-- ─── ĐẤU GIÁ HOT ─────────────────────────────────────────────── -->
<?php if (!empty($auctionProducts)): ?>
<section class="py-20 bg-gray-50 dark:bg-dark-bg">
  <div class="container mx-auto px-4">
    <div class="flex justify-between items-end mb-12">
      <div>
        <span class="inline-block bg-red-500/10 border border-red-500/20 text-red-500 text-xs font-extrabold px-4 py-1.5 rounded-full tracking-wider mb-2">
          <i class="bi bi-lightning-charge-fill mr-1"></i>ĐANG DIỄN RA
        </span>
        <h2 class="text-3xl font-extrabold text-gray-800 dark:text-dark-text m-0">Đấu giá đang HOT 🔥</h2>
        <p class="text-sm text-gray-500 mt-2 mb-0">Giá tự giảm theo thời gian · Mua ngay trước khi người khác nhanh hơn!</p>
      </div>
      <a href="<?= $appUrl ?>/products?type=auction" class="hidden md:flex items-center gap-1.5 text-primary font-bold text-sm no-underline transition-all hover:gap-2.5">
        Xem tất cả <i class="bi bi-arrow-right"></i>
      </a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($auctionProducts as $idx => $p): ?>
        <div class="animate-[fadeInUp_0.5s_ease-out_both]" style="animation-delay: <?= $idx * 100 ?>ms">
          <a href="<?= $appUrl ?>/products/show?id=<?= $p['id'] ?>" class="block no-underline group">
            <div class="bg-white dark:bg-dark-card rounded-2xl border-2 border-light-border dark:border-dark-border overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_20px_50px_rgba(239,68,68,0.18)] hover:border-red-300">
              <div class="relative h-48 overflow-hidden bg-gray-100 dark:bg-dark-2">
                <?php if ($p['image']): ?>
                  <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($p['image'], ENT_QUOTES) ?>" alt="" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                <?php else: ?>
                  <div class="flex items-center justify-center h-full text-5xl text-red-500/20"><i class="bi bi-lightning-fill"></i></div>
                <?php endif; ?>
                <div class="absolute top-3 left-3 bg-gradient-to-br from-red-600 to-red-500 text-white text-[11px] font-bold px-3 py-1 rounded-full animate-[badgePulse_1.5s_ease-in-out_infinite]">
                  <i class="bi bi-lightning-fill mr-1"></i>Đấu giá
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/55 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </div>
              
              <div class="p-5">
                <div class="text-[11px] text-gray-400 font-bold uppercase tracking-wider mb-1.5"><?= htmlspecialchars($p['category_name'], ENT_QUOTES) ?></div>
                <h6 class="font-bold text-[15px] text-gray-800 dark:text-dark-text leading-snug line-clamp-2 mb-3"><?= htmlspecialchars($p['title'], ENT_QUOTES) ?></h6>
                
                <div class="flex justify-between items-center">
                  <div>
                    <div class="text-xl font-black text-red-500 font-mono"><?= hp($p['current_price'] ?? $p['start_price']) ?></div>
                    <div class="text-xs text-gray-400">Gốc: <s class="opacity-75"><?= hp($p['start_price']) ?></s></div>
                  </div>
                  <div class="flex flex-col items-center gap-0.5 text-red-500 text-xs font-bold">
                    <i class="bi bi-arrow-down-circle-fill text-[22px] animate-[bounceDown_0.8s_ease-in-out_infinite_alternate]"></i>
                    <span>Đang giảm</span>
                  </div>
                </div>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ─── DANH MỤC ────────────────────────────────────────────────── -->
<section class="py-20 bg-white dark:bg-dark-card">
  <div class="container mx-auto px-4">
    <div class="text-center mb-12">
      <span class="inline-block bg-primary/10 border border-primary/20 text-primary text-xs font-extrabold px-4 py-1.5 rounded-full tracking-wider mb-2">
        <i class="bi bi-grid-fill mr-1"></i>DANH MỤC
      </span>
      <h2 class="text-3xl font-extrabold text-gray-800 dark:text-dark-text m-0">Mua sắm theo danh mục</h2>
      <p class="text-gray-500 mt-2 text-[15px]">Chọn danh mục yêu thích để tìm nhanh hơn</p>
    </div>
    
    <?php
    $catColors = [
      ['bg'=>'from-indigo-500 to-purple-500','shadow'=>'shadow-indigo-500/35'],
      ['bg'=>'from-pink-500 to-orange-500','shadow'=>'shadow-pink-500/35'],
      ['bg'=>'from-cyan-500 to-blue-500','shadow'=>'shadow-cyan-500/35'],
      ['bg'=>'from-emerald-500 to-green-600','shadow'=>'shadow-emerald-500/35'],
      ['bg'=>'from-amber-500 to-red-500','shadow'=>'shadow-amber-500/35'],
      ['bg'=>'from-purple-500 to-pink-500','shadow'=>'shadow-purple-500/35'],
      ['bg'=>'from-teal-500 to-indigo-500','shadow'=>'shadow-teal-500/35'],
      ['bg'=>'from-rose-500 to-orange-500','shadow'=>'shadow-rose-500/35'],
    ];
    $catIdx = 0;
    ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 justify-center">
      <?php foreach ($categories as $cat):
        $clr = $catColors[$catIdx % count($catColors)]; $catIdx++;
      ?>
        <a href="<?= $appUrl ?>/products?category=<?= $cat['id'] ?>" class="no-underline group block">
          <div class="bg-gray-50 dark:bg-dark-2 rounded-[20px] border-2 border-light-border dark:border-dark-border p-6 text-center transition-all duration-300 hover:-translate-y-2 hover:border-transparent hover:shadow-[0_18px_42px] hover:<?= $clr['shadow'] ?>">
            <div class="w-[58px] h-[58px] rounded-2xl mx-auto mb-3 flex items-center justify-center text-2xl text-white bg-gradient-to-br <?= $clr['bg'] ?> shadow-[0_8px_20px] <?= $clr['shadow'] ?>">
              <i class="bi <?= htmlspecialchars($cat['icon'] ?? 'bi-tag', ENT_QUOTES) ?>"></i>
            </div>
            <div class="text-[13px] font-bold text-gray-800 dark:text-dark-text leading-tight group-hover:text-primary transition-colors"><?= htmlspecialchars($cat['name'], ENT_QUOTES) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
      
      <!-- Nút "Tất cả" -->
      <a href="<?= $appUrl ?>/products" class="no-underline group block">
        <div class="bg-gray-50 dark:bg-dark-2 rounded-[20px] border-2 border-light-border dark:border-dark-border p-6 text-center transition-all duration-300 hover:-translate-y-2 hover:border-transparent hover:shadow-[0_12px_32px_rgba(100,116,139,0.2)]">
          <div class="w-[58px] h-[58px] rounded-2xl mx-auto mb-3 flex items-center justify-center text-2xl text-gray-400 bg-white dark:bg-dark-card border border-light-border dark:border-dark-border">
            <i class="bi bi-grid"></i>
          </div>
          <div class="text-[13px] font-bold text-gray-500 leading-tight group-hover:text-gray-800 dark:group-hover:text-white transition-colors">Tất cả</div>
        </div>
      </a>
    </div>
  </div>
</section>

<!-- ─── SẢN PHẨM MỚI NHẤT ──────────────────────────────────────── -->
<section class="py-20 bg-gray-50 dark:bg-dark-bg">
  <div class="container mx-auto px-4">
    <div class="flex justify-between items-end mb-12">
      <div>
        <span class="inline-block bg-primary/10 border border-primary/20 text-primary text-xs font-extrabold px-4 py-1.5 rounded-full tracking-wider mb-2">
          <i class="bi bi-stars mr-1"></i>MỚI NHẤT
        </span>
        <h2 class="text-3xl font-extrabold text-gray-800 dark:text-dark-text m-0">Sản phẩm mới nhất</h2>
      </div>
      <a href="<?= $appUrl ?>/products" class="hidden md:flex items-center gap-1.5 text-primary font-bold text-sm no-underline transition-all hover:gap-2.5">
        Xem tất cả <i class="bi bi-arrow-right"></i>
      </a>
    </div>

    <?php if (empty($featuredProducts)): ?>
      <div class="text-center py-16 bg-white dark:bg-dark-card rounded-3xl border border-light-border dark:border-dark-border">
        <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto text-4xl text-primary mb-4">
          <i class="bi bi-bag-x"></i>
        </div>
        <h5 class="text-gray-500 font-bold mb-4">Chưa có sản phẩm nào</h5>
        <?php if ($user): ?>
          <a href="<?= $appUrl ?>/products/create" class="inline-flex items-center bg-primary text-white font-bold px-6 py-2.5 rounded-full hover:brightness-110 transition-all no-underline">
            <i class="bi bi-plus-lg mr-2"></i>Đăng sản phẩm đầu tiên
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-5">
        <?php foreach ($featuredProducts as $idx => $p): ?>
          <div class="animate-[fadeInUp_0.5s_ease-out_both]" style="animation-delay:<?= $idx * 60 ?>ms">
            <a href="<?= $appUrl ?>/products/show?id=<?= $p['id'] ?>" class="block h-full no-underline group">
              <div class="bg-white dark:bg-dark-card rounded-[18px] border-2 border-light-border dark:border-dark-border overflow-hidden h-full flex flex-col transition-all duration-300 hover:-translate-y-2 hover:shadow-[0_18px_42px_rgba(99,102,241,0.14)] hover:border-indigo-200 dark:hover:border-indigo-500/30">
                <div class="relative h-40 bg-gray-100 dark:bg-dark-2 overflow-hidden flex-shrink-0">
                  <?php if ($p['image']): ?>
                    <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($p['image'], ENT_QUOTES) ?>" alt="" class="w-full h-full object-cover transition-transform duration-400 group-hover:scale-110">
                  <?php else: ?>
                    <div class="flex items-center justify-center h-full text-4xl text-gray-300"><i class="bi bi-image"></i></div>
                  <?php endif; ?>
                  
                  <?php if ($p['type'] === 'auction'): ?>
                    <span class="absolute top-2 left-2 bg-gradient-to-br from-red-500 to-orange-500 text-white text-[11px] font-extrabold px-2.5 py-1 rounded-full shadow-md">⚡ Đấu giá</span>
                  <?php elseif ($p['type'] === 'exchange'): ?>
                    <span class="absolute top-2 left-2 bg-gradient-to-br from-cyan-500 to-blue-500 text-white text-[11px] font-extrabold px-2.5 py-1 rounded-full shadow-md">🔄 Trao đổi</span>
                  <?php endif; ?>
                </div>
                
                <div class="p-3.5 flex flex-col flex-1">
                  <div class="text-[11px] text-gray-400 font-bold uppercase tracking-wider mb-1"><?= htmlspecialchars($p['category_name'], ENT_QUOTES) ?></div>
                  <div class="text-[14px] font-bold text-gray-800 dark:text-dark-text leading-snug line-clamp-2 mb-2 flex-1 group-hover:text-primary transition-colors"><?= htmlspecialchars($p['title'], ENT_QUOTES) ?></div>
                  
                  <div class="text-base font-black text-primary font-mono mt-auto">
                    <?php if ($p['type'] === 'auction'): ?>
                      <span class="text-red-500"><?= hp($p['current_price'] ?? $p['start_price']) ?></span>
                    <?php elseif ($p['type'] === 'sale'): ?>
                      <?= hp((int)$p['price']) ?>
                    <?php else: ?>
                      <span class="text-cyan-500 text-sm">Trao đổi</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ─── CTA BANNER ──────────────────────────────────────────────── -->
<?php if (!$user): ?>
<section class="relative overflow-hidden bg-gradient-to-br from-[#1e1b4b] via-[#4f46e5] to-[#7c3aed]">
  <div class="absolute w-[500px] h-[500px] rounded-full top-[-200px] left-[-100px] bg-[radial-gradient(circle,rgba(99,102,241,0.4)_0%,transparent_65%)] pointer-events-none"></div>
  <div class="absolute w-[400px] h-[400px] rounded-full bottom-[-180px] right-[-80px] bg-[radial-gradient(circle,rgba(236,72,153,0.3)_0%,transparent_65%)] pointer-events-none"></div>
  
  <div class="container mx-auto px-4 relative z-10 py-20 text-center">
    <div class="inline-block bg-white/15 border border-white/30 text-indigo-100 text-xs font-extrabold px-4 py-1.5 rounded-full tracking-wider mb-4 shadow-sm">
      <i class="bi bi-rocket-takeoff-fill mr-1"></i>BẮT ĐẦU MIỄN PHÍ
    </div>
    <h2 class="text-[clamp(1.8rem,4vw,2.8rem)] font-black text-white mb-4">
      Sẵn sàng mua sắm thông minh?
    </h2>
    <p class="text-white/75 text-lg max-w-lg mx-auto leading-relaxed mb-10">
      Đăng ký miễn phí, đăng bài trong 2 phút, tiết kiệm hàng trăm nghìn mỗi học kỳ.
    </p>
    
    <div class="flex flex-wrap justify-center gap-4">
      <a href="<?= $appUrl ?>/register" class="inline-flex items-center bg-white text-indigo-600 font-extrabold px-8 py-3.5 rounded-2xl text-base shadow-[0_8px_28px_rgba(0,0,0,0.2)] hover:-translate-y-1 hover:shadow-[0_14px_38px_rgba(0,0,0,0.28)] transition-all no-underline">
        <i class="bi bi-person-plus-fill mr-2"></i>Tạo tài khoản ngay
      </a>
      <a href="<?= $appUrl ?>/products" class="inline-flex items-center bg-white/10 backdrop-blur-md text-white font-bold px-8 py-3.5 rounded-2xl text-base border-2 border-white/30 hover:bg-white/20 hover:-translate-y-1 transition-all no-underline">
        <i class="bi bi-bag mr-2"></i>Khám phá sản phẩm
      </a>
    </div>
  </div>
</section>
<?php endif; ?>
