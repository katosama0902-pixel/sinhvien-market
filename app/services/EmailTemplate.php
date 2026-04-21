<?php

namespace App\Services;

/**
 * EmailTemplate — Cung cấp các template HTML email đẹp, chuẩn responsive
 *
 * Tất cả style đều là INLINE CSS vì Gmail/Outlook không hỗ trợ <style> block.
 * Màu chủ đạo: #6366f1 (indigo)
 */
class EmailTemplate
{
    // ─── Wrapper chung ────────────────────────────────────────────────────────

    private static function wrap(string $content, string $preheader = ''): string
    {
        $appName = $_ENV['APP_NAME'] ?? 'SinhVienMarket';
        $appUrl  = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
        $year    = date('Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$appName}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:'Segoe UI',Arial,sans-serif;">

<!-- Preheader ẩn (hiện trong preview email) -->
<span style="display:none;max-height:0;overflow:hidden;mso-hide:all;">{$preheader}&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;</span>

<!-- Wrapper -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f6fb;padding:32px 16px;">
  <tr>
    <td align="center">
      <!-- Card -->
      <table width="560" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;width:100%;">

        <!-- Header -->
        <tr>
          <td style="background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);border-radius:16px 16px 0 0;padding:32px 40px;text-align:center;">
            <a href="{$appUrl}" style="text-decoration:none;">
              <div style="display:inline-flex;align-items:center;gap:10px;">
                <div style="width:40px;height:40px;background:rgba(255,255,255,0.2);border-radius:10px;display:inline-block;line-height:40px;text-align:center;font-size:20px;">🛒</div>
                <span style="color:#ffffff;font-size:22px;font-weight:800;letter-spacing:-0.5px;">{$appName}</span>
              </div>
            </a>
          </td>
        </tr>

        <!-- Content body -->
        <tr>
          <td style="background:#ffffff;padding:40px 40px 32px;border-left:1px solid #e8eaf0;border-right:1px solid #e8eaf0;">
            {$content}
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f8f9fc;border:1px solid #e8eaf0;border-top:none;border-radius:0 0 16px 16px;padding:20px 40px;text-align:center;">
            <p style="margin:0 0 6px;color:#9ca3af;font-size:12px;">
              Email này được gửi tự động từ <strong>{$appName}</strong>. Vui lòng không trả lời email này.
            </p>
            <p style="margin:0;color:#9ca3af;font-size:12px;">
              © {$year} {$appName} — Nền tảng mua bán sinh viên 🎓
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
HTML;
    }

    /** Nút CTA chính */
    private static function btn(string $url, string $label, string $color = '#6366f1'): string
    {
        return <<<HTML
<div style="text-align:center;margin:28px 0 8px;">
  <a href="{$url}" style="display:inline-block;background:{$color};color:#ffffff;font-weight:700;font-size:15px;padding:14px 36px;border-radius:10px;text-decoration:none;letter-spacing:0.2px;">
    {$label}
  </a>
</div>
HTML;
    }

    /** Block hiển thị mã OTP nổi bật */
    private static function otpBox(string $otp): string
    {
        return <<<HTML
<div style="background:#f0f0ff;border:2px dashed #6366f1;border-radius:12px;padding:20px;text-align:center;margin:24px 0;">
  <p style="margin:0 0 6px;color:#6366f1;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;">Mã xác minh của bạn</p>
  <p style="margin:0;color:#1e1b4b;font-size:40px;font-weight:900;letter-spacing:10px;font-family:'Courier New',monospace;">{$otp}</p>
</div>
HTML;
    }

    /** Tiêu đề section */
    private static function h1(string $icon, string $text): string
    {
        return "<h1 style='margin:0 0 16px;font-size:22px;font-weight:800;color:#1e1b4b;'>{$icon} {$text}</h1>";
    }

    /** Đoạn văn bản thường */
    private static function p(string $html): string
    {
        return "<p style='margin:0 0 12px;color:#374151;font-size:15px;line-height:1.6;'>{$html}</p>";
    }

    // ─── Public Templates ─────────────────────────────────────────────────────

    /**
     * OTP xác minh tài khoản (đăng ký mới / resend)
     */
    public static function otpVerify(string $name, string $otp, int $minutes = 15): string
    {
        $content = self::h1('✉️', 'Xác minh tài khoản của bạn')
            . self::p("Xin chào <strong>{$name}</strong>,")
            . self::p("Cảm ơn bạn đã đăng ký tài khoản trên <strong>SinhVienMarket</strong>! Để hoàn tất quá trình đăng ký, hãy nhập mã OTP sau vào ứng dụng:")
            . self::otpBox($otp)
            . self::p("⏰ Mã có hiệu lực trong <strong>{$minutes} phút</strong>. Vui lòng không chia sẻ mã này với bất kỳ ai.")
            . self::p("<span style='color:#9ca3af;font-size:13px;'>Nếu bạn không thực hiện yêu cầu này, hãy bỏ qua email này.</span>");

        return self::wrap($content, "Mã OTP của bạn là: {$otp} — Có hiệu lực {$minutes} phút");
    }

    /**
     * OTP đăng nhập 2FA
     */
    public static function otpLogin(string $name, string $otp): string
    {
        $content = self::h1('🔐', 'Xác minh đăng nhập (2FA)')
            . self::p("Xin chào <strong>{$name}</strong>,")
            . self::p("Chúng tôi nhận được yêu cầu đăng nhập vào tài khoản của bạn. Nhập mã bên dưới để xác minh đây là bạn:")
            . self::otpBox($otp)
            . self::p("⏰ Mã có hiệu lực trong <strong>10 phút</strong>.")
            . self::p("<span style='color:#dc2626;font-size:13px;'>⚠️ Nếu bạn không thực hiện yêu cầu đăng nhập này, hãy đổi mật khẩu ngay lập tức vì tài khoản của bạn có thể đang bị truy cập trái phép.</span>");

        return self::wrap($content, "Mã đăng nhập 2FA: {$otp}");
    }

    /**
     * Khôi phục mật khẩu
     */
    public static function resetPassword(string $name, string $resetLink): string
    {
        $content = self::h1('🔑', 'Đặt lại mật khẩu')
            . self::p("Xin chào <strong>{$name}</strong>,")
            . self::p("Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản gắn với email này. Nhấn nút bên dưới để tạo mật khẩu mới:")
            . self::btn($resetLink, '🔑 Đặt lại mật khẩu ngay')
            . self::p("⏰ Link có hiệu lực trong <strong>15 phút</strong>. Sau đó bạn cần yêu cầu lại.")
            . "<div style='background:#fff7ed;border-left:4px solid #f59e0b;border-radius:6px;padding:12px 16px;margin:16px 0;'>"
            . "<p style='margin:0;color:#92400e;font-size:13px;'>Nếu bạn <strong>không</strong> yêu cầu đặt lại mật khẩu, hãy bỏ qua email này. Mật khẩu của bạn sẽ không bị thay đổi.</p>"
            . "</div>";

        return self::wrap($content, 'Yêu cầu đặt lại mật khẩu SinhVienMarket');
    }

    /**
     * Cảnh báo đăng nhập từ thiết bị / IP lạ
     */
    public static function newDeviceLogin(string $name, string $ip, string $time, string $device): string
    {
        $appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
        $changePassLink = $appUrl . '/profile/edit';

        $content = self::h1('🚨', 'Phát hiện đăng nhập từ thiết bị lạ')
            . self::p("Xin chào <strong>{$name}</strong>,")
            . self::p("Chúng tôi phát hiện có người đăng nhập vào tài khoản của bạn từ một địa chỉ IP chưa được nhận dạng:")
            . "<div style='background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:16px 20px;margin:16px 0;'>"
            . "<table cellpadding='0' cellspacing='0' border='0' width='100%'>"
            . "<tr><td style='color:#6b7280;font-size:13px;padding:4px 0;width:100px;'>📍 Địa chỉ IP</td><td style='color:#111827;font-weight:700;font-size:13px;'>{$ip}</td></tr>"
            . "<tr><td style='color:#6b7280;font-size:13px;padding:4px 0;'>⏱ Thời gian</td><td style='color:#111827;font-weight:700;font-size:13px;'>{$time}</td></tr>"
            . "<tr><td style='color:#6b7280;font-size:13px;padding:4px 0;vertical-align:top;'>💻 Thiết bị</td><td style='color:#111827;font-size:12px;'>{$device}</td></tr>"
            . "</table>"
            . "</div>"
            . self::p("Nếu đây là <strong>bạn</strong>, hãy bỏ qua email này. Nếu <strong>không phải bạn</strong>, hãy đổi mật khẩu ngay:")
            . self::btn($changePassLink, '🔒 Đổi mật khẩu ngay', '#dc2626');

        return self::wrap($content, 'Cảnh báo bảo mật: Đăng nhập từ thiết bị lạ');
    }

    /**
     * Sản phẩm được Admin duyệt
     */
    public static function productApproved(string $name, string $productTitle, string $productUrl): string
    {
        $content = self::h1('✅', 'Bài đăng của bạn đã được duyệt!')
            . self::p("Xin chào <strong>{$name}</strong>,")
            . self::p("Tin vui! Sản phẩm của bạn đã được Admin <strong>SinhVienMarket</strong> phê duyệt thành công và hiện đang được hiển thị công khai trên nền tảng.")
            . "<div style='background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:16px 20px;margin:16px 0;text-align:center;'>"
            . "<p style='margin:0;font-size:16px;font-weight:700;color:#166534;'>📦 " . htmlspecialchars($productTitle, ENT_QUOTES) . "</p>"
            . "</div>"
            . self::btn($productUrl, '👁️ Xem sản phẩm của tôi', '#16a34a')
            . self::p("Chúc bạn bán được nhiều hàng! 🎉");

        return self::wrap($content, "Sản phẩm \"{$productTitle}\" đã được duyệt");
    }

    /**
     * Sản phẩm bị Admin từ chối
     */
    public static function productRejected(string $name, string $productTitle, string $reason, string $myProductsUrl): string
    {
        $reasonHtml = $reason
            ? "<div style='background:#fff7ed;border-left:4px solid #f59e0b;border-radius:6px;padding:12px 16px;margin:12px 0;'><p style='margin:0;color:#92400e;font-size:14px;'><strong>Lý do:</strong> " . htmlspecialchars($reason, ENT_QUOTES) . "</p></div>"
            : '';

        $content = self::h1('❌', 'Bài đăng của bạn bị từ chối')
            . self::p("Xin chào <strong>{$name}</strong>,")
            . self::p("Rất tiếc, bài đăng sản phẩm <strong>\"" . htmlspecialchars($productTitle, ENT_QUOTES) . "\"</strong> của bạn chưa đáp ứng được yêu cầu và đã bị Admin từ chối.")
            . $reasonHtml
            . self::p("📝 Bạn có thể chỉnh sửa lại nội dung và đăng lại sản phẩm. Hãy đảm bảo sản phẩm tuân thủ <strong>Quy định đăng bài</strong> của SinhVienMarket.")
            . self::btn($myProductsUrl, '✏️ Chỉnh sửa và đăng lại', '#f59e0b');

        return self::wrap($content, "Sản phẩm \"{$productTitle}\" bị từ chối");
    }

    /**
     * Thông báo sản phẩm đã có người mua (gửi cho seller)
     */
    public static function itemSold(string $sellerName, string $productTitle, string $buyerName, int $price, string $historyUrl): string
    {
        $priceFormatted = number_format($price, 0, ',', '.') . 'đ';

        $content = self::h1('🎉', 'Sản phẩm của bạn đã được mua!')
            . self::p("Xin chào <strong>{$sellerName}</strong>,")
            . self::p("Tuyệt vời! Sản phẩm của bạn vừa có người mua. Chi tiết đơn hàng:")
            . "<div style='background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:16px 20px;margin:16px 0;'>"
            . "<table cellpadding='0' cellspacing='0' border='0' width='100%'>"
            . "<tr><td style='color:#6b7280;font-size:13px;padding:4px 0;width:100px;'>📦 Sản phẩm</td><td style='color:#111827;font-weight:700;font-size:14px;'>" . htmlspecialchars($productTitle, ENT_QUOTES) . "</td></tr>"
            . "<tr><td style='color:#6b7280;font-size:13px;padding:4px 0;'>👤 Người mua</td><td style='color:#111827;font-size:14px;'><strong>{$buyerName}</strong></td></tr>"
            . "<tr><td style='color:#6b7280;font-size:13px;padding:4px 0;'>💰 Giá bán</td><td style='color:#16a34a;font-weight:800;font-size:16px;'>{$priceFormatted}</td></tr>"
            . "</table>"
            . "</div>"
            . self::p("Hãy chuẩn bị hàng và xác nhận gửi hàng trong trang Lịch sử giao dịch.")
            . self::btn($historyUrl, '📦 Xem đơn hàng của tôi', '#6366f1');

        return self::wrap($content, "Sản phẩm \"{$productTitle}\" đã được mua");
    }

    /**
     * Sản phẩm wishlist giảm giá
     */
    public static function wishlistDrop(string $name, string $productTitle, int $oldPrice, int $newPrice, int $dropPct, string $productUrl): string
    {
        $old = number_format($oldPrice, 0, ',', '.') . 'đ';
        $new = number_format($newPrice, 0, ',', '.') . 'đ';

        $content = self::h1('📉', "Sản phẩm yêu thích giảm giá {$dropPct}%!")
            . self::p("Xin chào <strong>{$name}</strong>,")
            . self::p('Một sản phẩm trong danh sách yêu thích của bạn vừa giảm giá hấp dẫn:')
            . "<div style='background:#fefce8;border:1px solid #fde047;border-radius:10px;padding:16px 20px;margin:16px 0;text-align:center;'>"
            . "<p style='margin:0 0 8px;font-size:15px;font-weight:700;color:#713f12;'>\"" . htmlspecialchars($productTitle, ENT_QUOTES) . "\"</p>"
            . "<p style='margin:0;font-size:14px;color:#6b7280;'><del>{$old}</del> &nbsp;→&nbsp; <strong style='color:#dc2626;font-size:18px;'>{$new}</strong></p>"
            . "<p style='margin:6px 0 0;font-size:12px;background:#dc2626;color:#fff;display:inline-block;padding:3px 10px;border-radius:999px;'>Giảm {$dropPct}%</p>"
            . "</div>"
            . self::btn($productUrl, '🛒 Mua ngay trước khi hết!', '#dc2626');

        return self::wrap($content, "Sản phẩm yêu thích giảm {$dropPct}% — {$productTitle}");
    }
}
