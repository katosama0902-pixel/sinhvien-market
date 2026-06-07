<?php
/**
 * User Profile Edit View
 * Formats data for user editing their own profile
 */
use Core\Flash;
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$tab = $_GET['tab'] ?? 'info'; // 'info' hoặc 'security'
?>

<div class="container mx-auto px-4 py-8 max-w-5xl">
    <!-- Header -->
    <div class="mb-8">
        <a href="<?= $appUrl ?>/dashboard" class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-primary no-underline mb-2 transition-colors">
            <i class="bi bi-arrow-left mr-1.5"></i> Quay lại
        </a>
        <h2 class="text-2xl font-extrabold text-gray-800 dark:text-dark-text mb-1">Hồ sơ cá nhân</h2>
        <p class="text-gray-500 dark:text-gray-400 text-sm">Quản lý thông tin và cài đặt bảo mật cho tài khoản của bạn.</p>
    </div>

    <div class="flex flex-col md:flex-row gap-6">
        <!-- Sidebar Navigation -->
        <div class="md:w-1/4 flex-shrink-0">
            <div class="sticky top-24 bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border overflow-hidden">
                <a href="<?= $appUrl ?>/profile?tab=info" 
                   class="flex items-center gap-3 px-5 py-4 text-sm font-semibold border-b border-light-border dark:border-dark-border no-underline transition-colors
                          <?= $tab === 'info' ? 'bg-indigo-50/50 dark:bg-primary/10 text-primary border-l-4 border-l-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-2' ?>">
                    <i class="bi bi-person text-lg"></i>Thông tin cá nhân
                </a>
                <a href="<?= $appUrl ?>/profile?tab=security" 
                   class="flex items-center gap-3 px-5 py-4 text-sm font-semibold border-b border-light-border dark:border-dark-border no-underline transition-colors
                          <?= $tab === 'security' ? 'bg-indigo-50/50 dark:bg-primary/10 text-primary border-l-4 border-l-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-2' ?>">
                    <i class="bi bi-shield-lock text-lg"></i>Bảo mật & Tài khoản
                </a>
                <a href="<?= $appUrl ?>/profile?tab=bank" 
                   class="flex items-center gap-3 px-5 py-4 text-sm font-semibold no-underline transition-colors
                          <?= $tab === 'bank' ? 'bg-indigo-50/50 dark:bg-primary/10 text-primary border-l-4 border-l-primary' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-dark-2' ?>">
                    <i class="bi bi-bank text-lg"></i>Tài khoản ngân hàng
                </a>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="md:w-3/4">
            <?php if ($tab === 'info'): ?>
                <!-- Tab: Thông tin cá nhân -->
                <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border mb-6 overflow-hidden">
                    <div class="px-6 py-4 border-b border-light-border dark:border-dark-border flex items-center gap-3 bg-gray-50/50 dark:bg-dark-2/50">
                        <h5 class="font-extrabold text-gray-800 dark:text-dark-text m-0 text-base">Ảnh đại diện</h5>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-<?= $rank['color'] ?? 'secondary' ?> text-white shadow-sm">
                            <i class="bi bi-<?= $rank['icon'] ?? 'person' ?> mr-1"></i><?= $rank['name'] ?? 'Tân binh' ?>
                        </span>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                            <div class="relative flex-shrink-0">
                                <?php if (!empty($user['avatar'])): ?>
                                    <img src="<?= $appUrl ?>/users/avatar?id=<?= (int)($user['id'] ?? 0) ?>" alt="Avatar"
                                         class="w-28 h-28 rounded-full object-cover border-4 border-white shadow-md bg-white">
                                <?php else: ?>
                                    <div class="w-28 h-28 rounded-full bg-gradient-to-br from-primary to-purple-500 text-white flex items-center justify-center shadow-md text-4xl font-black border-4 border-white">
                                        <?= mb_strtoupper(mb_substr($user['name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-center sm:text-left flex-1">
                                <!-- Input file ẩn -->
                                <input type="file" id="avatarFileInput" class="hidden" accept="image/jpeg,image/png,image/webp">
                                <button type="button" onclick="document.getElementById('avatarFileInput').click()"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full border-2 border-primary text-primary font-bold text-sm hover:bg-primary hover:text-white transition-colors cursor-pointer bg-transparent">
                                    <i class="bi bi-scissors"></i>Chọn & Cắt ảnh
                                </button>
                                <p class="text-xs text-gray-500 mt-3 mb-0">Hỗ trợ JPG, PNG hoặc WEBP. Tối đa 10MB.<br>Tỷ lệ hiển thị tốt nhất là 1:1.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form thông tin -->
                <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border overflow-hidden">
                    <div class="px-6 py-4 border-b border-light-border dark:border-dark-border bg-gray-50/50 dark:bg-dark-2/50">
                        <h5 class="font-extrabold text-gray-800 dark:text-dark-text m-0 text-base">Chi tiết hồ sơ</h5>
                    </div>
                    <div class="p-6">
                        <form action="<?= $appUrl ?>/profile/update" method="POST">
                            <input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?>">

                            <h6 class="text-xs font-black text-primary uppercase tracking-widest mb-4">Thông tin cơ bản</h6>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Họ tên <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES) ?>" required
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                                    <input type="email" value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES) ?>" readonly disabled
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-gray-100 dark:bg-dark-bg text-gray-500 cursor-not-allowed">
                                    <p class="text-xs font-bold text-green-500 mt-1.5 mb-0"><i class="bi bi-check-circle-fill mr-1"></i>Đã được dùng để đăng nhập</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Số điện thoại</label>
                                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '', ENT_QUOTES) ?>"
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                                </div>
                            </div>

                            <hr class="border-light-border dark:border-dark-border my-6">

                            <h6 class="text-xs font-black text-primary uppercase tracking-widest mb-4">Thông tin giao dịch & Mạng xã hội</h6>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Trường / Khoa</label>
                                    <input type="text" name="university" placeholder="Vd: ĐH KHTN - Khoa CNTT" value="<?= htmlspecialchars($user['university'] ?? '', ENT_QUOTES) ?>"
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                                    <p class="text-[11px] text-gray-500 mt-1.5 mb-0">Tăng uy tín khi giao dịch</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Mã số sinh viên (Tùy chọn)</label>
                                    <input type="text" name="student_id" value="<?= htmlspecialchars($user['student_id'] ?? '', ENT_QUOTES) ?>"
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Ký túc xá / Địa chỉ giao nhận</label>
                                    <input type="text" name="dormitory_address" placeholder="Vd: KTX Khu B - Tòa BA1 - Phòng 204" value="<?= htmlspecialchars($user['dormitory_address'] ?? '', ENT_QUOTES) ?>"
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Liên kết Zalo / Facebook</label>
                                    <input type="text" name="social_contact" placeholder="Vd: zalo.me/0901234567" value="<?= htmlspecialchars($user['social_contact'] ?? '', ENT_QUOTES) ?>"
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Kết nối thường xuyên</label>
                                    <input type="text" name="available_time" placeholder="Vd: Sáng / Tối / Cuối tuần" value="<?= htmlspecialchars($user['available_time'] ?? '', ENT_QUOTES) ?>"
                                           class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Tiểu sử ngắn</label>
                                    <textarea name="bio" rows="3" placeholder="Giới thiệu đôi nét về bản thân hoặc các món đồ bạn thường mua bán..."
                                              class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all resize-none"><?= htmlspecialchars($user['bio'] ?? '', ENT_QUOTES) ?></textarea>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary text-white font-bold text-sm hover:brightness-110 transition-all shadow-md shadow-primary/20 border-0 cursor-pointer">
                                    <i class="bi bi-check-lg"></i>Lưu thay đổi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php elseif ($tab === 'security'): ?>
                <!-- Tab: Bảo mật -->
                <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border mb-6 overflow-hidden">
                    <div class="px-6 py-4 border-b border-light-border dark:border-dark-border bg-gray-50/50 dark:bg-dark-2/50">
                        <h5 class="font-extrabold text-gray-800 dark:text-dark-text m-0 text-base">Thay đổi mật khẩu</h5>
                    </div>
                    <div class="p-6">
                        <form action="<?= $appUrl ?>/profile/password" method="POST">
                            <input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?>">

                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Mật khẩu hiện tại</label>
                                <input type="password" name="old_password" required
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Mật khẩu mới</label>
                                <input type="password" name="new_password" required minlength="8"
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                                <p class="text-xs text-gray-500 mt-1.5 mb-0">Tối thiểu 8 ký tự.</p>
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Xác nhận mật khẩu mới</label>
                                <input type="password" name="confirm_password" required minlength="8"
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                            </div>

                            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-yellow-500 text-white font-bold text-sm hover:bg-yellow-600 transition-all shadow-md shadow-yellow-500/20 border-0 cursor-pointer">
                                <i class="bi bi-key"></i>Đổi mật khẩu
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-dark-2 rounded-2xl p-5 border border-light-border dark:border-dark-border">
                        <div class="flex gap-4">
                            <div class="text-3xl text-green-500 flex-shrink-0"><i class="bi bi-envelope-check"></i></div>
                            <div>
                                <h6 class="font-bold text-gray-800 dark:text-dark-text mb-1">Xác thực Email</h6>
                                <p class="text-xs text-gray-500 leading-relaxed mb-0">
                                    <?php if ($user['is_verified']): ?>
                                        Email liên kết với tài khoản này đã được xác thực an toàn.
                                    <?php else: ?>
                                        Email của bạn chưa được xác thực bằng mã OTP.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-dark-2 rounded-2xl p-5 border border-light-border dark:border-dark-border">
                        <div class="flex gap-4">
                            <div class="text-3xl text-cyan-500 flex-shrink-0"><i class="bi bi-shield-check"></i></div>
                            <div>
                                <h6 class="font-bold text-gray-800 dark:text-dark-text mb-1">Câu hỏi bảo mật</h6>
                                <div class="text-xs text-gray-500 leading-relaxed">
                                    <?php if (!empty($user['security_question'])): ?>
                                        Câu hỏi bảo mật đã được thiết lập. 
                                        <div class="mt-1.5 font-mono text-[11px] bg-white dark:bg-dark-card border border-light-border dark:border-dark-border px-2 py-1 rounded inline-block">"<?= htmlspecialchars($user['security_question']) ?>"</div>
                                    <?php else: ?>
                                        Bạn chưa cài đặt câu hỏi bảo mật.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lịch sử đăng nhập -->
                <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border overflow-hidden">
                    <div class="px-6 py-4 border-b border-light-border dark:border-dark-border bg-gray-50/50 dark:bg-dark-2/50 flex items-center justify-between">
                        <h5 class="font-extrabold text-gray-800 dark:text-dark-text m-0 text-base"><i class="bi bi-clock-history mr-2 text-primary"></i>Lịch sử đăng nhập</h5>
                        <span class="inline-flex items-center px-2 py-1 rounded bg-white dark:bg-dark-card text-gray-500 text-[10px] font-bold border border-light-border dark:border-dark-border">15 phiên gần nhất</span>
                    </div>
                    <div class="p-0 overflow-x-auto">
                        <?php if (empty($loginHistory)): ?>
                            <div class="p-6 text-center text-gray-500 text-sm">
                                <i class="bi bi-info-circle mr-1"></i>Chưa có dữ liệu đăng nhập nào.
                            </div>
                        <?php else: ?>
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-dark-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-light-border dark:border-dark-border">
                                        <th class="py-3 px-5 font-bold">Thời gian</th>
                                        <th class="py-3 px-5 font-bold">Địa chỉ IP</th>
                                        <th class="py-3 px-5 font-bold">Thiết bị</th>
                                        <th class="py-3 px-5 font-bold text-right">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-dark-border">
                                    <?php foreach ($loginHistory as $i => $log): ?>
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-dark-2/50 transition-colors <?= $i === 0 ? 'bg-indigo-50/30 dark:bg-primary/5' : '' ?>">
                                        <td class="py-3 px-5 text-sm text-gray-800 dark:text-dark-text whitespace-nowrap <?= $i === 0 ? 'font-bold' : '' ?>">
                                            <?= date('d/m/Y H:i', strtotime($log['logged_at'])) ?>
                                        </td>
                                        <td class="py-3 px-5">
                                            <code class="text-xs bg-gray-100 dark:bg-dark-2 text-primary px-2 py-0.5 rounded-md font-mono border border-gray-200 dark:border-gray-700">
                                                <?= htmlspecialchars($log['ip_address'], ENT_QUOTES) ?>
                                            </code>
                                        </td>
                                        <td class="py-3 px-5 text-xs text-gray-500 truncate max-w-[200px]" title="<?= htmlspecialchars($log['device_info'], ENT_QUOTES) ?>">
                                            <?= htmlspecialchars($log['device_info'], ENT_QUOTES) ?>
                                        </td>
                                        <td class="py-3 px-5 text-right whitespace-nowrap">
                                            <?php if ($i === 0): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 border border-green-200">🟢 Hiện tại</span>
                                            <?php elseif ($log['is_new_device']): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-yellow-100 text-yellow-700 border border-yellow-200">⚠️ Thiết bị lạ</span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 dark:bg-dark-2 dark:text-gray-400 border border-gray-200 dark:border-gray-700">✓ Bình thường</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($tab === 'bank'): ?>
                <!-- Tab: Tài khoản ngân hàng (VietQR) -->
                <div class="bg-white dark:bg-dark-card rounded-2xl border border-light-border dark:border-dark-border mb-6 overflow-hidden">
                    <div class="px-6 py-4 border-b border-light-border dark:border-dark-border bg-gray-50/50 dark:bg-dark-2/50">
                        <h5 class="font-extrabold text-gray-800 dark:text-dark-text m-0 text-base"><i class="bi bi-qr-code text-primary mr-2"></i>Cài đặt thanh toán VietQR</h5>
                        <p class="text-xs text-gray-500 mt-1 mb-0">Cung cấp thông tin tài khoản ngân hàng để người mua có thể thanh toán trực tiếp qua VietQR.</p>
                    </div>
                    <div class="p-6">
                        <form action="<?= $appUrl ?>/profile/bank" method="POST">
                            <input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?>">

                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Tên ngân hàng (VD: Vietcombank, Techcombank) <span class="text-red-500">*</span></label>
                                <input type="text" name="bank_name" value="<?= htmlspecialchars($bankAccount['bank_name'] ?? '', ENT_QUOTES) ?>" required
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Mã ngân hàng (BIN/Tên viết tắt dùng cho VietQR) <span class="text-red-500">*</span></label>
                                <input type="text" name="bank_code" value="<?= htmlspecialchars($bankAccount['bank_code'] ?? '', ENT_QUOTES) ?>" required placeholder="VD: VCB, TCB, MB, BIDV"
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                                <p class="text-xs text-gray-500 mt-1 mb-0">Hệ thống cần mã ngân hàng chính xác để tạo mã QR tự động. Ví dụ: Vietcombank là VCB, MBBank là MB.</p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Số tài khoản <span class="text-red-500">*</span></label>
                                <input type="text" name="account_no" value="<?= htmlspecialchars($bankAccount['account_no'] ?? '', ENT_QUOTES) ?>" required
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all">
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1.5">Tên chủ tài khoản <span class="text-red-500">*</span></label>
                                <input type="text" name="account_name" value="<?= htmlspecialchars($bankAccount['account_name'] ?? '', ENT_QUOTES) ?>" required placeholder="VIẾT HOA KHÔNG DẤU"
                                       class="w-full px-4 py-2.5 text-sm rounded-xl border-2 border-light-border dark:border-dark-border bg-white dark:bg-dark-2 outline-none focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all uppercase">
                                <p class="text-xs text-gray-500 mt-1 mb-0">Phải trùng khớp chính xác với tên hiển thị trên tài khoản ngân hàng của bạn.</p>
                            </div>

                            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary text-white font-bold text-sm hover:bg-primary-dark transition-all shadow-md shadow-primary/20 border-0 cursor-pointer">
                                <i class="bi bi-save"></i>Lưu thông tin ngân hàng
                            </button>
                        </form>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($tab === 'info'): ?>
