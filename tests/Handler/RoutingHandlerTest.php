<?php

declare(strict_types=1);

namespace Joust\Tests\Handler;

use Joust\Handler\RoutingHandler;
use Joust\Method;
use Joust\Response\JsonResponseFactory;
use Joust\RouteList;
use Joust\RouteMatch;
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
        $factory = new Psr17Factory();
        $handler = new TestHandler($factory);
        $container = new TestContainer([TestHandler::class => $handler]);

        $route = $this->createRoute(Method::Get, '/users/:id');

        $routingHandler = new RoutingHandler(
            $container,
            new JsonResponseFactory($factory, $factory),
            new RouteList($route),
        );

        $response = $routingHandler->handle(new ServerRequest('GET', '/users/42'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($handler->request);

        $result = RouteMatch::fromRequest($handler->request);

        $this->assertSame(TestHandler::class, $result->handler->name);
    }

    public function testHandleReturnsProblemWhenNoRouteMatches(): void
    {
        $factory = new Psr17Factory();

        $routingHandler = new RoutingHandler(
            new TestContainer(),
            new JsonResponseFactory($factory, $factory),
            new RouteList(),
        );

        $response = $routingHandler->handle(new ServerRequest('GET', '/'));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/problem+json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame('{"title":"Not Found","status":404}', (string) $response->getBody());
    }
}
