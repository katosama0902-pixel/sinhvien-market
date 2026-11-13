<?php

namespace Tests\Unit;

use App\Services\LocalRepairClassifier;
use PHPUnit\Framework\TestCase;

class LocalRepairClassifierTest extends TestCase
{
    public function test_repair_classification_is_local_deterministic_and_does_not_change_priority(): void
    {
        $classifier = new LocalRepairClassifier();
        $first = $classifier->classify('Ổ cắm có tia lửa, cần kiểm tra điện');
        $second = $classifier->classify('Ổ cắm có tia lửa, cần kiểm tra điện');

        self::assertSame($first, $second);
        self::assertSame('electrical', $first['category']);
        self::assertSame('urgent', $first['recommendedPriority']);
        self::assertSame('local-keyword-triage-v1', $first['modelKey']);
    }

    public function test_unknown_request_is_not_overconfident(): void
    {
        $result = (new LocalRepairClassifier())->classify('Xin xem xét giúp tôi');

        self::assertSame('other', $result['category']);
        self::assertSame(0.25, $result['confidence']);
        self::assertSame('normal', $result['recommendedPriority']);
    }
}