<!-- Crop Modal — Vanilla JS (no Bootstrap dependency) -->
<div id="cropModal" class="fixed inset-0 z-[9999] bg-slate-900/70 backdrop-blur-sm hidden items-center justify-center opacity-0 transition-opacity duration-200">
  <div id="cropModalBox" class="bg-white dark:bg-dark-card rounded-2xl border-0 shadow-2xl w-full max-w-[680px] overflow-hidden transform scale-95 opacity-0 transition-all duration-300 m-4">
    <!-- Header -->
    <div class="border-b border-light-border dark:border-dark-border px-6 py-4 flex items-center justify-between">
      <h5 class="font-extrabold text-gray-800 dark:text-dark-text m-0 text-lg flex items-center gap-2">
        <i class="bi bi-crop text-primary"></i>Cắt ảnh đại diện
      </h5>
      <button type="button" onclick="closeCropModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 bg-transparent border-0 cursor-pointer text-2xl leading-none transition-colors">×</button>
    </div>
    <!-- Body -->
    <div class="p-6 bg-gray-50 dark:bg-dark-2">
      <div class="rounded-xl overflow-hidden bg-black/10 dark:bg-black/30 border-2 border-dashed border-gray-300 dark:border-gray-600" style="max-height:420px">
        <img id="cropImage" src="" alt="Crop" class="max-w-full block">
      </div>
      <p class="text-xs text-gray-500 text-center mt-3 mb-0 font-medium">
        <i class="bi bi-info-circle mr-1"></i>Kéo để di chuyển · Cuộn để zoom · Tỷ lệ 1:1 (ảnh tròn)
      </p>
    </div>
    <!-- Footer -->
    <div class="border-t border-light-border dark:border-dark-border px-6 py-4 flex justify-end gap-3">
      <button type="button" onclick="closeCropModal()"
              class="px-5 py-2.5 rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-dark-card text-gray-600 dark:text-gray-300 font-bold text-sm hover:bg-gray-50 dark:hover:bg-dark-2 transition-colors cursor-pointer">
        <i class="bi bi-x"></i> Hủy
      </button>
      <button type="button" class="px-5 py-2.5 rounded-xl bg-primary text-white font-bold text-sm hover:brightness-110 transition-colors shadow-md shadow-primary/20 border-0 cursor-pointer" id="btnConfirmCrop">
        <i class="bi bi-check2"></i> Xác nhận
      </button>
    </div>
  </div>
