<?php
/**
 * User Profile Edit View
 * Formats data for user editing their own profile
 */
use Core\Flash;
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$tab = $_GET['tab'] ?? 'info'; // 'info' hoặc 'security'
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center mb-1">
                <a href="<?= $appUrl ?>/dashboard" class="text-muted text-decoration-none me-2">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
            </div>
            <h2 class="fw-bold mb-0">Hồ sơ cá nhân</h2>
            <p class="text-muted">Quản lý thông tin và cài đặt bảo mật cho tài khoản của bạn.</p>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3 mb-4">
            <div class="list-group list-group-flush rounded-4 bg-white shadow-sm border-0 sticky-top" style="top: 80px;">
                <a href="<?= $appUrl ?>/profile?tab=info" class="list-group-item list-group-item-action py-3 border-bottom-0 <?= $tab === 'info' ? 'active rounded-top-4' : '' ?>">
                    <i class="bi bi-person me-2"></i>Thông tin cá nhân
                </a>
                <a href="<?= $appUrl ?>/profile?tab=security" class="list-group-item list-group-item-action py-3 border-bottom-0 <?= $tab === 'security' ? 'active rounded-bottom-4' : '' ?>">
                    <i class="bi bi-shield-lock me-2"></i>Bảo mật & Tài khoản
                </a>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-9">
            <?php if ($tab === 'info'): ?>
                <!-- Tab: Thông tin cá nhân -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 d-flex align-items-center gap-2">
                        <h5 class="fw-bold mb-0">Ảnh đại diện</h5>
                        <span class="badge bg-<?= $rank['color'] ?? 'secondary' ?> rounded-pill" style="font-size: 0.8rem; letter-spacing: 0.05em">
                            <i class="bi bi-<?= $rank['icon'] ?? 'person' ?> me-1"></i><?= $rank['name'] ?? 'Tân binh' ?>
                        </span>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="d-flex align-items-center gap-4">
                            <div class="position-relative">
                                <?php if (!empty($user['avatar'])): ?>
                                    <img src="<?= $appUrl ?>/public/uploads/<?= htmlspecialchars($user['avatar'], ENT_QUOTES) ?>" alt="Avatar" class="rounded-circle object-fit-cover shadow-sm border" style="width: 100px; height: 100px;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px; font-size: 2.5rem; font-weight: 700;">
                                        <?= mb_strtoupper(mb_substr($user['name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                            <div>
                                <!-- Input file ẩn — chỉ dùng để chọn ảnh, không submit trực tiếp -->
                                <input type="file" id="avatarFileInput" class="d-none"
                                       accept="image/jpeg,image/png,image/webp">

                                <button type="button" class="btn btn-outline-primary rounded-pill px-4"
                                        onclick="document.getElementById('avatarFileInput').click()">
                                    <i class="bi bi-scissors me-2"></i>Chọn &amp; Cắt ảnh
                                </button>
                                <div class="text-muted small mt-2">JPG, PNG hoặc WEBP. Tối đa 10MB.</div>
                            </div>

                            <!-- Modal Crop ảnh -->
                            <div class="modal fade" id="cropModal" tabindex="-1" data-bs-backdrop="static">
                              <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content rounded-4 border-0 shadow">
                                  <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold">
                                      <i class="bi bi-crop me-2 text-primary"></i>Cắt ảnh đại diện
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                  </div>
                                  <div class="modal-body px-4">
                                    <div style="max-height:420px;overflow:hidden;border-radius:12px;background:#f1f3f9;">
                                      <img id="cropImage" src="" alt="Crop" style="max-width:100%;display:block;">
                                    </div>
                                    <p class="text-muted small text-center mt-2 mb-0">
                                      <i class="bi bi-info-circle me-1"></i>
                                      Kéo để di chuyển · Cuộn để zoom · Tỷ lệ 1:1 (ảnh tròn)
                                    </p>
                                  </div>
                                  <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">
                                      <i class="bi bi-x me-1"></i>Hủy
                                    </button>
                                    <button type="button" class="btn btn-primary rounded-3 px-4" id="btnConfirmCrop">
                                      <i class="bi bi-check2 me-1"></i>Xác nhận &amp; Đặt làm ảnh đại diện
                                    </button>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <!-- Form submit ẩn (nhận canvas blob từ Cropper) -->
                            <form id="avatarCropForm" action="<?= $appUrl ?>/profile/avatar"
                                  method="POST" enctype="multipart/form-data" class="d-none">
                              <input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?>">
                              <input type="file" name="avatar" id="avatarCroppedInput">
                            </form>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0">Chi tiết hồ sơ</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?= $appUrl ?>/profile/update" method="POST">
                            <input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?>">

                            <div class="row g-3 mb-4">
                                <h6 class="text-primary fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Thông tin cơ bản</h6>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES) ?>" readonly disabled>
                                    <div class="form-text mt-1 text-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>Đã được dùng để đăng nhập
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Số điện thoại</label>
                                    <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES) ?>">
                                </div>
                            </div>

                            <hr class="my-4 opacity-10">

                            <div class="row g-3 mb-4">
                                <h6 class="text-primary fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Thông tin giao dịch & Mạng xã hội</h6>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Trường / Khoa</label>
                                    <input type="text" class="form-control" name="university" placeholder="Vd: ĐH KHTN - Khoa CNTT" value="<?= htmlspecialchars($user['university'] ?? '', ENT_QUOTES) ?>">
                                    <div class="form-text">Tăng uy tín khi giao dịch</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mã số sinh viên (Tùy chọn)</label>
                                    <input type="text" class="form-control" name="student_id" value="<?= htmlspecialchars($user['student_id'] ?? '', ENT_QUOTES) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Ký túc xá / Địa chỉ giao nhận</label>
                                    <input type="text" class="form-control" name="dormitory_address" placeholder="Vd: KTX Khu B - Tòa BA1 - Phòng 204" value="<?= htmlspecialchars($user['dormitory_address'] ?? '', ENT_QUOTES) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Liên kết Zalo / Facebook</label>
                                    <input type="text" class="form-control" name="social_contact" placeholder="Vd: zalo.me/0901234567" value="<?= htmlspecialchars($user['social_contact'] ?? '', ENT_QUOTES) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Kết nối thường xuyên</label>
                                    <input type="text" class="form-control" name="available_time" placeholder="Vd: Sáng / Tối / Cuối tuần" value="<?= htmlspecialchars($user['available_time'] ?? '', ENT_QUOTES) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Tiểu sử ngắn</label>
                                    <textarea class="form-control" name="bio" rows="3" placeholder="Giới thiệu đôi nét về bản thân hoặc các món đồ bạn thường mua bán..."><?= htmlspecialchars($user['bio'] ?? '', ENT_QUOTES) ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-4 rounded-3 fw-semibold">
                                    <i class="bi bi-check-lg me-2"></i>Lưu thay đổi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php elseif ($tab === 'security'): ?>
                <!-- Tab: Bảo mật -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold mb-0">Thay đổi mật khẩu</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?= $appUrl ?>/profile/password" method="POST">
                            <input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mật khẩu hiện tại</label>
                                <input type="password" class="form-control" name="old_password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mật khẩu mới</label>
                                <input type="password" class="form-control" name="new_password" required minlength="8">
                                <div class="form-text">Tối thiểu 8 ký tự.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                                <input type="password" class="form-control" name="confirm_password" required minlength="8">
                            </div>

                            <button type="submit" class="btn btn-warning px-4 rounded-3 fw-semibold">
                                <i class="bi bi-key me-2"></i>Đổi mật khẩu
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Info Cards cho Xác thực & Câu hỏi bảo mật -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light rounded-4 h-100 p-4 pb-3">
                            <div class="d-flex mb-2">
                                <div class="fs-3 text-success me-3"><i class="bi bi-envelope-check"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Xác thực Email</h6>
                                    <p class="text-muted small mb-0">
                                        <?php if ($user['is_verified']): ?>
                                            Email liên kết với tài khoản này đã được xác thực an toàn.
                                        <?php else: ?>
                                            Email của bạn chưa được xác thực bằng mã OTP.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light rounded-4 h-100 p-4 pb-3">
                            <div class="d-flex mb-2">
                                <div class="fs-3 text-info me-3"><i class="bi bi-shield-check"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-1">Câu hỏi bảo mật</h6>
                                    <p class="text-muted small mb-0">
                                        <?php if (!empty($user['security_question'])): ?>
                                            Câu hỏi bảo mật đã được thiết lập. 
                                            <div class="mt-1 font-monospace small bg-white border p-1 rounded d-inline-block">"<?= htmlspecialchars($user['security_question']) ?>"</div>
                                        <?php else: ?>
                                            Bạn chưa cài đặt câu hỏi bảo mật.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature 3B: L\u1ecbch s\u1eed \u0111\u0103ng nh\u1eadp -->
                <div class="card border-0 shadow-sm rounded-4 mt-4">
                  <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>L\u1ecbch s\u1eed \u0111\u0103ng nh\u1eadp</h5>
                    <span class="badge bg-light text-muted" style="font-size:.75rem;">15 phi\u00ean g\u1ea7n nh\u1ea5t</span>
                  </div>
                  <div class="card-body px-4 pb-4">
                    <?php if (empty($loginHistory)): ?>
                      <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>Ch\u01b0a c\u00f3 d\u1eef li\u1ec7u \u0111\u0103ng nh\u1eadp n\u00e0o.</p>
                    <?php else: ?>
                      <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0" style="font-size:.85rem;">
                          <thead>
                            <tr style="color:#94a3b8;font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;">
                              <th class="border-0 ps-0">Th\u1eddi gian</th>
                              <th class="border-0">\u0110\u1ecba ch\u1ec9 IP</th>
                              <th class="border-0">Thi\u1ebft b\u1ecb</th>
                              <th class="border-0 text-end">Tr\u1ea1ng th\u00e1i</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php foreach ($loginHistory as $i => $log): ?>
                            <tr class="<?= $i === 0 ? 'fw-semibold' : '' ?>">
                              <td class="ps-0 text-nowrap"><?= date('d/m/Y H:i', strtotime($log['logged_at'])) ?></td>
                              <td><code style="font-size:.8rem;background:var(--bg);padding:2px 6px;border-radius:6px;"><?= htmlspecialchars($log['ip_address'], ENT_QUOTES) ?></code></td>
                              <td class="text-muted"><?= htmlspecialchars($log['device_info'], ENT_QUOTES) ?></td>
                              <td class="text-end">
                                <?php if ($i === 0): ?>
                                  <span class="badge bg-success" style="font-size:.72rem;">\ud83d\udfe2 Hi\u1ec7n t\u1ea1i</span>
                                <?php elseif ($log['is_new_device']): ?>
                                  <span class="badge bg-warning text-dark" style="font-size:.72rem;">\u26a0\ufe0f Thi\u1ebft b\u1ecb l\u1ea1</span>
                                <?php else: ?>
                                  <span class="badge bg-light text-muted" style="font-size:.72rem;">\u2713 B\u00ecnh th\u01b0\u1eddng</span>
                                <?php endif; ?>
                              </td>
                            </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

            <?php endif; ?>
    </div>
</div>

<!-- Cropper.js CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>

<script>
(function () {
  const fileInput    = document.getElementById('avatarFileInput');
  const cropImage    = document.getElementById('cropImage');
  const btnConfirm   = document.getElementById('btnConfirmCrop');
  const cropForm     = document.getElementById('avatarCropForm');
  const croppedInput = document.getElementById('avatarCroppedInput');

  if (!fileInput) return;

  let cropperInstance = null;
  let cropModalBS     = null;

  fileInput.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    if (file.size > 10 * 1024 * 1024) {
      alert('Ảnh quá lớn! Vui lòng chọn ảnh nhỏ hơn 10MB.');
      this.value = ''; return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
      cropImage.src = e.target.result;
      if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
      if (!cropModalBS) cropModalBS = new bootstrap.Modal(document.getElementById('cropModal'));

      document.getElementById('cropModal').addEventListener('shown.bs.modal', function onShown() {
        cropperInstance = new Cropper(cropImage, {
          aspectRatio: 1, viewMode: 1, autoCropArea: 0.85,
          responsive: true, background: false, guides: true,
          toggleDragModeOnDblclick: false,
        });
        document.getElementById('cropModal').removeEventListener('shown.bs.modal', onShown);
      }, { once: true });

      cropModalBS.show();
    };
    reader.readAsDataURL(file);
    this.value = '';
  });

  btnConfirm.addEventListener('click', function () {
    if (!cropperInstance) return;
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';

    cropperInstance.getCroppedCanvas({ width: 400, height: 400,
      imageSmoothingEnabled: true, imageSmoothingQuality: 'high' })
    .toBlob(function (blob) {
      const croppedFile = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
      const dt = new DataTransfer();
      dt.items.add(croppedFile);
      croppedInput.files = dt.files;
      cropForm.submit();
    }, 'image/jpeg', 0.92);
  });

  document.getElementById('cropModal').addEventListener('hidden.bs.modal', function () {
    if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
    cropImage.src = '';
    btnConfirm.disabled = false;
    btnConfirm.innerHTML = '<i class="bi bi-check2 me-1"></i>Xác nhận &amp; Đặt làm ảnh đại diện';
  });
})();
</script>
