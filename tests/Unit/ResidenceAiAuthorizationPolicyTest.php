<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ResidenceAiAuthorizationPolicyTest extends TestCase
{
    public function test_ai_endpoints_require_explicit_permissions_and_dashboard_permission(): void
    {
        $controller = file_get_contents(ROOT . '/app/controllers/ResidenceApiController.php');
        $migration = file_get_contents(ROOT . '/database/migrations/20261114_week13_14_local_intelligence.sql');

        self::assertStringContainsString("requirePermission('residence.ai.repair.classify')", $controller);
        self::assertStringContainsString("requirePermission('residence.ai.rules_assistant.use')", $controller);
        self::assertStringContainsString("requirePermission('residence.operations.dashboard.read')", $controller);
        self::assertStringContainsString("WHERE r.role_key IN ('dorm_manager', 'admin')", $migration);
        self::assertStringContainsString("'residence.ai.rules_assistant.use'", $migration);
    }

    public function test_rules_retrieval_scopes_resident_audience_without_loading_user_identity(): void
    {
        $model = file_get_contents(ROOT . '/app/models/ResidenceNotice.php');

        self::assertStringContainsString("SELECT n.id, n.title, n.body", $model);
        self::assertStringContainsString("n.audience = 'all' OR EXISTS", $model);
        self::assertStringContainsString("rc.user_id = ?", $model);
        self::assertStringContainsString("rc.status = 'active'", $model);
        self::assertStringNotContainsString('JOIN users', substr(
            $model,
            strpos($model, 'function activeRulesForAssistant'),
            strpos($model, 'public function list') - strpos($model, 'function activeRulesForAssistant')
        ));
    }
}
