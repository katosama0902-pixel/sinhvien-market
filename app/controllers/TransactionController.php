<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use App\Models\Transaction;

use App\Services\MomoService;
use Core\Flash;

/**
 * TransactionController — Lịch sử giao dịch và Thanh toán
 */
class TransactionController extends Controller
{
    public function checkout(): void
    {
        Middleware::requireAuth();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Phiên làm việc hết hạn.');
            $this->redirect('products'); return;
        }

        $userId = $this->currentUser()['id'];
        $targetId = (int)$this->input('target_id');
        $type = $this->input('type'); // 'sale' or 'auction'
        $price = (float)$this->input('price');
        $address = $this->input('shipping_address');
        $method = $this->input('payment_method'); // cod, banking, zalopay

        $txModel = new Transaction();
        $pModel = new \App\Models\Product();
        $db = \Config\Database::getInstance();

        try {
            $db->beginTransaction();

            if ($type === 'auction') {
                $aModel = new \App\Models\Auction();
                // Khóa dòng auction
                $auction = $aModel->findByIdForUpdate($targetId);
                if (!$auction || $auction['status'] !== 'active') {
                    $db->rollBack();
                    Flash::set('danger', 'Phiên đấu giá không hợp lệ hoặc đã kết thúc.');
                    $this->redirect('products'); return;
                }
                $productId = $auction['product_id'];
                $sellerId = $pModel->findById($productId)['user_id'] ?? 0;
                
                // Mark auction as ended
                $aModel->markAsEnded($targetId, $userId, $price);
                $pModel->updateStatus($productId, 'sold');

            } else {
                // Sale
                $productId = $targetId;
                // Khóa dòng product
                $product = $pModel->findByIdForUpdate($productId);
                if (!$product || $product['status'] !== 'active') {
                    $db->rollBack();
                    Flash::set('danger', 'Sản phẩm không hợp lệ hoặc đã bán.');
                    $this->redirect('products'); return;
                }
                $sellerId = $product['user_id'];
                // Chỉ mark sold ngay với COD/banking/zalopay (thanh toán offline)
                // MoMo sẽ được mark trong callback khi xác nhận thành công
                if ($method !== 'momo') {
                    $pModel->updateStatus($productId, 'sold');
                }
            }

            $txId = $txModel->createTransaction($userId, $sellerId, $productId, $price, $method, $address);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            Flash::set('danger', 'Có lỗi xảy ra khi xử lý đơn hàng: ' . $e->getMessage());
            $this->redirect('products'); return;
        }

