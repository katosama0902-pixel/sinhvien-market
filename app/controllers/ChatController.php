<?php

namespace App\Controllers;

use Core\Controller;
use Core\Middleware;
use Core\Flash;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Services\NotificationService;

/**
 * ChatController — Hệ thống nhắn tin giữa người mua và người bán
 */
class ChatController extends Controller
{
    private Conversation $convModel;
    private Message      $msgModel;

    public function __construct()
    {
        $this->convModel = new Conversation();
        $this->msgModel  = new Message();
    }

    // ─── Danh sách tất cả cuộc hội thoại ────────────────────────────────────

    public function index(): void
    {
        Middleware::requireAuth();
        $user = $this->currentUser();

        $conversations = $this->convModel->getByUser($user['id']);

        $this->render('chat/index', [
            'title'         => 'Tin nhắn',
            'conversations' => $conversations,
            'activeConvId'  => null,
            'messages'      => [],
            'activeConv'    => null,
        ]);
    }

    // ─── Xem và chat trong 1 cuộc hội thoại ─────────────────────────────────

    public function show(): void
    {
        Middleware::requireAuth();
        $user   = $this->currentUser();
        $convId = (int)($_GET['id'] ?? 0);

        if ($convId <= 0) {
            $this->redirect('chat');
        }

        $conv = $this->convModel->findByIdForUser($convId, $user['id']);
        if (!$conv) {
            Flash::set('danger', 'Không tìm thấy cuộc hội thoại.');
            $this->redirect('chat');
        }

        // Đánh dấu đã đọc
        $this->msgModel->markAsRead($convId, $user['id']);

        $messages      = $this->msgModel->getByConversation($convId);
        $conversations = $this->convModel->getByUser($user['id']);

        $this->render('chat/index', [
            'title'         => 'Tin nhắn — ' . $conv['product_title'],
            'conversations' => $conversations,
            'activeConvId'  => $convId,
            'messages'      => $messages,
            'activeConv'    => $conv,
        ]);
    }

    // ─── Bắt đầu cuộc hội thoại từ trang sản phẩm ───────────────────────────

    public function start(): void
    {
        Middleware::requireAuth();
        if (!$this->verifyCsrf()) {
            Flash::set('danger', 'Phiên hết hạn.');
            $this->redirect('products');
        }

        $user      = $this->currentUser();
        $productId = (int)($_POST['product_id'] ?? 0);

        if ($productId <= 0) {
            Flash::set('danger', 'Sản phẩm không hợp lệ.');
            $this->redirect('products');
        }

        $productModel = new Product();
        $product      = $productModel->findById($productId);

        if (!$product) {
            Flash::set('danger', 'Sản phẩm không tồn tại.');
            $this->redirect('products');
        }

        // Không thể chat với chính mình
        if ((int)$product['user_id'] === (int)$user['id']) {
            Flash::set('warning', 'Bạn không thể liên hệ với chính mình.');
            $this->redirect('products/show?id=' . $productId);
        }

        $sellerId = (int)$product['user_id'];
        $convId   = $this->convModel->findOrCreate($productId, $user['id'], $sellerId);

        $offerPrice = (int)($_POST['offer_price'] ?? 0);
        if ($offerPrice > 0) {
            $this->msgModel->sendOffer($convId, $user['id'], $offerPrice);
        }

        $this->redirect('chat/show?id=' . $convId);
    }

    // ─── API: Gửi tin nhắn (AJAX POST) ──────────────────────────────────────

