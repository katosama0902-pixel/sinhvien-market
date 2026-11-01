<?php

namespace Tests\Unit;

use Core\LegacyPageRedirects;
use PHPUnit\Framework\TestCase;

class LegacyPageRedirectsTest extends TestCase
{
    public function test_maps_legacy_page_routes_to_existing_next_routes(): void
    {
        self::assertSame('/', LegacyPageRedirects::targetPath(''));
        self::assertSame('/dashboard', LegacyPageRedirects::targetPath('dashboard'));
        self::assertSame('/leaderboard', LegacyPageRedirects::targetPath('leaderboard'));
        self::assertSame('/products/create', LegacyPageRedirects::targetPath('products/create'));
        self::assertSame('/my-listings', LegacyPageRedirects::targetPath('products/my'));
        self::assertSame('/transactions', LegacyPageRedirects::targetPath('transactions/history'));
        self::assertSame('/admin/giveaway_spin', LegacyPageRedirects::targetPath('admin/giveaway_spin'));
    }

    public function test_maps_legacy_query_id_to_next_dynamic_pages(): void
    {
        self::assertSame('/products/42', LegacyPageRedirects::targetPath('products/show', ['id' => '42']));
        self::assertSame('/chat/7', LegacyPageRedirects::targetPath('chat/show', ['id' => '7']));
        self::assertSame('/users/9', LegacyPageRedirects::targetPath('users/profile', ['id' => '9']));
        self::assertSame('/products', LegacyPageRedirects::targetPath('products/show'));
        self::assertSame('/chat', LegacyPageRedirects::targetPath('chat/show', ['id' => '../']));
    }

    public function test_unmigrated_backend_routes_are_not_redirected(): void
    {
        self::assertNull(LegacyPageRedirects::targetPath('api/marketplace/products'));
        self::assertNull(LegacyPageRedirects::targetPath('products/image'));
        self::assertNull(LegacyPageRedirects::targetPath('transactions/zalopay-return'));
        self::assertNull(LegacyPageRedirects::targetPath('logout'));
    }

    public function test_location_preserves_query_and_uses_configured_base_url(): void
    {
        self::assertSame(
            'https://frontend.example.test/products/42?sort=recent',
            LegacyPageRedirects::location(
                '/products/42',
                ['id' => '42', 'sort' => 'recent', 'url' => 'products/show'],
                'https://frontend.example.test/'
            )
        );
    }

    public function test_unsafe_base_url_falls_back_to_same_host_path(): void
    {
        self::assertSame(
            '/profile?tab=security',
            LegacyPageRedirects::location('/profile', ['tab' => 'security'], 'javascript:alert(1)')
        );
    }
}