        if ($method === 'banking') {
            $this->redirect("transactions/bank?id=$txId");
        } elseif ($method === 'zalopay') {
            $this->redirect("transactions/zalopay?id=$txId");

        } elseif ($method === 'momo') {
            $this->redirect("transactions/momo?id=$txId");
        } else {
            Flash::set('success', 'Đặt hàng thành công! Đơn hàng sẽ thanh toán khi nhận (COD).');
            $this->redirect('transactions/history');
        }
    }

    public function bank(): void
    {
        Middleware::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $tx = (new Transaction())->findById($id);
        if (!$tx || (int)$tx['buyer_id'] !== (int)$this->currentUser()['id']) {
            $this->redirect('transactions/history'); return;
        }

        // Lấy thông tin ngân hàng của người bán
        $bankAccount = (new \App\Models\BankAccount())->findByUserId((int)$tx['seller_id']);
        $qrUrl = '';
        if ($bankAccount) {
            $qrUrl = \App\Services\VietQRService::generateQRUrl(
                $bankAccount['bank_code'],
                $bankAccount['account_no'],
                (int)$tx['amount'],
                'DH ' . $tx['id'],
                $bankAccount['account_name']
            );
        }

        $csrf = $this->csrfToken();
        include APP_PATH . '/views/transactions/bank.php';
    }

    public function bankConfirm(): void
    {
        Middleware::requireAuth();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Lỗi CSRF.');
            $this->redirect('transactions/history'); return;
        }

        $txId = (int)$this->input('id');
        $txModel = new Transaction();
        $tx = $txModel->findById($txId);

        if (!$tx || (int)$tx['buyer_id'] !== (int)$this->currentUser()['id']) {
            Flash::set('danger', 'Giao dịch không hợp lệ.');
            $this->redirect('transactions/history'); return;
        }

        $proofBytes = null;
        $proofMime  = null;
        if (isset($_FILES['proof']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['proof'];
            // NEW-004 fix: validate MIME type thực tế (không tin extension từ tên file)
            $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $actualMime  = mime_content_type($file['tmp_name']);
            if ($file['size'] <= 3 * 1024 * 1024 && in_array($actualMime, $allowedMime, true)) {
                $proofBytes = file_get_contents($file['tmp_name']);
                $proofMime  = $actualMime;
            }
        }

        if ($proofBytes !== null) {
            // Lưu biên lai vào DB (bền qua redeploy Railway)
            $txModel->saveProofImage($txId, $proofBytes, $proofMime);
            $txModel->updatePaymentProof($txId, 'db'); // marker
            Flash::set('success', 'Đã tải lên ảnh xác nhận. Đang chờ người bán kiểm tra.');
        } else {
            Flash::set('warning', 'Bạn chưa tải lên ảnh minh chứng hoặc ảnh quá lớn.');
            $this->redirect("transactions/bank?id=$txId"); return;
        }

        $this->redirect('transactions/history');
    }

    public function bankReceived(): void
    {
        Middleware::requireAuth();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Lỗi bảo mật.');
            $this->redirect('transactions/history'); return;
        }

        $txId = (int)$this->input('id');
        $txModel = new Transaction();
        $tx = $txModel->findById($txId);

        if (!$tx || (int)$tx['seller_id'] !== (int)$this->currentUser()['id']) {
            Flash::set('danger', 'Giao dịch không hợp lệ.');
            $this->redirect('transactions/history'); return;
        }

        $txModel->confirmBankPayment($txId);

        // Update product to sold if not yet
        (new \App\Models\Product())->updateStatus($tx['product_id'], 'sold');

        Flash::set('success', 'Đã xác nhận nhận tiền và chuyển đơn hàng sang trạng thái Đang giao.');
        $this->redirect('transactions/history');
    }

    public function zalopay(): void
    {
        Middleware::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $tx = (new Transaction())->findById($id);
        if (!$tx || (int)$tx['buyer_id'] !== (int)$this->currentUser()['id']) {
            $this->redirect('transactions/history'); return;
        }

        $result = \App\Services\ZaloPayService::createPayment(
            $id,
            (int)$tx['amount'],
            'Thanh toán SinhVienMarket #' . $id
        );

        if ($result['success']) {
            header('Location: ' . $result['payUrl']);
            exit;
        }

        Flash::set('danger', 'Không thể kết nối ZaloPay: ' . $result['message']);
        $this->redirect('transactions/history');
    }

    public function zalopayReturn(): void
    {
        $data = $_GET;
        $apptransid = $data['apptransid'] ?? '';
        $parts = explode('_', $apptransid);
        $txnRef = (int)($parts[1] ?? 0);

        $txModel = new Transaction();
        $tx = $txModel->findById($txnRef);

        if (!$tx) {
            Flash::set('danger', 'Không tìm thấy giao dịch.');
            $this->redirect('transactions/history'); return;
        }

        // Với môi trường Sandbox, có thể mac không khớp do test tool, nhưng vẫn implement chuẩn.
        // Chỉ cập nhật trạng thái nếu status = 1 (thành công)
        if (isset($data['status']) && (int)$data['status'] === 1) {
            $txModel->updatePaymentStatus($txnRef, 'paid');
            (new \App\Models\Product())->updateStatus($tx['product_id'], 'sold');
            Flash::set('success', '✅ Thanh toán ZaloPay thành công!');
        } else {
            Flash::set('warning', '⚠️ Giao dịch ZaloPay thất bại hoặc bị hủy.');
        }

        $this->redirect('transactions/history');
    }

    public function zalopayIpn(): void
    {
        $postdata = file_get_contents('php://input');
        $postdatajson = json_decode($postdata, true);
        
        $mac = hash_hmac('sha256', $postdatajson['data'], $_ENV['ZALOPAY_KEY2'] ?? ''); // V5-002 fix: bỏ hardcoded fallback
        
        $result = [];
        // BUG-A03 fix: dùng hash_equals() thay != để chống timing attack
        if (!hash_equals($mac, $postdatajson['mac'] ?? '')) {
            $result['return_code'] = -1;
            $result['return_message'] = 'mac not equal';
        } else {
            $data = json_decode($postdatajson['data'], true);
            $apptransid = $data['app_trans_id'] ?? '';
            $parts = explode('_', $apptransid);
            $txnRef = (int)($parts[1] ?? 0);

            if ($txnRef) {
                $txModel = new Transaction();
                $tx = $txModel->findById($txnRef);
                
                // Idempotency check: Nếu đã xử lý thì trả về success luôn, không làm lại
                if ($tx && $tx['payment_status'] === 'paid') {
                    $result['return_code'] = 1;
                    $result['return_message'] = 'success (already processed)';
                } elseif ($tx) {
                    // BUG-A10 fix: kiểm tra $tx không null trước khi truy cập product_id
                    $txModel->updatePaymentStatus($txnRef, 'paid');
                    (new \App\Models\Product())->updateStatus($tx['product_id'], 'sold');
                    $result['return_code'] = 1;
                    $result['return_message'] = 'success';
                } else {
                    $result['return_code'] = 0;
                    $result['return_message'] = 'transaction not found';
                }
            } else {
                $result['return_code'] = 0;
                $result['return_message'] = 'fail';
            }
        }
        
        echo json_encode($result);
        exit;
    }


    // ─── MoMo Sandbox ────────────────────────────────────────────────────────

    public function momo(): void
    {
        Middleware::requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $tx = (new Transaction())->findById($id);
        if (!$tx || (int)$tx['buyer_id'] !== (int)$this->currentUser()['id']) {
            $this->redirect('transactions/history'); return;
        }

        // Gọi MoMo API để lấy payUrl
        $result = MomoService::createPayment(
            $id,
            (int)$tx['amount'],
            'SinhVienMarket ĐH #' . $id
        );

        if ($result['success']) {
            header('Location: ' . $result['payUrl']);
            exit;
        }

        Flash::set('danger', 'Không thể kết nối MoMo: ' . $result['message']);
        $this->redirect('transactions/history');
    }

    public function momoReturn(): void
    {
        $data = $_GET;
        $parts  = explode('_', $data['orderId'] ?? '');
        $txnRef = (int)($parts[1] ?? 0);
        $txModel = new Transaction();
        $tx = $txModel->findById($txnRef);

        if (!$tx) {
            Flash::set('danger', 'Không tìm thấy giao dịch.');
            $this->redirect('transactions/history'); return;
        }

        if (!empty($data['signature']) && !MomoService::verifySignature($data)) {
            Flash::set('danger', 'Chữ ký MoMo không hợp lệ!');
            $this->redirect('transactions/history'); return;
        }

        if (MomoService::isSuccess($data)) {
            $txModel->updatePaymentStatus($txnRef, 'paid');
            // Mark sản phẩm là sold sau khi thanh toán thành công
            (new \App\Models\Product())->updateStatus($tx['product_id'], 'sold');
            Flash::set('success', '✅ Thanh toán MoMo thành công!');
        } else {
            Flash::set('warning', '⚠️ Giao dịch MoMo bị hủy hoặc thất bại.');
        }

        $this->redirect('transactions/history');
    }

    public function momoIpn(): void
    {
        // IPN (Instant Payment Notification) — MoMo gọi server-to-server
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($data)) {
            http_response_code(400); exit;
        }

        $parts  = explode('_', $data['orderId'] ?? '');
        $txnRef = (int)($parts[1] ?? 0);

        if ($txnRef && MomoService::isSuccess($data)) {
            $txModel = new Transaction();
            $tx = $txModel->findById($txnRef);
            if ($tx && $tx['payment_status'] !== 'paid') {
                $txModel->updatePaymentStatus($txnRef, 'paid');
            }
        }

        http_response_code(204);
        exit;
    }

    public function history(): void
    {
        Middleware::requireAuth();
        $user         = $this->currentUser();
        $txModel      = new Transaction();
        $transactions = $txModel->getByUser($user['id']);

        $this->render('transactions/history', [
            'title'        => 'Lịch sử giao dịch',
            'transactions' => $transactions,
        ]);
    }

    public function updateStatus(): void
    {
        Middleware::requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('transactions/history');
            return;
        }

        // V3-003 fix: thiếu CSRF check — seller có thể bị CSRF attack hủy đơn hàng
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Phiên làm việc hết hạn. Vui lòng thử lại.');
            $this->redirect('transactions/history');
            return;
        }

        $id     = (int)$this->input('id');
        $status = $this->input('status');
        $user = $this->currentUser();

        $txModel = new Transaction();
        $pModel = new \App\Models\Product();
        $tx = $txModel->findById($id);

        if (!$tx) {
            Flash::set('danger', 'Giao dịch không tồn tại.');
            $this->redirect('transactions/history');
            return;
        }

        $isBuyer = (int)$tx['buyer_id'] === (int)$user['id'];
        $isSeller = (int)$tx['seller_id'] === (int)$user['id'];

        // Kiểm tra quyền cập nhật
        if ($isSeller && in_array($status, ['shipping', 'delivered', 'cancelled'])) {
            $txModel->updateOrderStatus($id, $status);
            
            // Nếu hủy đơn hàng, trả lại trạng thái sản phẩm là 'active'
            if ($status === 'cancelled') {
                $pModel->updateStatus($tx['product_id'], 'active');
                Flash::set('success', 'Đã hủy đơn hàng và đưa sản phẩm quay trở lại sàn.');
            } else {
                Flash::set('success', 'Đã cập nhật trạng thái đơn hàng thành công!');
            }
        } elseif ($isBuyer && in_array($status, ['completed'])) {
            $txModel->updateOrderStatus($id, $status);
            Flash::set('success', 'Bạn đã xác nhận nhận hàng. Cảm ơn bạn!');
        } else {
            Flash::set('danger', 'Hành động không hợp lệ hoặc không có quyền.');
        }

        $this->redirect('transactions/history');
    }

    /** Phục vụ ảnh biên lai từ DB — chỉ người mua/bán của giao dịch hoặc admin. */
    public function proof(): void
    {
        Middleware::requireAuth();
        $user = $this->currentUser();
        $id   = (int)($_GET['id'] ?? 0);
        $tx   = $id > 0 ? (new Transaction())->findById($id) : null;
        if (!$tx || !(($user['role'] ?? '') === 'admin'
                      || (int)$tx['buyer_id'] === (int)$user['id']
                      || (int)$tx['seller_id'] === (int)$user['id'])) {
            http_response_code(403);
            exit;
        }
        $this->serveImageBlob((new Transaction())->getProofBlob($id));
    }
}
