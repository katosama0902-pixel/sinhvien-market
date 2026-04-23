<?php
/**
 * View: Bảng Xếp Hạng Top Seller
 * Vars: $topSellers, $myRank, $myStats, $appUrl
 */
$user = $_SESSION['user'] ?? null;
?>

<style>
/* ─── Leaderboard Styles ─────────────────────────────── */
.lb-hero {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 40%, #0f3460 100%);
    padding: 3rem 0 5rem;
    position: relative;
    overflow: hidden;
}
.lb-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle at 30% 40%, rgba(99,102,241,0.15) 0%, transparent 50%),
                radial-gradient(circle at 70% 60%, rgba(168,85,247,0.1) 0%, transparent 50%);
    animation: lb-pulse 8s ease-in-out infinite alternate;
}
@keyframes lb-pulse { from { transform: scale(1); } to { transform: scale(1.05); } }

.lb-title {
    font-size: 2.5rem;
    font-weight: 900;
    color: #fff;
    text-shadow: 0 2px 20px rgba(99,102,241,0.5);
    letter-spacing: -0.5px;
}
.lb-subtitle { color: rgba(255,255,255,0.6); font-size: 1rem; }

/* ─── Podium ──────────────────────────────────────────── */
.podium-wrap {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 1.5rem;
    margin-top: -2rem;
    padding: 0 1rem;
}
.podium-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}
.podium-avatar-wrap {
    position: relative;
    margin-bottom: 0.75rem;
}
.podium-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--card-bg);
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}
.podium-avatar-1 { width: 96px; height: 96px; }
.podium-crown {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 1.6rem;
    animation: crown-float 2s ease-in-out infinite;
}
@keyframes crown-float {
    0%,100% { transform: translateX(-50%) translateY(0); }
    50%      { transform: translateX(-50%) translateY(-6px); }
}
.podium-rank-badge {
    position: absolute;
    bottom: -4px;
    right: -4px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 800;
    border: 2px solid var(--card-bg);
    color: #fff;
}
.rank-1 { background: linear-gradient(135deg, #f59e0b, #d97706); }
.rank-2 { background: linear-gradient(135deg, #94a3b8, #64748b); }
.rank-3 { background: linear-gradient(135deg, #cd7c3e, #a55c22); }

.podium-name {
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--text);
    text-align: center;
    max-width: 100px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.podium-score { font-size: 0.75rem; color: var(--muted); }
.podium-box {
    border-radius: 16px 16px 0 0;
    width: 110px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 900;
    color: rgba(255,255,255,0.7);
    margin-top: 0.5rem;
}
.podium-box-1 { height: 100px; background: linear-gradient(180deg, #fbbf24, #f59e0b); width: 130px; }
.podium-box-2 { height: 70px;  background: linear-gradient(180deg, #94a3b8, #64748b); }
.podium-box-3 { height: 50px;  background: linear-gradient(180deg, #cd7c3e, #a55c22); }

/* ─── Rankings Table ─────────────────────────────────── */
.lb-card {
    background: var(--card-bg);
    border-radius: 20px;
    border: 1px solid var(--border);
    overflow: hidden;
}
.lb-row {
    display: flex;
    align-items: center;
    padding: 0.9rem 1.25rem;
    border-bottom: 1px solid var(--border);
    transition: background 0.2s;
    gap: 1rem;
}
.lb-row:last-child { border-bottom: none; }
.lb-row:hover { background: var(--hover-bg, rgba(99,102,241,0.04)); }
.lb-rank-num {
    width: 32px;
    font-size: 1rem;
    font-weight: 800;
    color: var(--muted);
    flex-shrink: 0;
    text-align: center;
}
.lb-user-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    border: 2px solid var(--border);
}
.lb-user-name { font-weight: 700; font-size: 0.92rem; color: var(--text); }
.lb-user-meta { font-size: 0.76rem; color: var(--muted); }
.lb-stats {
    margin-left: auto;
    display: flex;
    gap: 1.5rem;
    flex-shrink: 0;
}
.lb-stat {
    text-align: center;
    min-width: 48px;
}
.lb-stat-value { font-size: 0.95rem; font-weight: 700; color: var(--text); }
.lb-stat-label { font-size: 0.68rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.03em; }

/* ─── My Rank Card ──────────────────────────────────── */
.my-rank-card {
    background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(168,85,247,0.08));
    border: 2px solid rgba(99,102,241,0.4);
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
}
[data-theme="dark"] .my-rank-card {
    background: linear-gradient(135deg, rgba(99,102,241,0.05), rgba(168,85,247,0.04));
    border-color: rgba(99,102,241,0.2);
}
</style>

<!-- ─── Hero Section ─────────────────────────────────────── -->
<div class="lb-hero">
  <div class="container text-center position-relative">
    <div class="lb-title">🏆 Bảng Xếp Hạng</div>
    <div class="lb-subtitle mt-2">Top Người Bán Uy Tín — Khu ĐHQG TP.HCM</div>
  </div>
</div>

<!-- ─── Main Content ─────────────────────────────────────── -->
<div class="container" style="margin-top: -2rem; position: relative; z-index: 2; padding-bottom: 3rem;">

  <?php if (count($topSellers) === 0): ?>
  <div class="text-center py-5">
    <div style="font-size:4rem">🏪</div>
    <h4 class="mt-3 fw-bold">Chưa có dữ liệu</h4>
    <p class="text-muted">Hãy là người đầu tiên đăng sản phẩm để lên bảng xếp hạng!</p>
    <a href="<?= $appUrl ?>/products/create" class="btn btn-primary rounded-pill px-4">Đăng bán ngay</a>
  </div>
  <?php else: ?>

  <!-- ─── Podium Top 3 ──────────────────────────────────── -->
  <?php
    $top3 = array_slice($topSellers, 0, 3);
    // Sắp xếp lại thứ tự cho podium: [2nd, 1st, 3rd]
    $podiumOrder = [];
    if (isset($top3[1])) $podiumOrder[] = ['rank' => 2, 'user' => $top3[1]];
    if (isset($top3[0])) $podiumOrder[] = ['rank' => 1, 'user' => $top3[0]];
    if (isset($top3[2])) $podiumOrder[] = ['rank' => 3, 'user' => $top3[2]];
  ?>
  <div class="podium-wrap mb-4">
    <?php foreach ($podiumOrder as $pod): ?>
    <?php $u = $pod['user']; $r = $pod['rank']; ?>
    <div class="podium-item">
      <div class="podium-avatar-wrap">
        <?php if ($r === 1): ?>
        <span class="podium-crown">👑</span>
        <?php endif; ?>
        <img class="podium-avatar <?= $r === 1 ? 'podium-avatar-1' : '' ?>"
             src="<?= !empty($u['avatar']) ? $appUrl . '/public/uploads/' . htmlspecialchars($u['avatar']) : (!empty($u['avatar_url']) ? htmlspecialchars($u['avatar_url']) : 'https://ui-avatars.com/api/?name=' . urlencode($u['name']) . '&background=6366f1&color=fff&size=96') ?>"
             alt="<?= htmlspecialchars($u['name']) ?>">
        <span class="podium-rank-badge rank-<?= $r ?>"><?= $r ?></span>
      </div>
      <div class="podium-name"><?= htmlspecialchars($u['name']) ?></div>
      <div class="podium-score">
        ⭐ <?= number_format((float)$u['avg_rating'], 1) ?>
        &nbsp;·&nbsp; <?= (int)$u['sold_count'] ?> đã bán
      </div>
      <div class="podium-box podium-box-<?= $r ?>"><?= $r ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ─── Bảng hạng 4–10 ──────────────────────────────── -->
  <?php $rest = array_slice($topSellers, 3); ?>
  <?php if (count($rest) > 0): ?>
  <div class="lb-card mb-4">
    <?php foreach ($rest as $i => $s): ?>
    <?php $rank = $i + 4; ?>
    <a href="<?= $appUrl ?>/users/profile?id=<?= $s['id'] ?>" class="lb-row text-decoration-none" style="color:inherit">
      <div class="lb-rank-num"><?= $rank ?></div>
      <img class="lb-user-avatar"
           src="<?= !empty($s['avatar']) ? $appUrl . '/public/uploads/' . htmlspecialchars($s['avatar']) : (!empty($s['avatar_url']) ? htmlspecialchars($s['avatar_url']) : 'https://ui-avatars.com/api/?name=' . urlencode($s['name']) . '&background=6366f1&color=fff') ?>"
           alt="">
      <div>
        <div class="lb-user-name">
          <?= htmlspecialchars($s['name']) ?>
          <?php if ($s['is_student_verified']): ?>
          <i class="bi bi-patch-check-fill text-primary ms-1" style="font-size:.75rem" title="Sinh viên đã xác thực"></i>
          <?php endif; ?>
        </div>
        <div class="lb-user-meta">
          <?php
            $total = (int)$s['product_count'];
            if ($total >= 5) echo '<span class="text-warning">⭐ Uy tín</span>';
            elseif ($total >= 1) echo '<span class="text-info">✦ Tích cực</span>';
            else echo '<span>Tân binh</span>';
          ?>
        </div>
      </div>
      <div class="lb-stats">
        <div class="lb-stat">
          <div class="lb-stat-value"><?= (int)$s['sold_count'] ?></div>
          <div class="lb-stat-label">Đã bán</div>
        </div>
        <div class="lb-stat">
          <div class="lb-stat-value"><?= number_format((float)$s['avg_rating'], 1) ?>⭐</div>
          <div class="lb-stat-label">Đánh giá</div>
        </div>
        <div class="lb-stat d-none d-md-block">
          <div class="lb-stat-value"><?= (int)$s['product_count'] ?></div>
          <div class="lb-stat-label">Tin đăng</div>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- ─── Card Xếp Hạng Của Tôi ──────────────────────── -->
  <?php if ($user && $myRank > 0): ?>
  <div class="my-rank-card d-flex align-items-center gap-3 mb-4">
    <div style="font-size:2rem">🎯</div>
    <div class="flex-grow-1">
      <div class="fw-bold">Thứ hạng của tôi</div>
      <div class="small text-muted">
        <?php if ($myStats): ?>
        <?= (int)$myStats['sold_count'] ?> đã bán · ⭐ <?= number_format((float)$myStats['avg_rating'],1) ?> · <?= (int)$myStats['product_count'] ?> tin đăng
        <?php endif; ?>
      </div>
    </div>
    <div style="font-size:2rem;font-weight:900;color:#6366f1">#<?= $myRank ?></div>
  </div>
  <?php elseif ($user): ?>
  <div class="my-rank-card text-center mb-4">
    <div class="small text-muted mb-2">Bạn chưa có xếp hạng. Hãy đăng sản phẩm và hoàn thành giao dịch để lên bảng!</div>
    <a href="<?= $appUrl ?>/products/create" class="btn btn-sm btn-primary rounded-pill px-4">Đăng bán ngay</a>
  </div>
  <?php endif; ?>

  <?php endif; ?>

</div>
