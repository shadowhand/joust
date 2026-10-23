<?php

declare(strict_types=1);

namespace Joust\Tests\Middleware;

use Joust\Method;
use Joust\Middleware\Router;
use Joust\Route;
use Joust\RouteCollection;
use Joust\RouteMatch;
use Joust\Tests\Fixture\OtherTraceMiddleware;
use Joust\Tests\Fixture\TestContainer;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\Fixture\TraceMiddleware;
use Joust\Tests\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Router::class)]
final class RouterTest extends TestCase
{
    public function testShouldDispatchMatchedRoute(): void
    {
        $factory = new Psr17Factory();
        $handler = new TestHandler($factory);
        $container = new TestContainer([TestHandler::class => $handler]);

        $router = new Router($container, new RouteCollection($this->createRoute(Method::Get, '/users/:id')));

        $next = new TestHandler($factory);

        $response = $router->process(new ServerRequest('GET', '/users/42'), $next);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull($next->request);
        $this->assertNotNull($handler->request);

        $result = RouteMatch::fromRequest($handler->request);

        $this->assertSame(TestHandler::class, $result->handler->name);
    }

    public function testShouldPassThroughWhenNoRouteMatches(): void
    {
        $factory = new Psr17Factory();

        $router = new Router(new TestContainer(), new RouteCollection());

        $next = new TestHandler($factory);

        $response = $router->process(new ServerRequest('GET', '/'), $next);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($next->request);
    }

    public function testShouldRunRouteMiddlewareInDeclaredOrder(): void
    {
        $factory = new Psr17Factory();
        $handler = new TestHandler($factory);
        $container = new TestContainer([
            TestHandler::class => $handler,
            TraceMiddleware::class => new TraceMiddleware('first'),
            OtherTraceMiddleware::class => new OtherTraceMiddleware('second'),
        ]);

        $route = $this->createRoute(Method::Get, '/users/:id');
        $route = new Route(
            $route->method,
            $route->pattern,
            $route->handler->withMiddleware(TraceMiddleware::class, OtherTraceMiddleware::class),
        );

        $router = new Router($container, new RouteCollection($route));

        $response = $router->process(new ServerRequest('GET', '/users/42'), new TestHandler($factory));

        $this->assertNotNull($handler->request);
        $this->assertSame(['first', 'second'], $handler->request->getHeader('X-Trace'));
        $this->assertSame(['second', 'first'], $response->getHeader('X-Trace'));
        $this->assertSame(TestHandler::class, RouteMatch::fromRequest($handler->request)->handler->name);
    }
}
