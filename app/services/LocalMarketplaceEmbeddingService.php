<?php

namespace App\Services;

/**
 * Deterministic local feature hashing, not a pretrained or semantic language model.
 * Provides an offline vector-search baseline with no network or model dependency.
 */
class LocalMarketplaceEmbeddingService
{
    public const MODEL_KEY = 'local-feature-hash-v1-256';
    private const DIMENSIONS = 256;

    /** @return list<float> */
    public function embed(string $text): array
    {
        $text = mb_strtolower($text, 'UTF-8');
        preg_match_all('/[\p{L}\p{N}]+/u', $text, $matches);
        $tokens = $matches[0] ?? [];
        if ($tokens === []) {
            return array_fill(0, self::DIMENSIONS, 0.0);
        }
        $features = $tokens;
        for ($i = 0, $length = count($tokens) - 1; $i < $length; $i++) {
            $features[] = $tokens[$i] . '_' . $tokens[$i + 1];
        }

        $vector = array_fill(0, self::DIMENSIONS, 0.0);
        foreach ($features as $feature) {
            $hash = hash('sha256', $feature);
            $index = hexdec(substr($hash, 0, 8)) % self::DIMENSIONS;
            $sign = hexdec(substr($hash, 8, 1)) % 2 === 0 ? 1.0 : -1.0;
            $vector[$index] += $sign;
        }
        $norm = sqrt(array_sum(array_map(static fn(float $value): float => $value * $value, $vector)));
        if ($norm > 0.0) {
            foreach ($vector as &$value) {
                $value /= $norm;
            }
            unset($value);
        }
        return $vector;
    }

    /** @param list<float> $left @param list<float> $right */
    public function cosine(array $left, array $right): float
    {
        if (count($left) !== count($right) || $left === []) {
            return 0.0;
        }
        $dot = 0.0;
        foreach ($left as $index => $value) {
            $dot += (float)$value * (float)$right[$index];
        }
        return max(0.0, min(1.0, $dot));
    }

    /**
     * @param list<int> $rankedIds
     * @param list<int> $relevantIds
     * @return array{precisionAt5:float, recallAt5:float, mrrAt5:float, ndcgAt5:float}
     */
    public function evaluate(array $rankedIds, array $relevantIds): array
    {
        $relevant = array_fill_keys(array_map('intval', $relevantIds), true);
        $top = array_slice(array_map('intval', $rankedIds), 0, 5);
        $hits = 0;
        $reciprocalRank = 0.0;
        $dcg = 0.0;
        foreach ($top as $rank => $id) {
            if (isset($relevant[$id])) {
                $hits++;
                if ($reciprocalRank === 0.0) {
                    $reciprocalRank = 1.0 / ($rank + 1);
                }
                $dcg += 1.0 / log($rank + 2, 2);
            }
        }
        $idealHits = min(5, count($relevant));
        $idealDcg = 0.0;
        for ($rank = 0; $rank < $idealHits; $rank++) {
            $idealDcg += 1.0 / log($rank + 2, 2);
        }
        return [
            'precisionAt5' => (float)($hits / 5),
            'recallAt5' => $relevant === [] ? 0.0 : $hits / count($relevant),
            'mrrAt5' => $reciprocalRank,
            'ndcgAt5' => $idealDcg > 0.0 ? $dcg / $idealDcg : 0.0,
        ];
    }
}
