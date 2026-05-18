<?php
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$tx = $tx ?? [];
$priceFmt = number_format($tx['amount'], 0, ',', '.') . 'đ';
use Core\Flash;
?>
<?php ob_start(); ?>

<div class="container mx-auto px-4 py-12 max-w-2xl">
    <div class="bg-white dark:bg-dark-card rounded-3xl shadow-xl overflow-hidden border border-light-border dark:border-dark-border">
        <!-- Header -->
        <div class="bg-primary/10 dark:bg-primary/20 p-8 text-center border-b border-light-border dark:border-dark-border">
            <h2 class="text-3xl font-black text-primary mb-2">Thanh toán Chuyển khoản</h2>
            <p class="text-gray-600 dark:text-gray-400">Đơn hàng <strong class="text-gray-900 dark:text-white">#<?= $tx['id'] ?></strong></p>
            <div class="mt-4 inline-block bg-white dark:bg-dark-2 px-6 py-2 rounded-full shadow-sm">
                <span class="text-xs text-gray-500 uppercase tracking-widest font-bold">Số tiền cần thanh toán</span>
                <div class="text-2xl font-black text-danger"><?= $priceFmt ?></div>
            </div>
        </div>

        <div class="p-8">
            <?php if (empty($qrUrl)): ?>
                <div class="text-center p-8 bg-red-50 dark:bg-red-900/20 rounded-2xl border border-red-100 dark:border-red-800">
                    <i class="bi bi-exclamation-triangle text-5xl text-danger mb-4 block"></i>
                    <h3 class="text-lg font-bold text-danger mb-2">Người bán chưa cung cấp tài khoản ngân hàng</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Xin lỗi, người bán chưa thiết lập thông tin VietQR. Vui lòng liên hệ người bán qua tính năng Chat hoặc chọn phương thức thanh toán khác.</p>
                    <a href="<?= $appUrl ?>/transactions/history" class="inline-block px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-300 transition-colors">Quay lại Lịch sử</a>
                </div>
            <?php else: ?>
                <div class="flex flex-col md:flex-row gap-8 items-center">
                    <!-- QR Box -->
                    <div class="w-full md:w-1/2 flex flex-col items-center">
                        <div class="bg-white p-4 rounded-2xl shadow-md border-2 border-dashed border-primary/30 w-full max-w-[280px]">
                            <img src="<?= $qrUrl ?>" alt="VietQR" class="w-full h-auto rounded-lg">
                        </div>
                        <p class="text-sm text-gray-500 mt-4 text-center">
                            Sử dụng <strong>App Ngân hàng</strong> hoặc <strong>Ví điện tử</strong> để quét mã.
                        </p>
                    </div>

                    <!-- Info & Upload Form -->
                    <div class="w-full md:w-1/2">
                        <div class="bg-gray-50 dark:bg-dark-2 p-5 rounded-2xl border border-light-border dark:border-dark-border mb-6">
                            <h4 class="text-sm font-extrabold text-gray-800 dark:text-dark-text uppercase tracking-wider mb-3">Thông tin chuyển khoản</h4>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between border-b border-light-border dark:border-dark-border pb-2">
                                    <span class="text-gray-500">Người nhận:</span>
                                    <span class="font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($bankAccount['account_name'] ?? '') ?></span>
                                </div>
                                <div class="flex justify-between border-b border-light-border dark:border-dark-border pb-2">
                                    <span class="text-gray-500">Số tài khoản:</span>
                                    <span class="font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($bankAccount['account_no'] ?? '') ?></span>
                                </div>
                                <div class="flex justify-between border-b border-light-border dark:border-dark-border pb-2">
                                    <span class="text-gray-500">Ngân hàng:</span>
                                    <span class="font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($bankAccount['bank_name'] ?? '') ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Nội dung CK:</span>
                                    <span class="font-mono bg-yellow-100 text-yellow-800 px-2 rounded">DH <?= $tx['id'] ?></span>
                                </div>
                            </div>
                        </div>

                        <form action="<?= $appUrl ?>/transactions/bank-confirm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                            <input type="hidden" name="id" value="<?= $tx['id'] ?>">
                            
                            <div class="mb-5">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Tải lên biên lai / ảnh chụp màn hình <span class="text-danger">*</span></label>
                                <input type="file" name="proof" required accept="image/*"
                                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 border-2 border-dashed border-light-border dark:border-dark-border rounded-xl p-4 transition-all">
                                <p class="text-xs text-gray-400 mt-2">Định dạng JPG, PNG. Tối đa 3MB.</p>
                            </div>

                            <button type="submit" class="w-full flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-primary text-white font-bold text-base hover:bg-primary-dark transition-all shadow-lg shadow-primary/30 border-0 cursor-pointer">
                                <i class="bi bi-cloud-arrow-up"></i>Xác nhận đã chuyển tiền
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="mt-8 text-center">
                    <a href="<?= $appUrl ?>/transactions/history" class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-gray-800 dark:hover:text-white transition-colors no-underline">
                        <i class="bi bi-arrow-left mr-2"></i>Quay lại danh sách đơn hàng
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
include APP_PATH . '/views/layouts/main.php';
?>
