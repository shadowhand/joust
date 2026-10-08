<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\Handler\NotFoundHandler;
use Joust\Method;
use Joust\RouteHandler;
use Joust\RouteList;
use Joust\Router;
use Joust\Tests\Fixture\TestHandler;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Router::class)]
final class RouterTest extends TestCase
{
    public function testMatchesRouteByMethodAndUri(): void
    {
        $route = $this->createRoute(Method::Get, '/users/:id');
        $router = new Router(new RouteList($route));

        $result = $router->match(new ServerRequest('GET', '/users/42'));

        $this->assertSame($route, $result->route);
        $this->assertSame('42', $result->result->path->string('id'));
    }

    public function testSkipsRoutesWithNonMatchingMethod(): void
    {
        $get = $this->createRoute(Method::Get, '/users/:id');
        $post = $this->createRoute(Method::Post, '/users');

        $router = new Router(new RouteList($post, $get));

        $result = $router->match(new ServerRequest('GET', '/users/42'));

        $this->assertSame($get, $result->route);
    }

    public function testContinuesWhenUriDoesNotMatch(): void
    {
        $miss = $this->createRoute(Method::Get, '/users/:id');
        $hit = $this->createRoute(Method::Get, '/posts/:id');
        $router = new Router(new RouteList($miss, $hit));

        $result = $router->match(new ServerRequest('GET', '/posts/7'));

        $this->assertSame($hit, $result->route);
    }

    public function testReturnsNotFoundWhenUriDoesNotMatch(): void
    {
        $route = $this->createRoute(Method::Get, '/users/:id');
        $router = new Router(new RouteList($route));

        $result = $router->match(new ServerRequest('GET', '/other'));

        $this->assertSame(Method::Get, $result->route->method);
        $this->assertSame('/other', $result->result->path->string(0));
        $this->assertSame(NotFoundHandler::class, $result->route->handler->name);
    }

    public function testReturnsNotFoundWhenNoRouteMatchesMethod(): void
    {
        $route = $this->createRoute(Method::Post, '/users/:id');
        $router = new Router(new RouteList($route));

        $result = $router->match(new ServerRequest('GET', '/users/42'));

        $this->assertSame('/users/42', $result->result->path->string(0));
        $this->assertSame(NotFoundHandler::class, $result->route->handler->name);
    }

    public function testUsesCustomDefaultHandler(): void
    {
        $handler = new RouteHandler(TestHandler::class);
        $router = new Router(new RouteList($this->createRoute(Method::Post, '/users')), $handler);

        $result = $router->match(new ServerRequest('GET', '/users'));

        $this->assertSame($handler, $result->route->handler);
    }
}
