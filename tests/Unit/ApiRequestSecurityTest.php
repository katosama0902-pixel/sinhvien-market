<?php

namespace Tests\Unit;

use Core\Controller;
use Core\Router;
use PHPUnit\Framework\TestCase;

class ApiRequestSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = ['csrf_token' => str_repeat('a', 64)];
        unset($_SERVER['HTTP_X_CSRF_TOKEN'], $_SERVER['CONTENT_TYPE']);
        $_POST = [];
    }

    public function test_csrf_header_is_accepted_for_fetch_requests(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = str_repeat('a', 64);
        $controller = new class extends Controller {
            public function csrfIsValid(): bool { return $this->verifyCsrf(); }
        };

        self::assertTrue($controller->csrfIsValid());
    }

    public function test_csrf_header_rejects_mismatched_token(): void
    {
        $_SERVER['HTTP_X_CSRF_TOKEN'] = str_repeat('b', 64);
        $controller = new class extends Controller {
            public function csrfIsValid(): bool { return $this->verifyCsrf(); }
        };

        self::assertFalse($controller->csrfIsValid());
    }

    public function test_csrf_rejects_empty_token_even_without_session_token(): void
    {
        $_SESSION = [];
        $_SERVER['HTTP_X_CSRF_TOKEN'] = '';
        $controller = new class extends Controller {
            public function csrfIsValid(): bool { return $this->verifyCsrf(); }
        };

        self::assertFalse($controller->csrfIsValid());
    }

    public function test_router_registers_put_and_delete_methods(): void
    {
        $router = new Router();
        $router->put('api/profile/me', 'NextApi', 'updateProfile');
        $router->delete('api/user/products', 'NextApi', 'deleteProduct');

        $routes = new \ReflectionProperty(Router::class, 'routes');
        $routes->setAccessible(true);
        $registered = $routes->getValue($router);
        self::assertSame('updateProfile', $registered['PUT']['api/profile/me']['action']);
        self::assertSame('deleteProduct', $registered['DELETE']['api/user/products']['action']);
    }

    public function test_router_extracts_public_profile_id_parameter(): void
    {
        $router = new Router();
        $router->get('api/users/{id}/profile', 'NextApi', 'publicProfile');
        $method = new \ReflectionMethod(Router::class, 'findRoute');
        $method->setAccessible(true);

        $route = $method->invoke($router, 'GET', 'api/users/42/profile');

        self::assertSame('publicProfile', $route['action']);
        self::assertSame('42', $_GET['id']);
    }
}
