<?php
/**
 * Admin View: Nhật ký hành động Admin (Audit Log)
 * Biến: $logs
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');

$actionLabels = [
    'approve_product'  => ['✅ Duyệt SP',         'success'],
    'reject_product'   => ['❌ Từ chối SP',        'warning'],
    'delete_product'   => ['🗑️ Xóa SP',            'danger'],
    'lock_user'        => ['🔒 Khóa user',          'danger'],
    'unlock_user'      => ['🔓 Mở khóa user',       'success'],
    'create_category'  => ['➕ Tạo danh mục',       'primary'],
    'update_category'  => ['✏️ Sửa danh mục',        'info'],
    'delete_category'  => ['🗑️ Xóa danh mục',       'danger'],
];
?>
<div class="container-fluid py-4">
  <h4 class="fw-700 mb-4"><i class="bi bi-journal-text me-2 text-secondary"></i>Nhật ký hành động Admin</h4>

  <?php if (empty($logs)): ?>
    <div class="card-sv p-5 text-center text-muted">
      <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-25"></i>
      Chưa có hành động nào được ghi lại.
    </div>
  <?php else: ?>
    <div class="card-sv mb-3 p-2 px-3 d-flex align-items-center gap-2">
      <i class="bi bi-info-circle text-muted"></i>
      <span class="small text-muted">Hiển thị <strong><?= count($logs) ?></strong> hành động gần nhất. Dữ liệu không thể sửa/xóa.</span>
    </div>

    <div class="card-sv">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Thời gian</th>
              <th>Admin</th>
              <th>Hành động</th>
              <th>Đối tượng</th>
              <th>Ghi chú</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $i => $log): ?>
              <?php
                [$actionLabel, $actionColor] = $actionLabels[$log['action']] ?? [$log['action'], 'secondary'];
              ?>
              <tr>
                <td class="small text-muted"><?= $i + 1 ?></td>
                <td class="small text-nowrap">
                  <?= date('d/m/Y', strtotime($log['created_at'])) ?>
                  <br><span class="text-muted"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
                </td>
                <td>
                  <div class="fw-600 small"><?= htmlspecialchars($log['admin_name'], ENT_QUOTES) ?></div>
                  <div class="small text-muted"><?= htmlspecialchars($log['admin_email'], ENT_QUOTES) ?></div>
                </td>
                <td><span class="badge bg-<?= $actionColor ?>"><?= $actionLabel ?></span></td>
                <td class="small">
                  <code><?= htmlspecialchars(ucfirst($log['target_type']), ENT_QUOTES) ?> #<?= $log['target_id'] ?></code>
                </td>
                <td class="small" style="max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                  <?= htmlspecialchars($log['note'] ?? '—', ENT_QUOTES) ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>
