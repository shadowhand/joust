<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\Method;
use Joust\RouteList;
use Joust\RouteMatch;
use Joust\Router;
use Joust\Tests\Fixture\TestContainer;
use Joust\Tests\Fixture\TestHandler;
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

        $routes = new RouteList($this->createRoute(Method::Get, '/users/:id'));

        $router = new Router($container, $routes);

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

        $router = new Router(new TestContainer(), new RouteList());

        $next = new TestHandler($factory);

        $response = $router->process(new ServerRequest('GET', '/'), $next);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($next->request);
    }
}
