<?php
/**
 * Admin View: Quản lý người dùng
 * Biến: $users
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$me     = $_SESSION['user'];
use Core\Controller;
use Core\Flash;
$ctrl = new class extends Controller {};
$csrf = $ctrl->csrfToken();
?>
<div class="container-fluid py-4">
  <h4 class="fw-700 mb-4"><i class="bi bi-people me-2 text-primary"></i>Quản lý người dùng</h4>
  <?= Flash::render() ?>

  <div class="card-sv">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
      <span class="small text-muted">Tổng: <strong><?= count($users) ?></strong> tài khoản</span>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Tên / Email</th>
            <th>SĐT</th>
            <th>Vai trò</th>
            <th>Trạng thái</th>
            <th>Ngày tạo</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $i => $u): ?>
            <tr>
              <td class="small text-muted"><?= $i + 1 ?></td>
              <td>
                <div class="fw-600"><?= htmlspecialchars($u['name'], ENT_QUOTES) ?></div>
                <div class="small text-muted"><?= htmlspecialchars($u['email'], ENT_QUOTES) ?></div>
              </td>
              <td class="small"><?= htmlspecialchars($u['phone'] ?? '—', ENT_QUOTES) ?></td>
              <td>
                <?php if ($u['role'] === 'admin'): ?>
                  <span class="badge bg-danger"><i class="bi bi-shield-fill me-1"></i>Admin</span>
                <?php else: ?>
                  <span class="badge bg-primary">Sinh viên</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($u['is_locked']): ?>
                  <span class="badge bg-danger"><i class="bi bi-lock-fill me-1"></i>Bị khóa</span>
                <?php else: ?>
                  <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Hoạt động</span>
                <?php endif; ?>
              </td>
              <td class="small text-nowrap"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
              <td>
                <?php if ($u['role'] !== 'admin' && (int)$u['id'] !== (int)$me['id']): ?>
                  <form method="POST" action="<?= $appUrl ?>/admin/users/toggle"
                        onsubmit="return confirm('<?= $u['is_locked'] ? 'Mở khóa' : 'Khóa' ?> tài khoản <?= htmlspecialchars($u['name'], ENT_QUOTES) ?>?')">
                    <input type="hidden" name="_csrf"    value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                    <input type="hidden" name="user_id"  value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-<?= $u['is_locked'] ? 'success' : 'danger' ?>">
                      <i class="bi bi-<?= $u['is_locked'] ? 'unlock' : 'lock' ?> me-1"></i>
                      <?= $u['is_locked'] ? 'Mở khóa' : 'Khóa' ?>
                    </button>
                  </form>
                <?php else: ?>
                  <span class="text-muted small">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
