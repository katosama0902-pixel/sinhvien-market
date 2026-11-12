<?php

namespace App\Services;

/**
 * Private, deterministic repair-request triage. No network or external model calls.
 */
class LocalRepairClassifier
{
    private const CATEGORIES = [
        'plumbing' => ['nước', 'rò rỉ', 'ro ri', 'vòi', 'voi', 'ống', 'ong', 'thoát nước', 'toilet', 'bồn cầu', 'bon cau'],
        'electrical' => ['điện', 'dien', 'ổ cắm', 'o cam', 'đèn', 'den', 'mất điện', 'mat dien', 'chập', 'chap', 'dây điện', 'day dien'],
        'furniture' => ['giường', 'giuong', 'bàn', 'ban', 'ghế', 'ghe', 'tủ', 'tu', 'cửa', 'cua', 'khóa', 'khoa'],
        'cleaning' => ['vệ sinh', 've sinh', 'rác', 'rac', 'cống', 'cong', 'mùi', 'mui', 'côn trùng', 'con trung'],
    ];

    /**
     * @return array{category:string,confidence:float,recommendedPriority:string,reason:string,modelKey:string}
     */
    public function classify(string $text): array
    {
        $text = mb_strtolower(trim($text));
        $scores = [];
        foreach (self::CATEGORIES as $category => $keywords) {
            $scores[$category] = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $scores[$category]++;
                }
            }
        }
        arsort($scores);
        $category = (int)reset($scores) > 0 ? (string)key($scores) : 'other';
        $hits = (int)($scores[$category] ?? 0);
        $dangerTerms = ['khói', 'khoi', 'tia lửa', 'tia lua', 'cháy', 'chay', 'mùi gas', 'mui gas', 'rò điện', 'ro dien'];
        $urgent = false;
        foreach ($dangerTerms as $term) {
            if (str_contains($text, $term)) {
                $urgent = true;
                break;
            }
        }
        $priority = $urgent ? 'urgent' : ($hits >= 2 ? 'high' : 'normal');
        $reason = $urgent
            ? 'Có từ khóa cảnh báo an toàn; nhân viên cần xác minh ngay.'
            : ($hits > 0 ? 'Phân loại cục bộ dựa trên từ khóa; nhân viên cần xác nhận.' : 'Chưa nhận diện được nhóm cụ thể; nhân viên cần xem xét.');

        return [
            'category' => $category,
            'confidence' => $hits === 0 ? 0.25 : min(0.95, 0.55 + 0.1 * ($hits - 1)),
            'recommendedPriority' => $priority,
            'reason' => $reason,
            'modelKey' => 'local-keyword-triage-v1',
        ];
    }
}