    public function send(): void
    {
        Middleware::requireAuth();

        if (!$this->verifyCsrf()) {
            $this->json(['success' => false, 'message' => 'CSRF không hợp lệ.'], 403);
        }

        $user   = $this->currentUser();
        $convId = (int)($_POST['conversation_id'] ?? 0);
        $body   = trim($_POST['body'] ?? '');

        if ($convId <= 0 || $body === '') {
            $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], 400);
        }

        // Kiểm tra quyền truy cập cuộc hội thoại
        $conv = $this->convModel->findByIdForUser($convId, $user['id']);
        if (!$conv) {
            $this->json(['success' => false, 'message' => 'Không có quyền.'], 403);
        }

        // Giới hạn độ dài tin nhắn
        if (mb_strlen($body) > 1000) {
            $this->json(['success' => false, 'message' => 'Tin nhắn tối đa 1000 ký tự.'], 400);
        }

        $msgId = $this->msgModel->send($convId, $user['id'], $body);

        // Xác định người nhận để gửi thông báo
        $receiverId = ((int)$conv['buyer_id'] === (int)$user['id'])
            ? (int)$conv['seller_id']
            : (int)$conv['buyer_id'];

        NotificationService::notifyNewMessage(
            $receiverId,
            $user['name'],
            $convId,
            $conv['product_title']
        );

        // ─── Tích hợp AI Auto-Responder (Lazada/Shopee Style) ──────────────────────
        // Nếu người mua nhắn tin, Bot AI sẽ tự động phản hồi 1 lần duy nhất 
        // dựa trên các mốc thời gian (Cooldown)
        if ((int)$conv['buyer_id'] === (int)$user['id']) {
            $lastSellerTime = $this->msgModel->getLastMessageTimeBySender($convId, $conv['seller_id']);
            $lastBuyerTime  = $this->msgModel->getLastMessageTimeBySender($convId, $conv['buyer_id'], $msgId);
            
            // Điều kiện 1: Người bán (hoặc Bot) KHÔNG nhắn tin gì trong vòng 12 tiếng qua.
            // Nếu người bán vừa phản hồi, Bot sẽ im lặng nhường sân cho người bán.
            $sellerCooledDown = true;
            if ($lastSellerTime && (time() - strtotime($lastSellerTime)) < 12 * 3600) {
                $sellerCooledDown = false;
            }

            // Điều kiện 2: Chống spam gọi Bot nhiều lần.
            // Nếu người mua nhắn liên tục (các tin nhắn cách nhau dưới 5 phút), 
            // thì chỉ tin nhắn ĐẦU TIÊN mới kích hoạt Bot.
            $isNewSession = true;
            if ($lastBuyerTime && (time() - strtotime($lastBuyerTime)) < 5 * 60) {
                $isNewSession = false;
            }

            if ($sellerCooledDown && $isNewSession) {
                $productModel = new Product();
                $p = $productModel->findById($conv['product_id']);
                if ($p) {
                    $prompt = "Bạn là AI trợ lý bán hàng tự động của SinhVienMarket. Người dùng đang hỏi hoặc chào hỏi về món đồ cũ:\n";
                    $prompt .= "- Tên sản phẩm: {$p['title']}\n";
                    $prompt .= "- Giá: {$p['price']} VNĐ\n";
                    $prompt .= "- Tình trạng bề ngoài: {$p['condition']}\n";
                    $prompt .= "- Nơi giao dịch: " . ($p['seller_address'] ?? 'Khu vực ĐHQG TP.HCM') . "\n";
                    $prompt .= "\nKhách hàng vừa nhắn tin nhắn đầu tiên: \"$body\"\n";
                    $prompt .= "\nNhiệm vụ của bạn: Đóng vai người bán hàng, hãy trả lời thật ngắn gọn (dưới 40 chữ), thân thiện năng động chuẩn sinh viên, xưng hô 'mình' và 'cậu' hoặc 'bạn'. Có thể dựa vào nội dung khách nhắn để nương theo, nếu khách chỉ chào thì bạn cũng chào lại và giới thiệu tắt về sản phẩm.";
                    
                    $aiResponse = \App\Services\GoogleAiService::askGemini($prompt);
                    
                    if ($aiResponse) {
                        $aiText = "🤖 *[Hệ thống tự động]* " . $aiResponse . "\n_(Chủ shop đang vắng mặt, bạn cứ để lại lời nhắn nha)_";
                        $this->msgModel->send($convId, $conv['seller_id'], $aiText);
                    }
                }
            }
        }

        $this->json([
            'success' => true,
            'data'    => [
                'message_id' => $msgId,
                'body'       => htmlspecialchars($body, ENT_QUOTES, 'UTF-8'),
                'sender'     => $user['name'],
                'time'       => date('H:i'),
            ],
            'message' => '',
        ]);
    }

    public function sendOffer(): void
    {
        Middleware::requireAuth();
        if (!$this->verifyCsrf()) {
            $this->json(['success' => false, 'message' => 'CSRF không hợp lệ.'], 403);
        }
        $user   = $this->currentUser();
        $convId = (int)($_POST['conversation_id'] ?? 0);
        $price  = (int)($_POST['offer_price'] ?? 0);

        if ($convId <= 0 || $price <= 0) {
            $this->json(['success' => false, 'message' => 'Giá trả phải lớn hơn 0.'], 400);
        }
        $conv = $this->convModel->findByIdForUser($convId, $user['id']);
        if (!$conv) {
            $this->json(['success' => false, 'message' => 'Không có quyền.'], 403);
        }

        // Chỉ người mua mới có quyền gửi Trả giá.
        if ((int)$conv['buyer_id'] !== (int)$user['id']) {
            $this->json(['success' => false, 'message' => 'Chỉ người mua mới được gửi đề nghị.'], 400);
        }

        $msgId = $this->msgModel->sendOffer($convId, $user['id'], $price);
        $this->json(['success' => true, 'message' => 'Đã gửi đề nghị thành công.']);
    }

    public function respondOffer(): void
    {
        Middleware::requireAuth();
        if (!$this->verifyCsrf()) {
            $this->json(['success' => false, 'message' => 'CSRF không hợp lệ.'], 403);
        }
        $user   = $this->currentUser();
        $msgId  = (int)($_POST['message_id'] ?? 0);
        $status = $_POST['status'] ?? ''; // 'accepted' or 'rejected'
        $convId = (int)($_POST['conversation_id'] ?? 0);

        if (!in_array($status, ['accepted', 'rejected'])) {
            $this->json(['success' => false, 'message' => 'Logic lỗi.'], 400);
        }

        $conv = $this->convModel->findByIdForUser($convId, $user['id']);
        if (!$conv) {
            $this->json(['success' => false, 'message' => 'Không có quyền.'], 403);
        }
        if ((int)$conv['seller_id'] !== (int)$user['id']) {
            $this->json(['success' => false, 'message' => 'Chỉ người bán mới có quyền duyệt offer.'], 400);
        }

        $this->msgModel->updateOfferStatus($msgId, $status);
        
        $replyText = ($status === 'accepted') ? "✅ Mình đồng ý với mức giá đề nghị của bạn. Giao dịch nhé!" : "❌ Mình chưa thể bán với mức giá này nha. Bạn có thể trả thêm chút không?";
        $this->msgModel->send($convId, $user['id'], $replyText);

        $this->json(['success' => true]);
    }

    // ─── API: Polling tin nhắn mới ───────────────────────────────────────────

    public function apiPoll(): void
    {
        Middleware::requireAuth();

        $user    = $this->currentUser();
        $convId  = (int)($_GET['conv_id'] ?? 0);
        $afterId = (int)($_GET['after_id'] ?? 0);

        if ($convId <= 0) {
            $this->json(['messages' => []]);
        }

        // Kiểm tra quyền
        $conv = $this->convModel->findByIdForUser($convId, $user['id']);
        if (!$conv) {
            $this->json(['messages' => []], 403);
        }

        // Đánh dấu đã đọc
        $this->msgModel->markAsRead($convId, $user['id']);

        $newMessages = $this->msgModel->getAfter($convId, $afterId);

        $formatted = array_map(fn($m) => [
            'id'          => $m['id'],
            'body'        => htmlspecialchars($m['body'], ENT_QUOTES, 'UTF-8'),
            'msg_type'    => $m['msg_type'] ?? 'text',
            'offer_price' => $m['offer_price'] ?? null,
            'offer_status'=> $m['offer_status'] ?? null,
            'sender_name' => $m['sender_name'],
            'sender_id'   => $m['sender_id'],
            'time'        => date('H:i', strtotime($m['created_at'])),
            'is_me'       => (int)$m['sender_id'] === (int)$user['id'],
        ], $newMessages);

        $this->json([
            'success' => true,
            'data'    => ['messages' => $formatted],
            'message' => '',
        ]);
    }

    // ─── API: Đếm tin nhắn chưa đọc (badge trên navbar) ────────────────────

    public function apiUnreadCount(): void
    {
        if (!$this->isLoggedIn()) {
            $this->json([
                'success' => true,
                'data'    => ['count' => 0],
                'message' => ''
            ]);
        }
        $user  = $this->currentUser();
        $count = $this->convModel->countTotalUnread($user['id']);
        $this->json([
            'success' => true,
            'data'    => ['count' => $count],
            'message' => ''
        ]);
    }
}
