<?php
/**
 * View: Trang tạo/đăng sản phẩm
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
use Core\Flash;
use Core\Controller;
$ctrl = new class extends Controller {};
$csrf = $ctrl->csrfToken();

// Helper: input field
function formField(string $name, string $label, string $type, string $placeholder, array $errors, array $old, array $extra = []): void {
    $hasErr = isset($errors[$name]);
    $borderCls = $hasErr ? 'border-danger ring-4 ring-danger/10' : 'border-light-border focus:border-primary focus:ring-4 focus:ring-primary/10';
    echo "<div class=\"mb-4\">";
    echo "<label class=\"form-label text-sm\" for=\"{$name}\">{$label}</label>";
    $attrs = array_merge(['type' => $type, 'name' => $name, 'id' => $name, 'placeholder' => $placeholder], $extra);
    $attrsStr = '';
    foreach ($attrs as $k => $v) $attrsStr .= " {$k}=\"" . htmlspecialchars($v, ENT_QUOTES) . "\"";
    $val = htmlspecialchars($old[$name] ?? '', ENT_QUOTES);
    echo "<input{$attrsStr} value=\"{$val}\" class=\"form-control w-full {$borderCls}\">";
    if ($hasErr) echo "<p class=\"text-xs text-danger mt-1\"><i class=\"bi bi-exclamation-circle mr-1\"></i>" . htmlspecialchars($errors[$name]) . "</p>";
    echo "</div>";
}
?>

<div class="container mx-auto px-4 py-8" style="max-width:760px">

  <h1 class="text-2xl font-extrabold text-gray-800 dark:text-dark-text flex items-center gap-2 mb-6">
    <i class="bi bi-plus-circle text-primary"></i>Đăng bán sản phẩm
  </h1>

  <?= Flash::render() ?>

  <form action="<?= $appUrl ?>/products/create" method="POST" enctype="multipart/form-data" novalidate id="createForm">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">

    <!-- ─── Thông tin sản phẩm ──────────────────────── -->
    <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border p-6 mb-5">
      <h2 class="text-sm font-extrabold text-primary uppercase tracking-wider mb-4">Thông tin sản phẩm</h2>

      <!-- Tiêu đề -->
      <div class="mb-4">
        <label class="form-label text-sm">Tên sản phẩm <span class="text-danger">*</span></label>
        <input type="text" name="title" placeholder="VD: Giáo trình Toán Cao Cấp A1 – còn mới 90%"
               value="<?= htmlspecialchars($old['title'] ?? '', ENT_QUOTES) ?>"
               class="form-control w-full <?= isset($errors['title']) ? 'border-danger ring-4 ring-danger/10' : '' ?>" required>
        <?php if (isset($errors['title'])): ?><p class="text-xs text-danger mt-1"><i class="bi bi-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['title']) ?></p><?php endif; ?>
      </div>

      <!-- Mô tả -->
      <div class="mb-4">
        <label class="form-label text-sm">Mô tả chi tiết <span class="text-danger">*</span></label>
        <textarea name="description" rows="4" placeholder="Tình trạng đồ, lý do bán, thông tin liên hệ giao nhận..."
                  class="form-control w-full <?= isset($errors['description']) ? 'border-danger ring-4 ring-danger/10' : '' ?>" required><?= htmlspecialchars($old['description'] ?? '', ENT_QUOTES) ?></textarea>
        <?php if (isset($errors['description'])): ?><p class="text-xs text-danger mt-1"><i class="bi bi-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['description']) ?></p><?php endif; ?>
      </div>

      <!-- Danh mục + Tình trạng -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div>
          <label class="form-label text-sm">Danh mục <span class="text-danger">*</span></label>
          <select name="category_id" class="form-control w-full <?= isset($errors['category_id']) ? 'border-danger' : '' ?>" required>
            <option value="">-- Chọn danh mục --</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ($old['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['category_id'])): ?><p class="text-xs text-danger mt-1"><?= htmlspecialchars($errors['category_id']) ?></p><?php endif; ?>
        </div>
        <div>
          <label class="form-label text-sm">Tình trạng <span class="text-danger">*</span></label>
          <select name="condition" class="form-control w-full" required>
            <?php foreach (['new' => 'Mới 100%', 'like_new' => 'Như mới (90%+)', 'used' => 'Đã qua sử dụng', 'worn' => 'Cũ & có dấu vết'] as $val => $lbl): ?>
              <option value="<?= $val ?>" <?= ($old['condition'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Loại đăng -->
      <div class="mb-4 sm:w-1/2">
        <label class="form-label text-sm">Loại đăng <span class="text-danger">*</span></label>
        <select name="type" id="productType" class="form-control w-full" required onchange="toggleTypeFields()">
          <option value="sale"     <?= ($old['type'] ?? '') === 'sale'     ? 'selected' : '' ?>>💰 Bán thường</option>
          <option value="exchange" <?= ($old['type'] ?? '') === 'exchange' ? 'selected' : '' ?>>🔄 Trao đổi</option>
          <option value="auction"  <?= ($old['type'] ?? '') === 'auction'  ? 'selected' : '' ?>>⚡ Đấu giá ngược</option>
        </select>
      </div>

      <!-- Hình ảnh -->
      <div class="mb-2">
        <label class="form-label text-sm">Hình ảnh sản phẩm <span class="text-xs text-gray-400">(tối đa 3MB)</span></label>
        <input type="file" name="image" id="imageInput" accept="image/*"
               class="form-control w-full <?= isset($errors['image']) ? 'border-danger' : '' ?>"
               onchange="previewImage(this)">
        <?php if (isset($errors['image'])): ?><p class="text-xs text-danger mt-1"><?= htmlspecialchars($errors['image']) ?></p><?php endif; ?>
        <div id="imagePreview" class="mt-2 hidden">
          <img id="previewImg" src="" alt="Preview" class="rounded-xl max-h-40 object-contain border-2 border-dashed border-light-border">
        </div>
      </div>
    </div>

    <!-- ─── Giá bán thường ──────────────────────────── -->
    <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border p-6 mb-5" id="salePriceBox">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-sm font-extrabold text-primary uppercase tracking-wider m-0">Giá bán</h2>
        <button type="button" onclick="suggestPriceByAI()" id="btnSuggestAI" class="text-xs font-bold text-white bg-indigo-500 hover:bg-indigo-600 px-3 py-1.5 rounded-lg transition-colors border-0 cursor-pointer flex items-center gap-1 shadow-sm">
          <i class="bi bi-magic"></i> AI Gợi ý giá
        </button>
      </div>
      <label class="form-label text-sm">Giá bán (VNĐ) <span class="text-danger">*</span></label>
      <div class="flex rounded-sm border-2 border-light-border focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10 transition-all overflow-hidden">
        <input type="number" name="price" id="inputSalePrice" placeholder="VD: 50000" min="1000"
               value="<?= htmlspecialchars($old['price'] ?? '', ENT_QUOTES) ?>"
               class="flex-1 px-3 py-2.5 text-sm border-0 outline-none bg-white dark:bg-dark-card">
        <span class="flex items-center px-3 bg-gray-50 dark:bg-dark-2 text-gray-500 border-l border-light-border font-bold">đ</span>
      </div>
      <?php if (isset($errors['price'])): ?><p class="text-xs text-danger mt-1"><?= htmlspecialchars($errors['price']) ?></p><?php endif; ?>
      <div id="aiSuggestionBox" class="hidden mt-3 p-3 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/30 text-sm text-indigo-900 dark:text-indigo-200">
        <!-- AI Suggestion content will appear here -->
      </div>
    </div>

    <!-- ─── Đấu giá ngược ──────────────────────────── -->
    <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border p-6 mb-5 hidden" id="auctionBox">
      <h2 class="text-sm font-extrabold text-danger uppercase tracking-wider mb-1 flex items-center gap-2">
        <i class="bi bi-lightning-fill"></i>Cấu hình đấu giá ngược
      </h2>
      <p class="text-xs text-gray-400 mb-4">Giá sẽ tự động giảm dần theo thời gian cho đến khi có người mua hoặc chạm giá sàn.</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <?php
          $aFields = [
            ['start_price',    'Giá khởi điểm (VNĐ)', '80000', '1000'],
            ['floor_price',    'Giá sàn (VNĐ)',        '30000', '0'],
            ['decrease_amount','Mức giảm mỗi lần (VNĐ)', '5000', '1000'],
            ['step_minutes',   'Chu kỳ giảm (phút)',   '10',    '1'],
          ];
          foreach ($aFields as [$aName, $aLabel, $aPlaceholder, $aMin]):
        ?>
          <div>
            <label class="form-label text-sm"><?= $aLabel ?> <span class="text-danger">*</span></label>
            <div class="flex rounded-sm border-2 border-light-border focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10 transition-all overflow-hidden">
              <input type="number" name="<?= $aName ?>" placeholder="<?= $aPlaceholder ?>" min="<?= $aMin ?>"
                     value="<?= htmlspecialchars($old[$aName] ?? ($aName === 'step_minutes' ? '10' : ''), ENT_QUOTES) ?>"
                     class="flex-1 px-3 py-2.5 text-sm border-0 outline-none bg-white dark:bg-dark-card">
              <span class="flex items-center px-3 bg-gray-50 dark:bg-dark-2 text-gray-500 border-l border-light-border font-bold text-xs">
                <?= $aName === 'step_minutes' ? 'phút' : 'đ' ?>
              </span>
            </div>
            <?php if (isset($errors[$aName])): ?><p class="text-xs text-danger mt-1"><?= htmlspecialchars($errors[$aName]) ?></p><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <!-- Formula preview -->
      <div id="formulaPreview" class="hidden mt-4 p-3 rounded-xl bg-blue-50 dark:bg-dark-2 text-xs text-gray-500">
        <i class="bi bi-info-circle mr-1"></i>
        Công thức: Giá hiện tại = <span id="fStart">?</span> − (Số bước × <span id="fDecrease">?</span>)
        <br>Giá sàn tối thiểu: <span id="fFloor">?</span>
      </div>
    </div>

    <!-- Submit -->
    <div class="flex gap-3">
      <button type="submit" id="btnSubmit"
              class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white font-bold text-sm border-0 cursor-pointer hover:brightness-110 transition-all shadow shadow-primary/30">
        <i class="bi bi-send"></i>Đăng bài
      </button>
      <a href="<?= $appUrl ?>/products"
         class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border-2 border-light-border text-gray-500 font-semibold text-sm no-underline hover:bg-gray-50 transition-all">
        Hủy
      </a>
    </div>
  </form>
</div>

<script>
function toggleTypeFields() {
  const t = document.getElementById('productType').value;
  document.getElementById('salePriceBox').classList.toggle('hidden', t !== 'sale');
  document.getElementById('auctionBox').classList.toggle('hidden',   t !== 'auction');
}
function previewImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('previewImg').src = e.target.result;
      document.getElementById('imagePreview').classList.remove('hidden');
    };
    reader.readAsDataURL(input.files[0]);
  }
}
['start_price','floor_price','decrease_amount'].forEach(n => {
  const el = document.querySelector(`[name="${n}"]`);
  if (el) el.addEventListener('input', updateFormula);
});
function updateFormula() {
  const start    = parseInt(document.querySelector('[name="start_price"]').value)    || 0;
  const floor    = parseInt(document.querySelector('[name="floor_price"]').value)    || 0;
  const decrease = parseInt(document.querySelector('[name="decrease_amount"]').value) || 0;
  const box      = document.getElementById('formulaPreview');
  if (start > 0 && decrease > 0) {
    box.classList.remove('hidden');
    document.getElementById('fStart').textContent    = start.toLocaleString('vi-VN') + 'đ';
    document.getElementById('fDecrease').textContent = decrease.toLocaleString('vi-VN') + 'đ';
    document.getElementById('fFloor').textContent    = floor.toLocaleString('vi-VN') + 'đ';
  } else { box.classList.add('hidden'); }
}

async function suggestPriceByAI() {
  const btn = document.getElementById('btnSuggestAI');
  const box = document.getElementById('aiSuggestionBox');
  const title = document.querySelector('[name="title"]').value.trim();
  const categoryId = document.querySelector('[name="category_id"]').value;
  const condition = document.querySelector('[name="condition"]').value;
  const csrf = document.querySelector('[name="_csrf"]').value;

  if (title.length < 5) {
    alert('Vui lòng nhập tên sản phẩm (ít nhất 5 ký tự) trước khi gọi AI gợi ý giá!');
    document.querySelector('[name="title"]').focus();
    return;
  }

  btn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> Đang phân tích...';
  btn.disabled = true;
  box.classList.add('hidden');

  try {
    const fd = new FormData();
    fd.append('title', title);
    fd.append('category_id', categoryId);
    fd.append('condition', condition);
    fd.append('_csrf', csrf);

    const res = await fetch('<?= $appUrl ?>/products/suggest-price', {
      method: 'POST',
      body: fd
    });
    const data = await res.json();
    
    box.classList.remove('hidden');
    if (data.success) {
      box.innerHTML = `<p class="font-bold mb-1 flex items-center gap-1"><i class="bi bi-robot"></i> Trợ lý AI Gợi ý:</p><p class="whitespace-pre-wrap">${data.suggestion}</p>`;
      
      // Attempt to extract numbers from AI response to pre-fill the input automatically
      const priceMatch = data.suggestion.replace(/\./g, '').match(/(\d+000)/);
      if (priceMatch && !document.getElementById('inputSalePrice').value) {
        document.getElementById('inputSalePrice').value = priceMatch[1];
      }
    } else {
      box.innerHTML = `<p class="text-danger"><i class="bi bi-exclamation-triangle"></i> Lỗi: ${data.message}</p>`;
    }
  } catch (err) {
    box.classList.remove('hidden');
    box.innerHTML = `<p class="text-danger"><i class="bi bi-wifi-off"></i> Không thể kết nối tới máy chủ AI. Vui lòng thử lại sau.</p>`;
  } finally {
    btn.innerHTML = '<i class="bi bi-magic"></i> AI Gợi ý giá';
    btn.disabled = false;
  }
}
document.addEventListener('DOMContentLoaded', () => { toggleTypeFields(); updateFormula(); });

document.getElementById('createForm').addEventListener('submit', function() {
  const btn = document.getElementById('btnSubmit');
  btn.disabled = true;
  btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>Đang đăng...';
});
</script>
