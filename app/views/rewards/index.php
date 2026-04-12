<?php
/**
 * View: Trung tâm Nhận xu & Gamification
 */
$appUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
$user   = $_SESSION['user'];
use Core\Controller;
$ctrl = new class extends Controller {};
$csrf = $ctrl->csrfToken();
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <h4 class="fw-bold mb-4 text-center">Trung Tâm Phần Thưởng Sinh Viên</h4>

            <!-- BẢNG ĐIỂM DANH 7 NGÀY (Shopee Style) -->
            <div class="card-sv p-4 mb-4" style="background:#fffaf0; border:1px solid #ffeeba; border-radius: 16px;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="fw-bold mb-1 text-warning"><i class="bi bi-calendar-check-fill me-2"></i>Chuỗi điểm danh nhận Xu</h5>
                        <div class="text-muted small">Bạn đang có: <strong class="text-dark"><?= number_format($coins) ?> xu</strong></div>
                    </div>
                    <div>
                        <?php if ($canCheckin): ?>
                            <form action="<?= $appUrl ?>/coins/checkin" method="POST" style="margin:0;">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES) ?>">
                                <button type="submit" class="btn btn-warning rounded-pill fw-bold px-4 shadow-sm text-dark btn-lg">
                                    Điểm danh ngay
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-secondary rounded-pill fw-bold px-4 disabled">
                                Đã điểm danh hôm nay
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center text-center mt-5 position-relative">
                    <div class="position-absolute w-100" style="height: 6px; background: #ffeeba; top: 22px; z-index: 0; border-radius: 3px;"></div>
                    <div class="position-absolute" style="height: 6px; background: #ffc107; top: 22px; z-index: 0; border-radius: 3px; width: <?= min(100, ($streak/7)*100) ?>%; transition: width 0.8s ease-out;"></div>
                    
                    <?php for($i=1; $i<=7; $i++): ?>
                        <?php 
                            $isPassed = $i <= $streak;
                            $isToday = ($canCheckin && $i == $streak + 1);
                            $isRewardDay = ($i == 7);
                            $coinVal = $isRewardDay ? 50 : 10;
                        ?>
                        <div class="position-relative" style="z-index: 1; flex: 1;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 shadow-sm"
                                 style="width:50px; height:50px; background: <?= $isPassed ? '#ffc107' : ($isToday ? '#fff' : '#f8f9fa') ?>; 
                                        border: <?= $isPassed || $isToday ? '4px' : '3px' ?> solid <?= $isPassed || $isToday ? '#ffc107' : '#dee2e6' ?>;
                                        color: <?= $isPassed ? '#fff' : ($isToday ? '#ffc107' : '#adb5bd') ?>;
                                        font-size: 1.5rem;">
                                <?php if ($isPassed): ?>
                                    <i class="bi bi-check-lg" style="-webkit-text-stroke: 1px;"></i>
                                <?php elseif ($isRewardDay): ?>
                                    <i class="bi bi-gift-fill"></i>
                                <?php else: ?>
                                    <i class="bi bi-coin"></i>
                                <?php endif; ?>
                            </div>
                            <?php if ($isPassed): ?>
                                <div class="small fw-bold text-success mt-1">Đã nhận</div>
                                <div class="small text-muted text-decoration-line-through opacity-75">+<?= $coinVal ?> xu</div>
                            <?php else: ?>
                                <div class="small fw-bold <?= $isToday ? 'text-dark' : 'text-muted' ?> mt-1">Ngày <?= $i ?></div>
                                <div class="small text-danger fw-bold">+<?= $coinVal ?> xu</div>
                            <?php endif; ?>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Khu vực dự phòng Minigame sau này -->
            <div class="text-center mt-5 text-muted">
                <i class="bi bi-controller fs-1 opacity-25"></i>
                <p class="mt-2 text-uppercase fw-bold opacity-50" style="letter-spacing: 2px;">Minigames sắp ra mắt</p>
                <p class="small opacity-50">Các trò chơi nhận xu hấp dẫn sẽ sớm xuất hiện tại đây.</p>
            </div>
            
        </div>
    </div>
</div>
