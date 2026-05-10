<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use Core\Flash;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\AuditLog;
use App\Models\Report;
use App\Models\Rating;
use App\Models\Banner;
use App\Models\Giveaway;
use App\Services\NotificationService;

class AdminReportController extends Controller
{
    private User $userModel;
    private Product $productModel;
    private Category $categoryModel;
    private Transaction $txModel;
    private AuditLog $auditModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->txModel = new Transaction();
        $this->auditModel = new AuditLog();
    }

    // ─── Dashboard ───────────────────────────────────────────────────────

    public function reports(): void
    {
        Middleware::requireAdmin();
        $fromDate = $_GET['from'] ?? date('Y-m-01');
        $toDate = $_GET['to'] ?? date('Y-m-d');

        if (strtotime($fromDate) > strtotime($toDate)) {
            Flash::set('danger', 'Lỗi: "Từ ngày" không được lớn hơn "Đến ngày"!');
            $fromDate = $toDate;
        }

        $allTx = $this->txModel->getAll($fromDate, $toDate);
        
        // Chỉ lấy các giao dịch đã hoàn thành
        $transactions = array_filter($allTx, function($t) {
            return in_array($t['order_status'] ?? '', ['delivered', 'received', 'completed']);
        });

        $totalAmount = array_reduce($transactions, fn($sum, $t) => $sum + $t['amount'], 0);

        $this->render('admin/reports', [
            'title' => 'Báo cáo giao dịch',
            'transactions' => $transactions,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'totalAmount' => $totalAmount,
        ], 'admin');
    }

    // ─── Audit Log ───────────────────────────────────────────────────────

    public function ratings(): void
    {
        Middleware::requireAdmin();
        $ratingModel = new \App\Models\Rating();
        $ratings = $ratingModel->getAllForAdmin();
        
        $this->render('admin/ratings', [
            'title'   => 'Kiểm duyệt Đánh giá',
            'ratings' => $ratings,
            'csrf'    => $this->csrfToken(), // BUG-012 fix: truyền token vào view
        ], 'admin');
    }

    public function toggleRating(): void
    {
        Middleware::requireAdmin();
        // BUG-012 fix: thêm CSRF protection
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Phiên làm việc hết hạn.');
            $this->redirect('admin/ratings'); return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$this->input('id');
            $ratingModel = new \App\Models\Rating();
            $rating = $ratingModel->findById($id);
            
            if ($rating) {
                $ratingModel->toggleHide($id);
                
                // Gửi email cảnh cáo nếu bị ẩn
                if ($rating['status'] === 'visible') {
                    $userModel = new \App\Models\User();
                    $rater = $userModel->findById($rating['rater_id']);
                    if ($rater) {
                        $reason = 'Bình luận đánh giá của bạn (ID: #'.$rating['id'].') chứa ngôn từ vi phạm tiêu chuẩn cộng đồng và đã bị quản trị viên ẩn khỏi hệ thống.';
                        NotificationService::sendEmail(
                            $rater['email'],
                            $rater['name'],
                            'Cảnh cáo Vi phạm Tiêu chuẩn Cộng đồng',
                            \App\Services\EmailTemplate::strikeWarning($rater['name'], 0, $reason)
                        );
                    }
                }

                \Core\Flash::set('success', 'Đã thay đổi trạng thái bình luận.');
            }
        }
        $this->redirect('admin/ratings'); // BUG-012 fix: bỏ dấu /
    }

    // ─── Quản lý Banner ──────────────────────────────────────────────────

    public function systemReports(): void
    {
        Middleware::requireAdmin();
        $reportModel = new \App\Models\Report();
        $status = $_GET['status'] ?? ''; // pending, resolved, ignored
        $reports = $reportModel->getAll((string)$status);
        
        $this->render('admin/system_reports', [
            'title'   => 'Quản lý Báo cáo Vi phạm',
            'reports' => $reports,
            'status'  => $status
        ], 'admin');
    }

    public function resolveReport(): void
    {
        Middleware::requireAdmin();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'CSRF không hợp lệ.');
            $this->redirect('admin/system-reports');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$this->input('id');
            $status = $this->input('status'); // resolved / ignored
            $adminNote = $this->input('admin_note');
            
            if ($id > 0 && in_array($status, ['resolved', 'ignored'])) {
                $evidenceUrl = null;
                
                // Upload ảnh bằng chứng (nếu có)
                if (!empty($_FILES['evidence']['name'])) {
                    $uploadPath = __DIR__ . '/../../public/uploads/evidence/';
                    if (!is_dir($uploadPath)) {
                        @mkdir($uploadPath, 0777, true);
                    }
                    
                    $ext = pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('evidence_') . '.' . $ext;
                    
                    if (move_uploaded_file($_FILES['evidence']['tmp_name'], $uploadPath . $filename)) {
                        $evidenceUrl = '/public/uploads/evidence/' . $filename;
                    }
                }

                $reportModel = new \App\Models\Report();
                $reportModel->updateStatus($id, $status, $adminNote, $evidenceUrl);
                Flash::set('success', 'Đã lưu kết quả xử lý báo cáo vi phạm.');
            }
        }
        $this->redirect('admin/system-reports');
    }
}
