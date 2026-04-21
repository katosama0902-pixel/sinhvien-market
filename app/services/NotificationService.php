<?php

namespace App\Services;

use App\Models\Notification;
use Core\Mailer;
use App\Services\EmailTemplate;

/**
 * NotificationService — Dịch vụ tập trung gửi thông báo
 * Gửi cả In-App notification + Email cho mọi sự kiện quan trọng
 */
class NotificationService
{
    private static function notifModel(): Notification
    {
        return new Notification();
    }

    private static function baseUrl(): string
    {
        return rtrim($_ENV['APP_URL'] ?? 'http://localhost:8080/sinhvien-market', '/');
    }

    // ─── Sự kiện: Admin duyệt sản phẩm ──────────────────────────────────────

    public static function notifyProductApproved(int $userId, string $userEmail, string $userName, int $productId, string $productTitle): void
    {
        $link = self::baseUrl() . '/products/show?id=' . $productId;
        self::notifModel()->create(
            $userId,
            'product_approved',
            '✅ Bài đăng đã được duyệt',
            "Sản phẩm \"$productTitle\" đã được Admin phê duyệt và hiện đang được hiển thị công khai.",
            $link
        );

        // Gửi email
        $link = self::baseUrl() . '/products/show?id=' . $productId;
        Mailer::send(
            $userEmail,
            $userName,
            '✅ Bài đăng của bạn đã được duyệt — SinhVienMarket',
            EmailTemplate::productApproved($userName, $productTitle, $link)
        );
    }

    // ─── Sự kiện: Admin từ chối sản phẩm ────────────────────────────────────

    public static function notifyProductRejected(int $userId, string $userEmail, string $userName, int $productId, string $productTitle, string $reason = ''): void
    {
        $link = self::baseUrl() . '/products/my';
        self::notifModel()->create(
            $userId,
            'product_rejected',
            '❌ Bài đăng bị từ chối',
            "Sản phẩm \"$productTitle\" đã bị Admin từ chối." . ($reason ? " Lý do: $reason" : ''),
            $link
        );

        $link = self::baseUrl() . '/products/my';
        $reasonHtml = $reason ? "<p><strong>Lý do:</strong> $reason</p>" : '';
        Mailer::send(
            $userEmail,
            $userName,
            '❌ Bài đăng của bạn bị từ chối — SinhVienMarket',
            EmailTemplate::productRejected($userName, $productTitle, $reason, $link)
        );
    }

    // ─── Sự kiện: Sản phẩm được chốt đơn (người bán nhận thông báo) ──────────

    public static function notifyItemSold(int $sellerId, string $sellerEmail, string $sellerName, int $productId, string $productTitle, string $buyerName, int $finalPrice): void
    {
        $link = self::baseUrl() . '/transactions/history';
        self::notifModel()->create(
            $sellerId,
            'item_sold',
            '🎉 Sản phẩm của bạn đã được mua!',
            "\"$productTitle\" vừa được $buyerName mua với giá " . number_format($finalPrice, 0, ',', '.') . 'đ.',
            $link
        );

        $link = self::baseUrl() . '/transactions/history';
        Mailer::send(
            $sellerEmail,
            $sellerName,
            '🎉 Sản phẩm đã được mua — SinhVienMarket',
            EmailTemplate::itemSold($sellerName, $productTitle, $buyerName, $finalPrice, $link)
        );
    }

    // ─── Sự kiện: Giá sản phẩm yêu thích giảm mạnh ──────────────────────────

    public static function notifyWishlistDrop(int $userId, string $userEmail, string $userName, int $productId, string $productTitle, int $oldPrice, int $newPrice): void
    {
        $link = self::baseUrl() . '/products/show?id=' . $productId;
        $dropPct = $oldPrice > 0 ? round((1 - $newPrice / $oldPrice) * 100) : 0;

        self::notifModel()->create(
            $userId,
            'wishlist_drop',
            "📉 Sản phẩm yêu thích giảm giá {$dropPct}%!",
            "\"$productTitle\" đã giảm từ " . number_format($oldPrice, 0, ',', '.') . 'đ xuống ' . number_format($newPrice, 0, ',', '.') . 'đ.',
            $link
        );

        Mailer::send(
            $userEmail,
            $userName,
            "📉 Sản phẩm yêu thích giảm giá {$dropPct}% — SinhVienMarket",
            EmailTemplate::wishlistDrop($userName, $productTitle, $oldPrice, $newPrice, $dropPct, $link)
        );
    }

    // ─── Sự kiện: Có tin nhắn mới ────────────────────────────────────────────

    public static function notifyNewMessage(int $receiverId, string $senderName, int $convId, string $productTitle): void
    {
        $link = self::baseUrl() . '/chat/show?id=' . $convId;
        self::notifModel()->create(
            $receiverId,
            'new_message',
            "💬 Tin nhắn mới từ $senderName",
            "về sản phẩm \"$productTitle\"",
            $link
        );
        // Không gửi email cho tin nhắn (tránh spam), chỉ in-app notification
    }
}
