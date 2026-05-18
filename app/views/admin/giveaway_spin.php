<?php
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
?>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<style>
.spin-container {
    perspective: 1000px;
    margin: 40px auto;
    width: 100%;
    max-width: 600px;
    height: 120px;
    position: relative;
    border-radius: 16px;
    background: #fff;
    box-shadow: inset 0 0 20px rgba(0,0,0,0.1), 0 10px 30px rgba(0,0,0,0.05);
    overflow: hidden;
    border: 4px solid #6366f1;
}
.dark .spin-container {
    background: #1e293b;
    border-color: #818cf8;
}
.spin-track {
    display: flex;
    height: 100%;
    transition: transform 6s cubic-bezier(0.2, 0.8, 0.2, 1);
    transform: translateX(0);
}
.participant-card {
    min-width: 200px;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-right: 2px dashed #e2e8f0;
    background: #f8fafc;
    flex-shrink: 0;
}
.dark .participant-card { background: #1e293b; border-right-color: #334155; }
.participant-card.winner {
    background: #fffbeb;
    border: 3px solid #fbbf24;
}
.participant-avatar {
    width: 48px; height: 48px; border-radius: 50%;
    background: #e2e8f0; color: #475569;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; font-weight: 700; margin-bottom: 8px;
}
.spin-pointer {
    position: absolute; top: -15px; left: 50%;
    transform: translateX(-50%);
    width: 0; height: 0;
    border-left: 20px solid transparent;
    border-right: 20px solid transparent;
    border-top: 30px solid #ef4444;
    z-index: 10;
    filter: drop-shadow(0 4px 4px rgba(239, 68, 68, 0.4));
}
</style>

<div class="font-sans antialiased text-gray-800 dark:text-dark-text">
  <!-- Header -->
  <div class="flex items-start justify-between mb-6">
    <div>
      <a href="<?= $appUrl ?>/admin/giveaways"
         class="inline-flex items-center gap-1.5 text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 no-underline transition-colors mb-3">
        <i class="bi bi-arrow-left"></i> Quay lại
      </a>
      <h4 class="text-xl font-extrabold text-gray-800 dark:text-dark-text m-0">
        🎰 Vòng Quay: <?= htmlspecialchars($giveaway['title']) ?>
      </h4>
    </div>
  </div>

  <!-- Spin Card -->
  <div class="bg-white dark:bg-dark-card rounded-[24px] border border-light-border dark:border-dark-border shadow-sm p-8 md:p-12 text-center animate-[fadeInUp_0.4s_ease-out_both]">
    
    <!-- Participant Count -->
    <div id="participantCount" class="text-base text-gray-500 dark:text-gray-400 mb-6">
      Tổng cộng: <strong class="text-2xl font-black text-primary" id="listLength">0</strong> sinh viên tham gia
    </div>

    <!-- Spin Machine -->
    <div class="spin-container">
      <div class="spin-pointer"></div>
      <div class="spin-track" id="spinTrack">
        <!-- Cards will be populated by JS -->
      </div>
    </div>

    <!-- Center line indicator -->
    <div class="relative max-w-[600px] mx-auto -mt-2 mb-6">
      <div class="absolute left-1/2 -top-0 w-0.5 h-4 bg-indigo-500/40 transform -translate-x-1/2"></div>
    </div>

    <!-- Spin Button -->
    <button id="btnSpin"
            class="inline-flex items-center gap-3 px-10 py-4 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-extrabold text-xl hover:brightness-110 hover:-translate-y-1 hover:shadow-[0_12px_36px_rgba(99,102,241,0.4)] transition-all border-0 cursor-pointer shadow-lg mt-4">
      <i class="bi bi-play-fill"></i>Bắt Đầu Quay
    </button>

  </div>
</div>

<script>
const rawParticipants = <?= $participants ?>;
const appUrl = '<?= $appUrl ?>';
const giveawayId = <?= $giveaway['id'] ?>;

document.getElementById('listLength').textContent = rawParticipants.length;

const track = document.getElementById('spinTrack');
const btnSpin = document.getElementById('btnSpin');

let loopItems = [];
if (rawParticipants.length > 0) {
    for(let i = 0; i < 50; i++) {
        loopItems.push(rawParticipants[i % rawParticipants.length]);
    }
}

function renderTrack() {
    track.innerHTML = '';
    loopItems.forEach((p, idx) => {
        let abbr = p.name ? p.name.charAt(0).toUpperCase() : '?';
        track.innerHTML += `
            <div class="participant-card" id="card-${idx}">
                <div class="participant-avatar">${abbr}</div>
                <div style="font-weight:700;font-size:0.9rem;">${p.name}</div>
                <div style="font-size:0.75rem;color:#94a3b8;">${p.email}</div>
            </div>
        `;
    });
}

if (rawParticipants.length > 0) {
    renderTrack();
} else {
    track.innerHTML = '<div class="w-full h-full flex items-center justify-center text-gray-400 text-sm">Chưa có ai tham gia</div>';
    btnSpin.disabled = true;
    btnSpin.classList.add('opacity-50', 'cursor-not-allowed');
}

btnSpin.addEventListener('click', function() {
    if (rawParticipants.length === 0) return;
    btnSpin.disabled = true;
    btnSpin.innerHTML = '<i class="bi bi-arrow-clockwise animate-spin"></i> Đang quay...';

    const winnerIdx = Math.floor(Math.random() * 15) + 30;
    const winner = loopItems[winnerIdx];

    const itemWidth = 200;
    const containerCenter = 300;
    const offset = (winnerIdx * itemWidth) + (itemWidth/2) - containerCenter;

    track.style.transform = `translateX(-${offset}px)`;

    setTimeout(() => {
        confetti({
            particleCount: 150,
            spread: 70,
            origin: { y: 0.6 }
        });

        document.getElementById(`card-${winnerIdx}`).classList.add('winner');

        Swal.fire({
            title: '🎉 Chúc mừng!',
            html: `Người may mắn trúng giải là:<br><strong style="font-size:1.1rem;color:#6366f1">${winner.name}</strong><br><span style="color:#94a3b8;font-size:0.9rem">${winner.email}</span>`,
            icon: 'success',
            confirmButtonText: 'Lưu kết quả & Đóng sự kiện',
            confirmButtonColor: '#6366f1',
            showCancelButton: true,
            cancelButtonText: 'Quay lại'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(appUrl + '/admin/giveaway_spin_api', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${giveawayId}&winner_id=${winner.id}&_csrf=<?= htmlspecialchars($csrf ?? '', ENT_QUOTES) ?>`
                }).then(res => res.json()).then(data => {
                    if (data.success === true) {
                        window.location.href = appUrl + '/admin/giveaways';
                    } else {
                        alert(data.error?.message || 'Có lỗi xảy ra khi lưu kết quả.');
                    }
                });
            }
        });
    }, 6100);
});
</script>
