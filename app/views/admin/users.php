<?php
/**
 * Admin View: Quản lý người dùng — Tailwind Edition
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$me     = $_SESSION['user'];

// Generate CSRF token properly from session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

use Core\Flash;

$durationOptions = [
    '3days'   => '📅 3 ngày (nhắc nhở nhẹ)',
    '1week'   => '📅 1 tuần (vi phạm nhẹ)',
    '2weeks'  => '📅 2 tuần (vi phạm vừa)',
    '1month'  => '📅 1 tháng (vi phạm nặng)',
    '3months' => '📅 3 tháng (vi phạm nghiêm trọng)',
    '6months' => '📅 6 tháng (tái phạm nhiều lần)',
    'forever' => '🔒 Vĩnh viễn (trường hợp đặc biệt)',
];
?>

<div class="font-sans antialiased text-gray-800 dark:text-dark-text">
  <!-- Page Header -->
  <div class="relative overflow-hidden bg-gradient-to-br from-[#1e1b4b] via-[#312e81] to-[#4f46e5] rounded-[24px] p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6 mb-8 shadow-[0_20px_60px_rgba(99,102,241,0.3)]">
    <div class="absolute w-[350px] h-[350px] rounded-full bg-[radial-gradient(circle,rgba(139,92,246,0.35)_0%,transparent_65%)] -top-[100px] right-[50px] pointer-events-none"></div>
    
    <div class="flex items-center gap-4 relative z-10 w-full md:w-auto">
      <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-400 to-purple-400 flex items-center justify-center text-xl text-white shadow-md flex-shrink-0">
        <i class="bi bi-people-fill"></i>
      </div>
      <div>
        <div class="text-[11px] font-bold text-white/60 uppercase tracking-wider">Quản lý</div>
        <h2 class="text-2xl font-extrabold text-white my-1">Danh sách người dùng</h2>
        <div class="text-xs font-medium text-white/50">Khóa / Mở khóa tài khoản sinh viên</div>
      </div>
    </div>
    
    <div class="flex items-center gap-4 relative z-10 w-full md:w-auto">
      <button type="button" class="flex items-center justify-center w-full md:w-auto px-5 py-2.5 rounded-full bg-white/90 text-green-600 font-bold text-sm shadow-sm hover:bg-white transition-colors border-0 cursor-pointer" onclick="openExportModal('users')">
        <i class="bi bi-file-earmark-excel-fill mr-2"></i> Xuất Excel (CSV)
      </button>
      <div class="hidden lg:flex items-center bg-white/15 border border-white/30 text-white px-4 py-2 rounded-full text-sm font-bold shadow-sm whitespace-nowrap">
        <i class="bi bi-people mr-2"></i><?= count($users) ?> tài khoản
      </div>
    </div>
  </div>

  <?= Flash::render() ?>

  <!-- Table Card -->
  <div class="bg-white dark:bg-dark-card rounded-[20px] border border-light-border dark:border-dark-border overflow-hidden shadow-sm animate-[fadeInUp_0.5s_ease-out_both]">
    <div class="flex items-center justify-between px-6 py-5 border-b border-light-border dark:border-dark-border bg-gray-50/50 dark:bg-dark-2/50">
      <div class="flex items-center gap-3 font-extrabold text-base text-gray-800 dark:text-dark-text">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-base bg-gradient-to-br from-indigo-500 to-purple-500 shadow-sm">
          <i class="bi bi-people-fill"></i>
        </div>
        Tất cả tài khoản
      </div>
      <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1.5 rounded-full"><?= count($users) ?> người dùng</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse min-w-[800px]">
        <thead>
          <tr class="bg-gray-50 dark:bg-dark-2 border-b border-light-border dark:border-dark-border text-[11px] font-bold text-gray-500 uppercase tracking-wider">
            <th class="py-3 px-6">#</th>
            <th class="py-3 px-6">Người dùng</th>
            <th class="py-3 px-6">SĐT</th>
            <th class="py-3 px-6">Vai trò</th>
            <th class="py-3 px-6">Trạng thái & Gậy</th>
            <th class="py-3 px-6">Ngày tạo</th>
            <th class="py-3 px-6 text-right">Hành động</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
          <?php foreach ($users as $i => $u): ?>
            <tr class="hover:bg-gray-50/50 dark:hover:bg-dark-2/50 transition-colors">
              <td class="py-4 px-6 text-xs font-bold text-gray-400 whitespace-nowrap"><?= $i + 1 ?></td>
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0 shadow-sm">
                    <?= mb_strtoupper(mb_substr($u['name'], 0, 1)) ?>
                  </div>
                  <div>
                    <div class="font-bold text-sm text-gray-800 dark:text-dark-text leading-tight"><?= htmlspecialchars($u['name'], ENT_QUOTES) ?></div>
                    <div class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($u['email'], ENT_QUOTES) ?></div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6 text-xs font-medium text-gray-500 whitespace-nowrap"><?= htmlspecialchars($u['phone'] ?? '—', ENT_QUOTES) ?></td>
              <td class="py-4 px-6 whitespace-nowrap">
                <?php if ($u['role'] === 'admin'): ?>
                  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold text-white bg-gradient-to-br from-red-500 to-red-600 shadow-sm">
                    <i class="bi bi-shield-fill"></i>Admin
                  </span>
                <?php else: ?>
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold text-indigo-700 bg-indigo-100">
                    Sinh viên
                  </span>
                <?php endif; ?>
              </td>
              <td class="py-4 px-6 whitespace-nowrap">
                <?php if ($u['is_locked']): ?>
                  <div class="mb-1.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold text-red-600 bg-red-100 animate-[pulse_2s_cubic-bezier(0.4,0,0.6,1)_infinite]">
                      <i class="bi bi-lock-fill"></i>Bị khóa
                    </span>
                    <?php if (!empty($u['locked_until'])): ?>
                      <div class="text-[11px] font-semibold text-gray-400 mt-1">đến <?= date('d/m/Y', strtotime($u['locked_until'])) ?></div>
                    <?php else: ?>
                      <div class="text-[11px] font-bold text-red-500 mt-1">Vĩnh viễn</div>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <div class="mb-1.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold text-green-700 bg-green-100">
                      <i class="bi bi-check-circle-fill"></i>Hoạt động
                    </span>
                  </div>
                <?php endif; ?>
                
                <?php 
                  $strikes = (int)($u['strike_count'] ?? 0);
                  if ($strikes > 0): 
                ?>
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black text-white bg-red-500 shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?= $strikes ?>/3 Gậy
                  </span>
                <?php endif; ?>
              </td>
              <td class="py-4 px-6 text-xs font-medium text-gray-500 whitespace-nowrap">
                <?= date('d/m/Y', strtotime($u['created_at'])) ?>
              </td>
              <td class="py-4 px-6 text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-2">
                    <!-- CHI TIẾT -->
                    <a href="<?= $appUrl ?>/admin/users/detail?id=<?= (int)$u['id'] ?>"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-gradient-to-br from-indigo-500 to-purple-500 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgba(99,102,241,0.3)] transition-all no-underline">
                      <i class="bi bi-eye-fill"></i>Chi tiết
                    </a>
                    
                    <?php if ($u['role'] !== 'admin' && (int)$u['id'] !== (int)$me['id']): ?>
                      <!-- LỊCH SỬ GẬY -->
                      <a href="<?= $appUrl ?>/admin/users/violations?id=<?= (int)$u['id'] ?>"
                         class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-gradient-to-br from-amber-500 to-orange-500 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgba(245,158,11,0.3)] transition-all no-underline">
                        <i class="bi bi-clock-history"></i>Gậy (<?= $u['strike_count'] ?? 0 ?>)
                      </a>

                      <!-- PHẠT GẬY -->
                      <button type="button" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-gradient-to-br from-red-600 to-red-700 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgba(239,68,68,0.3)] transition-all border-0 cursor-pointer"
                              onclick="openStrikeModal(<?= (int)$u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name']), ENT_QUOTES) ?>', <?= (int)($u['strike_count'] ?? 0) ?>)">
                        <i class="bi bi-hammer"></i>Phạt Gậy
                      </button>

                      <?php if ($u['is_locked']): ?>
                        <!-- MỞ KHÓA (Thủ công) -->
                        <form method="POST" action="<?= $appUrl ?>/admin/users/toggle" class="inline-block m-0"
                              onsubmit="return confirm('Mở khóa tài khoản <?= htmlspecialchars(addslashes($u['name']), ENT_QUOTES) ?>?')">
                          <input type="hidden" name="_csrf"   value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                          <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                          <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-gradient-to-br from-emerald-500 to-green-600 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgba(16,185,129,0.3)] transition-all border-0 cursor-pointer">
                            <i class="bi bi-unlock-fill"></i>Mở khóa
                          </button>
                        </form>
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ─── STRIKE MODAL ──────────────────────────────────────────────── -->
<div id="strikeModal" class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200">
  <div class="bg-white dark:bg-dark-card rounded-[22px] shadow-[0_40px_100px_rgba(0,0,0,0.3)] p-8 w-full max-w-[500px] transform scale-95 opacity-0 transition-all duration-300 m-4">
    <div class="w-14 h-14 rounded-[16px] bg-gradient-to-br from-red-600 to-red-700 flex items-center justify-center text-white text-2xl shadow-[0_8px_24px_rgba(239,68,68,0.4)] mb-4">
      <i class="bi bi-hammer"></i>
    </div>
    <div class="text-xl font-black text-gray-800 dark:text-dark-text mb-1">Tặng Gậy Cảnh Cáo</div>
    <div id="strikeSubtitle" class="text-sm font-medium text-gray-500 mb-6">Xử phạt tài khoản vi phạm</div>

    <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-amber-700 dark:text-amber-500 px-4 py-3 rounded-xl text-sm mb-6 flex items-start gap-2">
      <i class="bi bi-info-circle-fill mt-0.5"></i>
      <span id="strikeCurrentStatus">Đang tải...</span>
    </div>

    <form method="POST" action="<?= $appUrl ?>/admin/users/strike" id="strikeForm">
      <input type="hidden" name="_csrf"    value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
      <input type="hidden" name="user_id"  id="strikeUserId" value="">

      <div class="mb-5">
        <label class="block text-[13px] font-bold text-gray-700 dark:text-gray-300 mb-2">
          <i class="bi bi-exclamation-triangle mr-1 text-red-500"></i>Lý do vi phạm (Gửi Email) <span class="text-red-500">*</span>
        </label>
        <textarea name="reason" id="strikeReason" 
                  class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-red-500 focus:ring-4 focus:ring-red-500/10 text-sm font-medium resize-y min-h-[100px] transition-all"
                  placeholder="Ví dụ: Đăng bán hàng giả, lừa đảo, dùng ngôn từ thô tục..." required></textarea>
      </div>
                
      <div class="mb-6">
        <label class="block text-[13px] font-bold text-gray-700 dark:text-gray-300 mb-2">
          <i class="bi bi-link-45deg mr-1"></i>Link Bằng Chứng (Tùy chọn)
        </label>
        <input type="url" name="evidence_url" id="strikeEvidence" 
               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-red-500 focus:ring-4 focus:ring-red-500/10 text-sm font-medium transition-all" 
               placeholder="https://...">
      </div>

      <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-red-600 to-red-700 text-white font-extrabold text-sm hover:shadow-[0_8px_24px_rgba(239,68,68,0.4)] hover:-translate-y-0.5 transition-all border-0 cursor-pointer mb-2">
        <i class="bi bi-hammer mr-2"></i>Xác nhận Tặng Gậy
      </button>
      <button type="button" class="w-full py-3 rounded-xl bg-gray-100 dark:bg-dark-2 text-gray-500 dark:text-gray-400 font-bold text-sm hover:bg-gray-200 dark:hover:bg-dark-border transition-colors border-0 cursor-pointer" onclick="closeStrikeModal()">
        Hủy bỏ
      </button>
    </form>
  </div>
</div>

<!-- ─── EXPORT MODAL ──────────────────────────────────────────────── -->
<div id="exportModal" class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200">
  <div class="bg-white dark:bg-dark-card rounded-[22px] shadow-[0_40px_100px_rgba(0,0,0,0.3)] p-8 w-full max-w-[500px] transform scale-95 opacity-0 transition-all duration-300 m-4">
    <div class="w-14 h-14 rounded-[16px] bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center text-white text-2xl shadow-[0_8px_24px_rgba(16,185,129,0.4)] mb-4">
      <i class="bi bi-file-earmark-excel-fill"></i>
    </div>
    <div class="text-xl font-black text-gray-800 dark:text-dark-text mb-1">Xuất Dữ Liệu</div>
    <div class="text-sm font-medium text-gray-500 mb-6">Chọn khoảng thời gian để xuất báo cáo</div>

    <form method="GET" action="<?= $appUrl ?>/admin/export" id="exportForm">
      <input type="hidden" name="type" id="exportType" value="users">
      <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">

      <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
          <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Từ ngày</label>
          <input type="date" name="from" class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-emerald-500 text-sm font-medium transition-colors">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Đến ngày</label>
          <input type="date" name="to" class="w-full px-4 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-2 outline-none focus:border-emerald-500 text-sm font-medium transition-colors">
        </div>
      </div>
      
      <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 text-blue-700 dark:text-blue-500 px-4 py-3 rounded-xl text-sm mb-6 flex items-start gap-2">
        <i class="bi bi-info-circle-fill mt-0.5"></i>
        <span>Để trống nếu muốn xuất toàn bộ dữ liệu.</span>
      </div>

      <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 text-white font-extrabold text-sm hover:shadow-[0_8px_24px_rgba(16,185,129,0.4)] hover:-translate-y-0.5 transition-all border-0 cursor-pointer mb-2">
        <i class="bi bi-download mr-2"></i>Tải file CSV
      </button>
      <button type="button" class="w-full py-3 rounded-xl bg-gray-100 dark:bg-dark-2 text-gray-500 dark:text-gray-400 font-bold text-sm hover:bg-gray-200 dark:hover:bg-dark-border transition-colors border-0 cursor-pointer" onclick="closeExportModal()">
        Hủy bỏ
      </button>
    </form>
  </div>
</div>

<script>
// Modal Logic
function openModal(id) {
  const modal = document.getElementById(id);
  const box = modal.querySelector('div.transform');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  // Trigger reflow
  void modal.offsetWidth;
  modal.classList.remove('opacity-0');
  box.classList.remove('scale-95', 'opacity-0');
}

function closeModal(id) {
  const modal = document.getElementById(id);
  const box = modal.querySelector('div.transform');
  modal.classList.add('opacity-0');
  box.classList.add('scale-95', 'opacity-0');
  setTimeout(() => {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }, 300);
}

// Export
function openExportModal(type) {
  document.getElementById('exportType').value = type;
  openModal('exportModal');
}
function closeExportModal() {
  closeModal('exportModal');
}
document.getElementById('exportModal').addEventListener('click', function(e) {
  if (e.target === this) closeExportModal();
});

// Strike
function openStrikeModal(userId, userName, currentStrikes) {
  document.getElementById('strikeUserId').value = userId;
  document.getElementById('strikeSubtitle').textContent = `Xử phạt: ${userName}`;
  document.getElementById('strikeReason').value = '';
  document.getElementById('strikeEvidence').value = '';
  
  const statusSpan = document.getElementById('strikeCurrentStatus');
  const nextStrike = currentStrikes + 1;
  if (nextStrike === 1) {
    statusSpan.innerHTML = `<strong>Gậy 1:</strong> Người dùng sẽ nhận được Email cảnh cáo nghiêm trọng.`;
  } else if (nextStrike === 2) {
    statusSpan.innerHTML = `<strong>Gậy 2:</strong> Người dùng sẽ bị <strong class="text-red-500">Khóa 7 Ngày</strong>.`;
  } else {
    statusSpan.innerHTML = `<strong>Gậy 3+:</strong> Người dùng sẽ bị <strong class="text-red-500">Khóa Vĩnh Viễn</strong>.`;
  }
  
  openModal('strikeModal');
}
function closeStrikeModal() {
  closeModal('strikeModal');
}
document.getElementById('strikeModal').addEventListener('click', function(e) {
  if (e.target === this) closeStrikeModal();
});
document.getElementById('strikeForm').addEventListener('submit', function(e) {
  const reason = document.getElementById('strikeReason').value.trim();
  if (!reason) {
    e.preventDefault();
    document.getElementById('strikeReason').focus();
    alert('Vui lòng nhập lý do vi phạm!');
    return false;
  }
  return confirm(`Chắc chắn muốn tặng Gậy cho người dùng này? Quyết định sẽ được ghi log và báo qua Email.`);
});
</script>
