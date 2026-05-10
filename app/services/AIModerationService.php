<?php

namespace App\Services;

use App\Services\GoogleAiService;

/**
 * Dịch vụ kiểm duyệt nội dung bằng AI (Gemini)
 */
class AIModerationService
{
    /**
     * Kiểm duyệt tiêu đề và mô tả sản phẩm
     * Trả về mảng: ['status' => 'safe'|'suspicious'|'violation', 'reason' => '...']
     */
    public static function reviewProduct(string $title, string $description): array
    {
        $prompt = "Bạn là hệ thống kiểm duyệt nội dung cho sàn mua bán sinh viên ký túc xá.
Phân tích bài đăng sau và trả về JSON hợp lệ:
{
  \"status\": \"safe|suspicious|violation\",
  \"reason\": \"Lý do ngắn gọn bằng tiếng Việt (dưới 100 ký tự)\"
}

Tiêu đề: $title
Mô tả: $description

Tiêu chí VI PHẠM (status=violation):
- Hàng giả, hàng nhái, hàng cấm (vũ khí, chất kích thích, thuốc lá, vape...)
- Lừa đảo rõ ràng, gian lận, cờ bạc
- Ngôn từ thô tục, xúc phạm cá nhân
- Mua bán tài khoản game, account trái phép

Tiêu chí ĐÁNG NGỜ (status=suspicious):
- Giá bất thường (quá cao hoặc quá thấp so với loại hàng thông thường)
- Mô tả quá sơ sài, mơ hồ, thiếu thông tin cần thiết
- Không rõ xuất xứ, yêu cầu cọc tiền trước một cách đáng ngờ

Tiêu chí AN TOÀN (status=safe):
- Là hàng hóa thông thường của sinh viên (sách, điện tử, quần áo, đồ dùng, xe cộ, đồ ăn...) có thông tin rõ ràng.
";

        $response = GoogleAiService::askGemini($prompt);
        
        // Cố gắng parse JSON từ response
        // Đôi khi AI trả về markdown code block ```json ... ```
        $response = preg_replace('/```json|```/', '', $response);
        $data = json_decode(trim($response), true);

        if (json_last_error() === JSON_ERROR_NONE && isset($data['status'])) {
            $status = strtolower($data['status']);
            if (in_array($status, ['safe', 'suspicious', 'violation'])) {
                return [
                    'status' => $status,
                    'reason' => $data['reason'] ?? 'Không rõ lý do'
                ];
            }
        }

        // Fallback an toàn nếu AI không trả về đúng định dạng
        return [
            'status' => 'suspicious',
            'reason' => 'Hệ thống AI không thể xác định, cần Admin duyệt tay.'
        ];
    }
}
