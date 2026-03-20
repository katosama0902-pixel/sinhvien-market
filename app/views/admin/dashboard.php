<?php
/**
 * Admin View: Dashboard — Premium Edition
 * $stats = [total_users, active_products, pending_count, tx_today, recent_tx, recent_products]
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$admin  = $_SESSION['user'];
?>

<!-- ─── Premium Admin Dashboard ───────────────────────────────── -->
<div class="adm-dash">

  <!-- Welcome Banner -->
  <div class="adm-welcome">
    <div class="adm-welcome-blob-1"></div>
    <div class="adm-welcome-blob-2"></div>
    <div class="d-flex align-items-center gap-4" style="position:relative;z-index:2">
      <div class="adm-welcome-avatar">
        <?= mb_strtoupper(mb_substr($admin['name'], 0, 1)) ?>
      </div>
      <div>
        <div class="adm-welcome-greeting">Xin chào trở lại, 👋</div>
        <h2 class="adm-welcome-name"><?= htmlspecialchars($admin['name'], ENT_QUOTES) ?></h2>
        <div class="adm-welcome-sub">
          <i class="bi bi-calendar3 me-1"></i><?= date('l, d/m/Y') ?> &nbsp;·&nbsp;
          <i class="bi bi-shield-check me-1"></i>Administrator
        </div>
      </div>
    </div>
    <div class="d-none d-lg-flex align-items-center gap-4" style="position:relative;z-index:2">
      <?php if (($stats['pending_count'] ?? 0) > 0): ?>
        <div class="adm-alert-badge">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>
          <?= $stats['pending_count'] ?> bài chờ duyệt
        </div>
      <?php endif; ?>
      <a href="<?= $appUrl ?>/admin/products" class="adm-welcome-action">
        <i class="bi bi-card-checklist me-1"></i>Duyệt ngay
      </a>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="row g-4 mb-5">
    <?php
    $statsCards = [
      [
        'label'  => 'Tổng người dùng',
        'value'  => number_format($stats['total_users'] ?? 0),
        'icon'   => 'bi-people-fill',
        'bg'     => 'linear-gradient(135deg,#6366f1,#8b5cf6)',
        'glow'   => 'rgba(99,102,241,.5)',
        'badge'  => 'Tài khoản',
        'trend'  => '+12%',
      ],
      [
        'label'  => 'Sản phẩm đang bán',
        'value'  => number_format($stats['active_products'] ?? 0),
        'icon'   => 'bi-bag-check-fill',
        'bg'     => 'linear-gradient(135deg,#10b981,#059669)',
        'glow'   => 'rgba(16,185,129,.5)',
        'badge'  => 'Đang live',
        'trend'  => '+5%',
      ],
      [
        'label'  => 'Chờ duyệt',
        'value'  => number_format($stats['pending_count'] ?? 0),
        'icon'   => 'bi-hourglass-split',
        'bg'     => 'linear-gradient(135deg,#f59e0b,#ef4444)',
        'glow'   => 'rgba(245,158,11,.5)',
        'badge'  => 'Cần xử lý',
        'trend'  => '',
      ],
      [
        'label'  => 'Giao dịch hôm nay',
        'value'  => number_format($stats['tx_today'] ?? 0),
        'icon'   => 'bi-receipt-cutoff',
        'bg'     => 'linear-gradient(135deg,#06b6d4,#3b82f6)',
        'glow'   => 'rgba(6,182,212,.5)',
        'badge'  => 'Hôm nay',
        'trend'  => '+8%',
      ],
    ];
    ?>
    <?php foreach ($statsCards as $i => $card): ?>
      <div class="col-sm-6 col-xl-3 fade-in-up" style="animation-delay:<?= $i * 80 ?>ms">
        <div class="adm-stat-card">
          <div class="adm-stat-icon" style="background:<?= $card['bg'] ?>;box-shadow:0 10px 28px <?= $card['glow'] ?>">
            <i class="bi <?= $card['icon'] ?>"></i>
          </div>
          <div class="adm-stat-body">
            <div class="adm-stat-label"><?= $card['label'] ?></div>
            <div class="adm-stat-value"><?= $card['value'] ?></div>
            <div class="adm-stat-meta">
              <span class="adm-stat-badge"><?= $card['badge'] ?></span>
              <?php if ($card['trend']): ?>
                <span class="adm-stat-trend"><i class="bi bi-arrow-up-right me-1"></i><?= $card['trend'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          <div class="adm-stat-bg-icon"><i class="bi <?= $card['icon'] ?>"></i></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Content Grid -->
  <div class="row g-4 mb-5">

    <!-- Pending Posts -->
    <div class="col-lg-6 fade-in-up delay-200">
      <div class="adm-panel">
        <div class="adm-panel-header">
          <div class="adm-panel-title">
            <div class="adm-panel-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444)">
              <i class="bi bi-hourglass-split"></i>
            </div>
            Bài đăng chờ duyệt
          </div>
          <a href="<?= $appUrl ?>/admin/products" class="adm-panel-action">
            Xem tất cả <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>
        <div class="adm-panel-body">
          <?php if (empty($stats['recent_products'])): ?>
            <div class="adm-empty">
              <i class="bi bi-check-circle-fill"></i>
              <span>Không có bài nào chờ duyệt</span>
            </div>
          <?php else: ?>
            <?php foreach (array_slice($stats['recent_products'], 0, 5) as $p): ?>
              <div class="adm-list-item">
                <div class="adm-list-avatar" style="background:linear-gradient(135deg,#f59e0b,#ef4444)">
                  <?= mb_strtoupper(mb_substr($p['seller_name'], 0, 1)) ?>
                </div>
                <div class="adm-list-info">
                  <div class="adm-list-title"><?= htmlspecialchars($p['title'], ENT_QUOTES) ?></div>
                  <div class="adm-list-meta">
                    bởi <?= htmlspecialchars($p['seller_name'], ENT_QUOTES) ?>
                    · <?= date('d/m H:i', strtotime($p['created_at'])) ?>
                  </div>
                </div>
                <span class="adm-type-badge adm-type-<?= $p['type'] ?>">
                  <?= $p['type'] === 'auction' ? '⚡ Đấu giá' : ($p['type'] === 'exchange' ? '🔄 Trao đổi' : '🏷️ Bán') ?>
                </span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Recent Transactions -->
    <div class="col-lg-6 fade-in-up delay-300">
      <div class="adm-panel">
        <div class="adm-panel-header">
          <div class="adm-panel-title">
            <div class="adm-panel-icon" style="background:linear-gradient(135deg,#06b6d4,#3b82f6)">
              <i class="bi bi-receipt-cutoff"></i>
            </div>
            Giao dịch mới nhất
          </div>
          <a href="<?= $appUrl ?>/admin/reports" class="adm-panel-action">
            Báo cáo <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>
        <div class="adm-panel-body">
          <?php if (empty($stats['recent_tx'])): ?>
            <div class="adm-empty">
              <i class="bi bi-inbox"></i>
              <span>Chưa có giao dịch nào</span>
            </div>
          <?php else: ?>
            <?php foreach ($stats['recent_tx'] as $t): ?>
              <div class="adm-list-item">
                <div class="adm-list-avatar" style="background:linear-gradient(135deg,#06b6d4,#3b82f6)">
                  <?= mb_strtoupper(mb_substr($t['buyer_name'], 0, 1)) ?>
                </div>
                <div class="adm-list-info">
                  <div class="adm-list-title"><?= htmlspecialchars($t['product_title'], ENT_QUOTES) ?></div>
                  <div class="adm-list-meta">
                    <?= htmlspecialchars($t['buyer_name'], ENT_QUOTES) ?> mua từ <?= htmlspecialchars($t['seller_name'], ENT_QUOTES) ?>
                  </div>
                </div>
                <span class="adm-tx-amount">+<?= number_format($t['amount'], 0, ',', '.') ?>đ</span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="adm-qa-section fade-in-up delay-400">
    <div class="adm-qa-header">
      <h5 class="adm-qa-title">Truy cập nhanh</h5>
      <p class="adm-qa-sub">Điều hướng nhanh đến các tính năng quản trị</p>
    </div>
    <div class="row g-3">
      <?php
      $actions = [
        ['admin/users',      'bi-people-fill',    'linear-gradient(135deg,#6366f1,#8b5cf6)',  'rgba(99,102,241,.4)',  'Quản lý người dùng',   'Xem & khóa tài khoản'],
        ['admin/products',   'bi-card-checklist', 'linear-gradient(135deg,#f59e0b,#ef4444)',  'rgba(245,158,11,.4)',  'Kiểm duyệt bài đăng',  'Duyệt & xóa vi phạm'],
        ['admin/categories', 'bi-tags-fill',      'linear-gradient(135deg,#06b6d4,#3b82f6)',  'rgba(6,182,212,.4)',   'Quản lý danh mục',     'CRUD danh mục'],
        ['admin/reports',    'bi-bar-chart-fill', 'linear-gradient(135deg,#10b981,#059669)',  'rgba(16,185,129,.4)',  'Báo cáo giao dịch',    'Thống kê doanh thu'],
        ['admin/giveaways',  'bi-gift-fill',      'linear-gradient(135deg,#ec4899,#f97316)',  'rgba(236,72,153,.4)',  'Sự kiện Giveaway',     'Tổ chức & quay số'],
        ['admin/audit-log',  'bi-journal-text',   'linear-gradient(135deg,#64748b,#475569)',  'rgba(100,116,139,.4)','Nhật ký hành động',    'Lịch sử admin'],
      ];
      ?>
      <?php foreach ($actions as $i => [$url, $icon, $bg, $glow, $title, $desc]): ?>
        <div class="col-sm-6 col-md-4 col-xl-2">
          <a href="<?= $appUrl ?>/<?= $url ?>" class="adm-qa-card text-decoration-none">
            <div class="adm-qa-icon" style="background:<?= $bg ?>;box-shadow:0 8px 22px <?= $glow ?>">
              <i class="bi <?= $icon ?>"></i>
            </div>
            <div class="adm-qa-card-title"><?= $title ?></div>
            <div class="adm-qa-card-desc"><?= $desc ?></div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- ─── Styles ─────────────────────────────────────────────────── -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');
.adm-dash { font-family: 'Plus Jakarta Sans', sans-serif; }

/* ── Welcome Banner ────────────────────────────────── */
.adm-welcome {
  position: relative; overflow: hidden;
  background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4f46e5 100%);
  border-radius: 24px; padding: 2rem 2.5rem;
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
  margin-bottom: 2rem;
  box-shadow: 0 20px 60px rgba(99,102,241,.3);
}
.adm-welcome-blob-1 {
  position:absolute;width:350px;height:350px;
  background:radial-gradient(circle,rgba(139,92,246,.35) 0%,transparent 65%);
  border-radius:50%;top:-100px;right:50px;pointer-events:none;
}
.adm-welcome-blob-2 {
  position:absolute;width:250px;height:250px;
  background:radial-gradient(circle,rgba(236,72,153,.25) 0%,transparent 65%);
  border-radius:50%;bottom:-80px;left:20%;pointer-events:none;
}
.adm-welcome-avatar {
  width:56px;height:56px;
  background:linear-gradient(135deg,#a78bfa,#f472b6);
  border-radius:16px;display:flex;align-items:center;justify-content:center;
  font-weight:900;font-size:1.5rem;color:#fff;
  box-shadow:0 8px 24px rgba(0,0,0,.3);flex-shrink:0;
}
.adm-welcome-greeting { font-size:.85rem;color:rgba(255,255,255,.6);font-weight:500; }
.adm-welcome-name { font-size:1.5rem;font-weight:800;color:#fff;margin:2px 0; }
.adm-welcome-sub { font-size:.8rem;color:rgba(255,255,255,.5); }
.adm-alert-badge {
  background:rgba(245,158,11,.15);border:1.5px solid rgba(245,158,11,.35);
  color:#fbbf24;padding:.45rem 1rem;border-radius:50px;font-size:.82rem;font-weight:700;
  display:flex;align-items:center;
}
.adm-welcome-action {
  background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.28);
  color:#fff;padding:.55rem 1.3rem;border-radius:50px;font-size:.88rem;font-weight:700;
  text-decoration:none;transition:all .2s;
}
.adm-welcome-action:hover { background:rgba(255,255,255,.28);color:#fff; }

/* ── Stat Card ─────────────────────────────────────── */
.adm-stat-card {
  background:#fff;border-radius:20px;
  border:1.5px solid #e2e8f0;
  padding:1.5rem;position:relative;overflow:hidden;
  transition:transform .32s cubic-bezier(.34,1.56,.64,1),box-shadow .28s;
  display:flex;align-items:center;gap:1.2rem;
}
.adm-stat-card:hover { transform:translateY(-6px);box-shadow:0 18px 44px rgba(0,0,0,.1); }
.adm-stat-icon {
  width:56px;height:56px;border-radius:16px;
  display:flex;align-items:center;justify-content:center;
  font-size:1.4rem;color:#fff;flex-shrink:0;
}
.adm-stat-label { font-size:.8rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.4px; }
.adm-stat-value { font-size:1.8rem;font-weight:900;color:#0f172a;line-height:1.1;margin:2px 0; }
.adm-stat-meta { display:flex;align-items:center;gap:.6rem;margin-top:.25rem; }
.adm-stat-badge {
  background:#f1f5f9;color:#64748b;font-size:.72rem;font-weight:700;
  padding:.2rem .65rem;border-radius:50px;
}
.adm-stat-trend { color:#10b981;font-size:.75rem;font-weight:700;display:flex;align-items:center; }
.adm-stat-bg-icon {
  position:absolute;right:-10px;bottom:-10px;font-size:5.5rem;
  color:rgba(0,0,0,.04);pointer-events:none;line-height:1;
}

/* ── Panel ─────────────────────────────────────────── */
.adm-panel {
  background:#fff;border-radius:20px;
  border:1.5px solid #e2e8f0;overflow:hidden;
  box-shadow:0 4px 16px rgba(0,0,0,.04);
}
.adm-panel-header {
  display:flex;align-items:center;justify-content:space-between;
  padding:1.2rem 1.4rem;border-bottom:1.5px solid #f1f5f9;
}
.adm-panel-title {
  display:flex;align-items:center;gap:.75rem;
  font-weight:800;font-size:1rem;color:#0f172a;
}
.adm-panel-icon {
  width:34px;height:34px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-size:.95rem;flex-shrink:0;
}
.adm-panel-action {
  font-size:.82rem;font-weight:700;color:#6366f1;
  text-decoration:none;display:flex;align-items:center;gap:.3rem;
  transition:gap .2s;
}
.adm-panel-action:hover { gap:.6rem; }
.adm-panel-body { padding:.5rem 0; }

/* List Items */
.adm-list-item {
  display:flex;align-items:center;gap:.9rem;
  padding:.9rem 1.4rem;
  border-bottom:1px solid #f8fafc;
  transition:background .18s;
}
.adm-list-item:last-child { border:none; }
.adm-list-item:hover { background:#f8fafc; }
.adm-list-avatar {
  width:36px;height:36px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;
  color:#fff;font-weight:800;font-size:.9rem;flex-shrink:0;
}
.adm-list-info { flex:1;min-width:0; }
.adm-list-title {
  font-weight:700;font-size:.88rem;color:#0f172a;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.adm-list-meta { font-size:.75rem;color:#94a3b8;margin-top:2px; }
.adm-type-badge {
  font-size:.72rem;font-weight:700;padding:.25rem .7rem;border-radius:50px;
  white-space:nowrap;flex-shrink:0;
}
.adm-type-auction  { background:#fef3c7;color:#d97706; }
.adm-type-sale     { background:#e0e7ff;color:#4f46e5; }
.adm-type-exchange { background:#cffafe;color:#0891b2; }
.adm-tx-amount { font-weight:900;color:#10b981;font-size:.9rem;white-space:nowrap;flex-shrink:0; }

/* Empty state */
.adm-empty {
  display:flex;flex-direction:column;align-items:center;gap:.5rem;
  padding:2.5rem;color:#94a3b8;font-size:.9rem;
}
.adm-empty i { font-size:2rem;color:#cbd5e1; }

/* ── Quick Actions ──────────────────────────────────── */
.adm-qa-section { }
.adm-qa-header { margin-bottom:1.5rem; }
.adm-qa-title { font-weight:800;font-size:1.1rem;color:#0f172a;margin:0; }
.adm-qa-sub   { font-size:.85rem;color:#94a3b8;margin:4px 0 0; }

.adm-qa-card {
  background:#fff;border-radius:18px;
  border:1.5px solid #e2e8f0;padding:1.4rem 1rem;
  text-align:center;display:block;
  transition:transform .32s cubic-bezier(.34,1.56,.64,1),box-shadow .28s,border-color .2s;
}
.adm-qa-card:hover {
  transform:translateY(-8px) scale(1.02);
  box-shadow:0 18px 44px rgba(0,0,0,.1);
  border-color:transparent;
}
.adm-qa-icon {
  width:54px;height:54px;border-radius:16px;
  display:flex;align-items:center;justify-content:center;
  font-size:1.4rem;color:#fff;margin:0 auto .85rem;
  transition:transform .25s;
}
.adm-qa-card:hover .adm-qa-icon { transform:scale(1.1) rotate(-5deg); }
.adm-qa-card-title { font-weight:800;font-size:.88rem;color:#0f172a;margin-bottom:.3rem; }
.adm-qa-card-desc  { font-size:.74rem;color:#94a3b8; }
</style>
