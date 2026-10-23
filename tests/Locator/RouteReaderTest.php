<?php

declare(strict_types=1);

namespace Joust\Tests\Locator;

use Errata\Middleware\ProblemMiddleware;
use Joust\Locator\RouteReader;
use Joust\Method;
use Joust\Route;
use Joust\Tests\Fixture\Routes\GetUsers;
use Joust\Tests\Fixture\Routes\UserActions;
use Joust\Tests\Fixture\TraceMiddleware;
use Joust\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function Psl\Vec\map;
use function Psl\Vec\values;

#[CoversClass(RouteReader::class)]
final class RouteReaderTest extends TestCase
{
    public function testItReadsRoutesWithoutMiddleware(): void
    {
        $routes = values(new RouteReader(GetUsers::class));

        $this->assertSame(
            ['GET /users ' . GetUsers::class],
            map(
                $routes,
                static fn(Route $route): string => "{$route->method->value} {$route->pattern->path} {$route->handler->name}",
            ),
        );

        foreach ($routes as $route) {
            $this->assertSame([], $route->handler->middleware);
        }
    }

    public function testItReadsEveryRouteAndDeclaredMiddleware(): void
    {
        $routes = values(new RouteReader(UserActions::class));

        $this->assertSame(
            [Method::Get, Method::Delete],
            map($routes, static fn(Route $route): Method => $route->method),
        );

        foreach ($routes as $route) {
            $this->assertSame('/users/:id', $route->pattern->path);
            $this->assertSame(UserActions::class, $route->handler->name);
            $this->assertSame([TraceMiddleware::class, ProblemMiddleware::class], $route->handler->middleware);
        }
    }
}
