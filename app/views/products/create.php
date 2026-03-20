<?php
/**
 * View: Trang tạo/đăng sản phẩm
 * Biến nhận: $categories, $errors, $old
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
use Core\Flash;
use Core\Controller;

$ctrl = new class extends Controller {};
$csrf = $ctrl->csrfToken();
?>
<div class="container py-4" style="max-width:760px">

  <h4 class="section-title mb-4"><i class="bi bi-plus-circle me-2 text-primary"></i>Đăng bán sản phẩm</h4>

  <?= Flash::render() ?>

  <form action="<?= $appUrl ?>/products/create" method="POST" enctype="multipart/form-data" novalidate id="createForm">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

    <div class="card-sv p-4 mb-4">
      <h6 class="fw-700 mb-3 text-primary">Thông tin sản phẩm</h6>

      <!-- Tiêu đề -->
      <div class="mb-3">
        <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
               placeholder="VD: Giáo trình Toán Cao Cấp A1 – còn mới 90%"
               value="<?= htmlspecialchars($old['title'] ?? '', ENT_QUOTES) ?>" required>
        <?php if (isset($errors['title'])): ?>
          <div class="invalid-feedback"><?= htmlspecialchars($errors['title']) ?></div>
        <?php endif; ?>
      </div>

      <!-- Mô tả -->
      <div class="mb-3">
        <label class="form-label">Mô tả chi tiết <span class="text-danger">*</span></label>
        <textarea name="description" rows="4"
                  class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                  placeholder="Tình trạng đồ, lý do bán, thông tin liên hệ giao nhận..."
                  required><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES) ?></textarea>
        <?php if (isset($errors['description'])): ?>
          <div class="invalid-feedback"><?= htmlspecialchars($errors['description']) ?></div>
        <?php endif; ?>
      </div>

      <!-- Danh mục + Loại -->
      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label">Danh mục <span class="text-danger">*</span></label>
          <select name="category_id" class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" required>
            <option value="">-- Chọn danh mục --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"
                <?= ($old['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['category_id'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['category_id']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-sm-6">
          <label class="form-label">Loại đăng <span class="text-danger">*</span></label>
          <select name="type" id="productType" class="form-select" required onchange="toggleTypeFields()">
            <option value="sale"     <?= ($old['type'] ?? '') === 'sale'     ? 'selected' : '' ?>>💰 Bán thường</option>
            <option value="exchange" <?= ($old['type'] ?? '') === 'exchange' ? 'selected' : '' ?>>🔄 Trao đổi</option>
            <option value="auction"  <?= ($old['type'] ?? '') === 'auction'  ? 'selected' : '' ?>>⚡ Đấu giá ngược</option>
          </select>
        </div>
      </div>

      <!-- Upload ảnh -->
      <div class="mb-3">
        <label class="form-label">Hình ảnh sản phẩm <span class="text-muted">(tối đa 3MB)</span></label>
        <input type="file" name="image" id="imageInput" accept="image/*"
               class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>"
               onchange="previewImage(this)">
        <?php if (isset($errors['image'])): ?>
          <div class="invalid-feedback"><?= htmlspecialchars($errors['image']) ?></div>
        <?php endif; ?>
        <div id="imagePreview" class="mt-2" style="display:none">
          <img id="previewImg" src="" alt="Preview" class="rounded" style="max-height:160px;max-width:100%;object-fit:contain;border:2px dashed var(--border)">
        </div>
      </div>
    </div>

    <!-- ─── Giá bán thường ─────────── -->
    <div class="card-sv p-4 mb-4" id="salePriceBox">
      <h6 class="fw-700 mb-3 text-primary">Giá bán</h6>
      <div class="mb-0">
        <label class="form-label">Giá bán (VNĐ) <span class="text-danger">*</span></label>
        <div class="input-group">
          <input type="number" name="price" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>"
                 placeholder="VD: 50000" min="1000"
                 value="<?= htmlspecialchars($old['price'] ?? '', ENT_QUOTES) ?>">
          <span class="input-group-text">đ</span>
          <?php if (isset($errors['price'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['price']) ?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ─── Cấu hình đấu giá ngược ── -->
    <div class="card-sv p-4 mb-4" id="auctionBox" style="display:none">
      <h6 class="fw-700 mb-1 text-danger"><i class="bi bi-lightning-fill me-2"></i>Cấu hình đấu giá ngược</h6>
      <p class="small text-muted mb-3">Giá sẽ tự động giảm dần theo thời gian cho đến khi có người mua hoặc chạm giá sàn.</p>

      <div class="row g-3">
        <div class="col-sm-6">
          <label class="form-label">Giá khởi điểm (VNĐ) <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="number" name="start_price" class="form-control <?= isset($errors['start_price']) ? 'is-invalid' : '' ?>"
                   placeholder="80000" min="1000"
                   value="<?= htmlspecialchars($old['start_price'] ?? '', ENT_QUOTES) ?>">
            <span class="input-group-text">đ</span>
          </div>
          <?php if (isset($errors['start_price'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['start_price']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-sm-6">
          <label class="form-label">Giá sàn (VNĐ) <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="number" name="floor_price" class="form-control <?= isset($errors['floor_price']) ? 'is-invalid' : '' ?>"
                   placeholder="30000" min="0"
                   value="<?= htmlspecialchars($old['floor_price'] ?? '', ENT_QUOTES) ?>">
            <span class="input-group-text">đ</span>
          </div>
          <?php if (isset($errors['floor_price'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['floor_price']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-sm-6">
          <label class="form-label">Mức giảm mỗi lần (VNĐ) <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="number" name="decrease_amount" class="form-control <?= isset($errors['decrease_amount']) ? 'is-invalid' : '' ?>"
                   placeholder="5000" min="1000"
                   value="<?= htmlspecialchars($old['decrease_amount'] ?? '', ENT_QUOTES) ?>">
            <span class="input-group-text">đ</span>
          </div>
          <?php if (isset($errors['decrease_amount'])): ?>
            <div class="text-danger small mt-1"><?= htmlspecialchars($errors['decrease_amount']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-sm-6">
          <label class="form-label">Chu kỳ giảm (phút) <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="number" name="step_minutes" class="form-control <?= isset($errors['step_minutes']) ? 'is-invalid' : '' ?>"
                   placeholder="10" min="1" max="1440"
                   value="<?= htmlspecialchars($old['step_minutes'] ?? '10', ENT_QUOTES) ?>">
            <span class="input-group-text">phút</span>
          </div>
        </div>
      </div>

      <!-- Preview công thức -->
      <div class="mt-3 p-3 bg-light rounded-3 small text-muted" id="formulaPreview" style="display:none">
        <i class="bi bi-info-circle me-1"></i>
        Công thức: Giá hiện tại = <span id="fStart">?</span> − (Số bước × <span id="fDecrease">?</span>)
        <br>Giá sàn tối thiểu: <span id="fFloor">?</span>
      </div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary px-4" id="btnSubmit">
        <i class="bi bi-send me-2"></i>Đăng bài
      </button>
      <a href="<?= $appUrl ?>/products" class="btn btn-outline-secondary">Hủy</a>
    </div>
  </form>
</div>

<script>
function toggleTypeFields() {
  var type = document.getElementById('productType').value;
  document.getElementById('salePriceBox').style.display  = (type === 'sale')    ? '' : 'none';
  document.getElementById('auctionBox').style.display    = (type === 'auction') ? '' : 'none';
}

function previewImage(input) {
  var preview = document.getElementById('imagePreview');
  var img     = document.getElementById('previewImg');
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) { img.src = e.target.result; preview.style.display = ''; };
    reader.readAsDataURL(input.files[0]);
  }
}

// Realtime formula preview
['start_price','floor_price','decrease_amount','step_minutes'].forEach(function(name) {
  var el = document.querySelector('[name="' + name + '"]');
  if (el) el.addEventListener('input', updateFormula);
});

function updateFormula() {
  var start    = parseInt(document.querySelector('[name="start_price"]').value)    || 0;
  var floor    = parseInt(document.querySelector('[name="floor_price"]').value)    || 0;
  var decrease = parseInt(document.querySelector('[name="decrease_amount"]').value) || 0;
  var box      = document.getElementById('formulaPreview');

  if (start > 0 && decrease > 0) {
    box.style.display = '';
    document.getElementById('fStart').textContent    = start.toLocaleString('vi-VN') + 'đ';
    document.getElementById('fDecrease').textContent = decrease.toLocaleString('vi-VN') + 'đ';
    document.getElementById('fFloor').textContent    = floor.toLocaleString('vi-VN') + 'đ';
  } else {
    box.style.display = 'none';
  }
}

// Init
toggleTypeFields();

// Prevent double submit
document.getElementById('createForm').addEventListener('submit', function() {
  var btn = document.getElementById('btnSubmit');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang đăng...';
});
</script>
