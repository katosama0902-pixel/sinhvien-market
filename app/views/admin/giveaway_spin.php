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
    border: 4px solid #3b5bdb;
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
.participant-card.winner {
    background: #fffbeb;
    border: 3px solid #fbbf24;
}

.participant-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 8px;
}

.spin-pointer {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 20px solid transparent;
    border-right: 20px solid transparent;
    border-top: 30px solid #ef4444;
    z-index: 10;
    filter: drop-shadow(0 4px 4px rgba(239, 68, 68, 0.4));
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <a href="<?= $appUrl ?>/admin/giveaways" class="btn btn-sm btn-outline-secondary mb-2">&larr; Quay lại</a>
    <h4 class="mb-0 fw-bold">Vòng Quay: <?= htmlspecialchars($giveaway['title']) ?></h4>
  </div>
</div>

<div class="card shadow-sm border-0 rounded-4">
  <div class="card-body p-5 text-center">
    
    <div id="participantCount" class="mb-3 text-muted">
       Tổng cộng: <strong class="fs-5 text-primary" id="listLength">0</strong> sinh viên tham gia
    </div>
    
    <div class="spin-container">
        <div class="spin-pointer"></div>
        <div class="spin-track" id="spinTrack">
            <!-- Cards will be populated by JS -->
        </div>
    </div>

    <button id="btnSpin" class="btn btn-primary btn-lg px-5 py-3 rounded-pill fw-bold mt-4" style="font-size: 1.25rem;">
        <i class="bi bi-play-fill me-2"></i>Bắt Đầu Quay
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

// Render mock loop items
let loopItems = [];
if (rawParticipants.length > 0) {
    // Duplicate items to simulate a long wheel
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
                <div class="fw-bold">${p.name}</div>
                <div class="small text-muted">${p.email}</div>
            </div>
        `;
    });
}

if (rawParticipants.length > 0) {
    renderTrack();
} else {
    track.innerHTML = '<div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">Chưa có ai tham gia</div>';
    btnSpin.disabled = true;
}

btnSpin.addEventListener('click', function() {
    if (rawParticipants.length === 0) return;
    btnSpin.disabled = true;
    
    // Choose a random winner between index 30 and 45 to ensure a good spin duration
    const winnerIdx = Math.floor(Math.random() * 15) + 30;
    const winner = loopItems[winnerIdx];

    // Card width is 200px. Track needs to shift so winnerIdx is at the center (left 50%)
    // Center is 300px (since container is 600px).
    // Target translation: -(winnerIdx * 200) + 200
    const itemWidth = 200;
    const containerCenter = 300;
    const offset = (winnerIdx * itemWidth) + (itemWidth/2) - containerCenter;

    track.style.transform = `translateX(-${offset}px)`;

    // Wait for animation to finish (6s)
    setTimeout(() => {
        confetti({
            particleCount: 150,
            spread: 70,
            origin: { y: 0.6 }
        });

        document.getElementById(`card-${winnerIdx}`).classList.add('winner');

        Swal.fire({
            title: 'Chúc mừng!',
            text: `Người may mắn trúng giải là: ${winner.name} (${winner.email})`,
            icon: 'success',
            confirmButtonText: 'Lưu kết quả & Đóng sự kiện'
        }).then((result) => {
            if (result.isConfirmed) {
                // Post to API
                fetch(appUrl + '/admin/giveaway_spin_api', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${giveawayId}&winner_id=${winner.id}&_csrf=<?= htmlspecialchars($this->csrfToken()) ?>`
                }).then(res => res.json()).then(data => {
                    if (data.status === 'success') {
                        window.location.href = appUrl + '/admin/giveaways';
                    } else {
                        alert(data.msg);
                    }
                });
            }
        });

    }, 6100);
});
</script>
