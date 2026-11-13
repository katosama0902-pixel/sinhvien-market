<?php

namespace Tests\Unit;

use App\Services\LocalMarketplaceEmbeddingService;
use PHPUnit\Framework\TestCase;

class LocalMarketplaceEmbeddingServiceTest extends TestCase
{
    public function test_embedding_is_deterministic_normalized_and_local(): void
    {
        $service = new LocalMarketplaceEmbeddingService();
        $first = $service->embed('Sách giáo trình lập trình PHP');
        $second = $service->embed('Sách giáo trình lập trình PHP');

        self::assertSame($first, $second);
        self::assertCount(256, $first);
        self::assertEqualsWithDelta(1.0, sqrt(array_sum(array_map(
            static fn(float $value): float => $value * $value,
            $first
        ))), 0.000001);
    }

    public function test_query_with_shared_terms_scores_above_unrelated_text(): void
    {
        $service = new LocalMarketplaceEmbeddingService();
        $query = $service->embed('sách giáo trình lập trình php');
        $related = $service->embed('giáo trình lập trình php cho sinh viên');
        $unrelated = $service->embed('xe đạp thể thao địa hình');

        self::assertGreaterThan(
            $service->cosine($query, $unrelated),
            $service->cosine($query, $related)
        );
    }

    public function test_evaluation_metrics_calculate_at_five_and_ignore_beyond_five(): void
    {
        $metrics = (new LocalMarketplaceEmbeddingService())->evaluate(
            [10, 20, 30, 40, 50, 60],
            [20, 60]
        );

        self::assertSame(0.2, $metrics['precisionAt5']);
        self::assertSame(0.5, $metrics['recallAt5']);
        self::assertSame(0.5, $metrics['mrrAt5']);
        self::assertEqualsWithDelta(0.3868528072, $metrics['ndcgAt5'], 0.000001);
    }

    public function test_empty_relevance_list_has_finite_zero_metrics(): void
    {
        $metrics = (new LocalMarketplaceEmbeddingService())->evaluate([1, 2], []);

        self::assertSame([
            'precisionAt5' => 0.0,
            'recallAt5' => 0.0,
            'mrrAt5' => 0.0,
            'ndcgAt5' => 0.0,
        ], $metrics);
    }
}
