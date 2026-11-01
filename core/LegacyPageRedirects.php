<?php

namespace Core;

/**
 * Legacy HTML page routes that have a corresponding Next.js page.
 *
 * API, media, payment callback, OAuth, and GET action endpoints are
 * intentionally omitted so they continue to be handled by the PHP backend.
 */
final class LegacyPageRedirects
{
    /** @var array<string, string> */
    private const PAGE_ROUTES = [
        '' => '/',
        'dashboard' => '/dashboard',
        'leaderboard' => '/leaderboard',
        'login-role' => '/login-role',
        'login' => '/login',
        'admin-login' => '/admin-login',
        'register' => '/register',
        'account-locked' => '/account-locked',
        'verify-otp' => '/verify-otp',
        'forgot-password' => '/forgot-password',
        'reset-password' => '/reset-password',
        'rewards' => '/rewards',
        'profile' => '/profile',
        'products' => '/products',
        'products/create' => '/products/create',
        'products/my' => '/my-listings',
        'transactions/history' => '/transactions',
        'admin' => '/admin',
        'admin/users' => '/admin/users',
        'admin/users/detail' => '/admin/users/detail',
        'admin/users/violations' => '/admin/users/violations',
        'admin/products' => '/admin/products',
        'admin/categories' => '/admin/categories',
        'admin/banners' => '/admin/banners',
        'admin/reports' => '/admin/reports',
        'admin/system-reports' => '/admin/system-reports',
        'admin/audit-log' => '/admin/audit-log',
        'admin/ratings' => '/admin/ratings',
        'admin/giveaways' => '/admin/giveaways',
        'admin/giveaway_spin' => '/admin/giveaway_spin',
        'chat' => '/chat',
        'notifications' => '/notifications',
        'wishlist' => '/wishlist',
    ];

    /**
     * Return the Next.js path for a migrated PHP page, or null if the route
     * must remain in PHP.
     *
     * @param array<string, mixed> $query
     */
    public static function targetPath(string $legacyRoute, array $query = []): ?string
    {
        if (isset(self::PAGE_ROUTES[$legacyRoute])) {
            return self::PAGE_ROUTES[$legacyRoute];
        }

        if ($legacyRoute === 'products/show') {
            $id = self::positiveInteger($query['id'] ?? null);
            return $id === null ? '/products' : '/products/' . $id;
        }

        if ($legacyRoute === 'chat/show') {
            $id = self::positiveInteger($query['id'] ?? null);
            return $id === null ? '/chat' : '/chat/' . $id;
        }

        if ($legacyRoute === 'users/profile') {
            $id = self::positiveInteger($query['id'] ?? null);
            return '/users/' . ($id ?? '0');
        }

        return null;
    }

    /**
     * Build a same-host relative Location unless an explicit safe Next.js base
     * URL has been configured.
     *
     * @param array<string, mixed> $query
     */
    public static function location(string $targetPath, array $query = [], ?string $nextBaseUrl = null): string
    {
        unset($query['url']);

        // The id is represented by the dynamic Next.js path for these routes.
        if (preg_match('#^/(products|chat|users)/[0-9]+$#', $targetPath)) {
            unset($query['id']);
        }

        $queryString = http_build_query($query);
        $path = $targetPath . ($queryString === '' ? '' : '?' . $queryString);

        $baseUrl = self::validatedBaseUrl($nextBaseUrl);
        return $baseUrl === null ? $path : $baseUrl . $path;
    }

    private static function positiveInteger(mixed $value): ?string
    {
        if (!is_scalar($value) || !preg_match('/^[1-9][0-9]*$/', (string) $value)) {
            return null;
        }

        return (string) $value;
    }

    private static function validatedBaseUrl(?string $baseUrl): ?string
    {
        if ($baseUrl === null || trim($baseUrl) === '') {
            return null;
        }

        $baseUrl = rtrim(trim($baseUrl), '/');
        $parts = parse_url($baseUrl);
        if (
            filter_var($baseUrl, FILTER_VALIDATE_URL) === false
            || $parts === false
            || !isset($parts['scheme'], $parts['host'])
            || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])
        ) {
            return null;
        }

        return $baseUrl;
    }
}
