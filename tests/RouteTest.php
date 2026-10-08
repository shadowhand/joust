<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\Method;
use Joust\Route;
use Joust\RouteHandler;
use Joust\Tests\Fixture\TestHandler;
use League\Uri\UrlPattern;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Route::class)]
final class RouteTest extends TestCase
{
    public function testExposesItsParts(): void
    {
        $pattern = UrlPattern::from('/users/:id');
        $handler = new RouteHandler(TestHandler::class);
        $route = new Route(Method::Get, $pattern, $handler);

        $this->assertSame(Method::Get, $route->method);
        $this->assertSame($pattern, $route->pattern);
        $this->assertSame($handler, $route->handler);
    }
}
