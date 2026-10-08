<?php

declare(strict_types=1);

namespace Joust\Tests\Middleware;

use Joust\Method;
use Joust\Middleware\RoutingMiddleware;
use Joust\RouteList;
use Joust\RouteMatch;
use Joust\Tests\Fixture\TestContainer;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RoutingMiddleware::class)]
final class RoutingMiddlewareTest extends TestCase
{
    public function testProcessDispatchesToResolvedHandler(): void
    {
        $factory = new Psr17Factory();
        $handler = new TestHandler($factory);
        $container = new TestContainer([TestHandler::class => $handler]);

        $middleware = new RoutingMiddleware($container, new RouteList($this->createRoute(Method::Get, '/users/:id')));

        $next = new TestHandler($factory);

        $response = $middleware->process(new ServerRequest('GET', '/users/42'), $next);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull($next->request);
        $this->assertNotNull($handler->request);

        $result = RouteMatch::fromRequest($handler->request);

        $this->assertSame(TestHandler::class, $result->handler->name);
    }

    public function testProcessPassesThroughWhenNoRouteMatches(): void
    {
        $factory = new Psr17Factory();

        $middleware = new RoutingMiddleware(
            new TestContainer(),
            new RouteList($this->createRoute(Method::Get, '/users/:id')),
        );

        $next = new TestHandler($factory);

        $response = $middleware->process(new ServerRequest('GET', '/nope'), $next);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($next->request);
    }
}