</div>

<!-- Form submit ẩn (nhận canvas blob từ Cropper) -->
<form id="avatarCropForm" action="<?= $appUrl ?>/profile/avatar" method="POST" enctype="multipart/form-data" class="hidden">
  <input type="hidden" name="_csrf" value="<?= $_SESSION['csrf_token'] ?>">
  <input type="file" name="avatar" id="avatarCroppedInput">
</form>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js"></script>

<script>
(function () {
  const fileInput    = document.getElementById('avatarFileInput');
  const cropImage    = document.getElementById('cropImage');
  const btnConfirm   = document.getElementById('btnConfirmCrop');
  const cropForm     = document.getElementById('avatarCropForm');
  const croppedInput = document.getElementById('avatarCroppedInput');
  const modal        = document.getElementById('cropModal');
  const modalBox     = document.getElementById('cropModalBox');

  if (!fileInput) return;

  let cropperInstance = null;

  // ── Open / Close helpers ────────────────────────────────────────────────
  window.openCropModal = function () {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    void modal.offsetWidth;
    modal.classList.remove('opacity-0');
    modalBox.classList.remove('scale-95', 'opacity-0');
    document.body.style.overflow = 'hidden';
    // Init Cropper after modal is visible
    setTimeout(() => {
      if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
      cropperInstance = new Cropper(cropImage, {
        aspectRatio: 1, viewMode: 1, autoCropArea: 0.85,
        responsive: true, background: false, guides: true,
        toggleDragModeOnDblclick: false,
      });
    }, 150);
  };

  window.closeCropModal = function () {
    modal.classList.add('opacity-0');
    modalBox.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      document.body.style.overflow = '';
      if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
      cropImage.src = '';
      btnConfirm.disabled = false;
      btnConfirm.innerHTML = '<i class="bi bi-check2 mr-1"></i>Xác nhận &amp; Đặt làm ảnh đại diện';
    }, 300);
  };

  // Close on backdrop click
  modal.addEventListener('click', function (e) {
    if (e.target === modal) closeCropModal();
  });

  // ── File input change ───────────────────────────────────────────────────
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
      openCropModal();
    };
    reader.readAsDataURL(file);
    this.value = '';
  });

  // ── Confirm crop ─────────────────────────────────────────────────────────
  btnConfirm.addEventListener('click', function () {
    if (!cropperInstance) return;
    this.disabled = true;
    this.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span> Đang xử lý...';

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
})();
</script>
<?php endif; ?>
