<?php
$appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="mb-0 fw-bold"><i class="bi bi-gift text-primary me-2"></i>Quản lý Giveaways</h4>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
    <i class="bi bi-plus-circle me-1"></i>Tạo Sự Kiện Mới
  </button>
</div>

<div class="card shadow-sm border-0 rounded-4">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Tên Sự Kiện</th>
            <th>Trạng thái</th>
            <th>Hết Hạn</th>
            <th>Người Trúng</th>
            <th class="text-end pe-4">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($giveaways)): ?>
            <tr><td colspan="5" class="text-center py-4 text-muted">Chưa có sự kiện nào</td></tr>
          <?php else: ?>
            <?php foreach ($giveaways as $ga): 
              $isActive = $ga['status'] === 'active' && strtotime($ga['end_time']) > time();
              $isEndedWithoutWinner = $ga['status'] === 'active' && strtotime($ga['end_time']) <= time();
            ?>
              <tr>
                <td class="ps-4 fw-600">
                  <div class="d-flex align-items-center gap-2">
                    <?php if ($ga['image']): ?>
                       <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($ga['image']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;">
                    <?php else: ?>
                       <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="width:40px;height:40px;border-radius:6px;"><i class="bi bi-gift"></i></div>
                    <?php endif; ?>
                    <?= htmlspecialchars($ga['title']) ?>
                  </div>
                </td>
                <td>
                  <?php if ($ga['status'] === 'ended'): ?>
                    <span class="badge bg-secondary">Đã kết thúc</span>
                  <?php elseif ($isEndedWithoutWinner): ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-clock-history"></i> Chờ xổ số</span>
                  <?php else: ?>
                    <span class="badge bg-success"><i class="bi bi-broadcast"></i> Đang diễn ra</span>
                  <?php endif; ?>
                </td>
                <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($ga['end_time'])) ?></td>
                <td>
                  <?php if ($ga['winner_id']): ?>
                    <span class="fw-bold text-success"><i class="bi bi-trophy-fill me-1"></i><?= htmlspecialchars($ga['winner_name']) ?></span>
                  <?php else: ?>
                    <span class="text-muted">Chưa có</span>
                  <?php endif; ?>
                </td>
                <td class="text-end pe-4">
                  <?php if ($ga['status'] === 'active'): ?>
                    <a href="<?= $appUrl ?>/admin/giveaway_spin?id=<?= $ga['id'] ?>" class="btn btn-sm btn-outline-primary fw-bold">
                      <i class="bi bi-play-circle-fill me-1"></i>Quay Số
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Thêm Mới -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="<?= $appUrl ?>/admin/giveaways/store" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold">Tạo Sự Kiện Giveaway Mới</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($this->csrfToken()) ?>">
        <div class="mb-3">
          <label class="form-label fw-600">Tiêu đề phần thưởng</label>
          <input type="text" name="title" class="form-control" required placeholder="Ví dụ: Giveaway: Bàn phím cơ DareU...">
        </div>
        <div class="mb-3">
          <label class="form-label fw-600">Mô tả chi tiết</label>
          <textarea name="description" class="form-control" rows="3"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label fw-600">Thời gian kết thúc</label>
          <input type="datetime-local" name="end_time" class="form-control" required min="<?= date('Y-m-d\TH:i') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label fw-600">Hình ảnh quà tặng</label>
          <input type="file" name="image" class="form-control" accept="image/*">
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Lưu</button>
      </div>
    </form>
  </div>
</div>
