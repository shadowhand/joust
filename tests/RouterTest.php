<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\Method;
use Joust\Response\JsonResponseFactory;
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
    public function testShouldActAsPsrHandler(): void
    {
        $factory = new Psr17Factory();
        $handler = new TestHandler($factory);
        $container = new TestContainer([TestHandler::class => $handler]);

        $routes = new RouteList($this->createRoute(Method::Get, '/users/:id'));

        $router = new Router($container, new JsonResponseFactory($factory, $factory), $routes);

        $response = $router->handle(new ServerRequest('GET', '/users/42'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($handler->request);

        $result = RouteMatch::fromRequest($handler->request);

        $this->assertSame(TestHandler::class, $result->handler->name);
    }

    public function testShouldActAsPsrMiddleware(): void
    {
        $factory = new Psr17Factory();
        $handler = new TestHandler($factory);
        $container = new TestContainer([TestHandler::class => $handler]);

        $routes = new RouteList($this->createRoute(Method::Get, '/users/:id'));

        $router = new Router($container, new JsonResponseFactory($factory, $factory), $routes);

        $next = new TestHandler($factory);

        $response = $router->process(new ServerRequest('GET', '/users/42'), $next);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull($next->request);
        $this->assertNotNull($handler->request);

        $result = RouteMatch::fromRequest($handler->request);

        $this->assertSame(TestHandler::class, $result->handler->name);
    }

    public function testShouldReturnNotFoundAsHandler(): void
    {
        $factory = new Psr17Factory();
        $container = new TestContainer();

        $router = new Router($container, new JsonResponseFactory($factory, $factory), new RouteList());

        $response = $router->handle(new ServerRequest('GET', '/'));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/problem+json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame('{"title":"Not Found","status":404}', (string) $response->getBody());
    }

    public function testShouldPassThroughAsMiddleware(): void
    {
        $factory = new Psr17Factory();
        $container = new TestContainer();

        $router = new Router($container, new JsonResponseFactory($factory, $factory), new RouteList());

        $next = new TestHandler($factory);

        $response = $router->process(new ServerRequest('GET', '/'), $next);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($next->request);
    }
}
