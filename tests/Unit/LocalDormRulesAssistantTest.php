<?php

namespace Tests\Unit;

use App\Models\ResidenceNotice;
use App\Services\LocalDormRulesAssistant;
use PHPUnit\Framework\TestCase;

class LocalDormRulesAssistantTest extends TestCase
{
    public function test_answer_uses_only_retrieved_rules_and_never_echoes_other_row_fields(): void
    {
        $notices = $this->createMock(ResidenceNotice::class);
        $notices->expects(self::once())
            ->method('activeRulesForAssistant')
            ->with(17)
            ->willReturn([[
                'id' => 3,
                'title' => 'Giờ yên tĩnh',
                'body' => 'Giữ yên lặng sau 22 giờ.',
                'author_email' => 'private@example.test',
            ]]);
        $assistant = new LocalDormRulesAssistant($notices);

        $answer = $assistant->answer('Giờ yên tĩnh là khi nào?', 17);

        self::assertStringContainsString('Giữ yên lặng sau 22 giờ.', $answer['answer']);
        self::assertSame([['id' => 3, 'title' => 'Giờ yên tĩnh']], $answer['sources']);
        self::assertStringNotContainsString('private@example.test', $answer['answer']);
    }

    public function test_no_matching_rule_returns_handoff_without_claiming_an_answer(): void
    {
        $notices = $this->createMock(ResidenceNotice::class);
        $notices->method('activeRulesForAssistant')->willReturn([]);

        $answer = (new LocalDormRulesAssistant($notices))->answer('Không có nội dung liên quan?', null);

        self::assertSame([], $answer['sources']);
        self::assertStringContainsString('chưa tìm thấy', mb_strtolower($answer['answer']));
    }
}
