<?php

namespace App\Services;

use App\Models\ResidenceNotice;

/**
 * Retrieval-only assistant: answers strictly from published dorm rules.
 */
class LocalDormRulesAssistant
{
    public function __construct(private readonly ResidenceNotice $notices = new ResidenceNotice())
    {
    }

    /**
     * @return array{answer:string,sources:list<array{id:int,title:string}>,modelKey:string}
     */
    public function answer(string $question, ?int $userId): array
    {
        $normalized = mb_strtolower(trim($question));
        $matches = [];
        foreach ($this->notices->activeRulesForAssistant($userId) as $rule) {
            $text = mb_strtolower((string)$rule['title'] . ' ' . (string)$rule['body']);
            $queryWords = preg_split('/[^\p{L}\p{N}]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $score = 0;
            foreach ($queryWords as $word) {
                if (mb_strlen($word) > 2 && str_contains($text, $word)) {
                    $score++;
                }
            }
            if ($score > 0) {
                $matches[] = ['score' => $score, 'rule' => $rule];
            }
        }
        usort($matches, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);
        $sources = [];
        $answers = [];
        foreach (array_slice($matches, 0, 3) as $match) {
            $rule = $match['rule'];
            $sources[] = ['id' => (int)$rule['id'], 'title' => (string)$rule['title']];
            $answers[] = mb_substr(trim(strip_tags((string)$rule['body'])), 0, 700);
        }

        return [
            'answer' => $answers
                ? implode("\n\n", $answers)
                : 'Tôi chưa tìm thấy quy định phù hợp trong các nội quy đang được công bố. Vui lòng liên hệ Ban quản lý KTX để được xác nhận.',
            'sources' => $sources,
            'modelKey' => 'local-dorm-rules-retrieval-v1',
        ];
    }
}
