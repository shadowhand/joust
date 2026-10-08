<?php

declare(strict_types=1);

namespace Joust\Tests\Handler;

use Joust\Handler\RoutingHandler;
use Joust\Method;
use Joust\RouteList;
use Joust\Router;
use Joust\RouteResult;
use Joust\Tests\Fixture\TestContainer;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RoutingHandler::class)]
final class RoutingHandlerTest extends TestCase
{
    public function testHandleDispatchesToResolvedHandler(): void
    {
        $handler = new TestHandler(new Psr17Factory());
        $container = new TestContainer([TestHandler::class => $handler]);

        $route = $this->createRoute(Method::Get, '/users/:id');
        $routingHandler = new RoutingHandler($container, new Router(new RouteList($route)));

        $response = $routingHandler->handle(new ServerRequest('GET', '/users/42'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($handler->request);

        $result = RouteResult::fromRequest($handler->request);

        $this->assertSame($route, $result->route);
    }
}
