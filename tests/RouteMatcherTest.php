<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\Method;
use Joust\RouteCollection;
use Joust\RouteMatch;
use Joust\RouteMatcher;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteMatcher::class)]
final class RouteMatcherTest extends TestCase
{
    public function testItMatchesRoutesByMethodAndPattern(): void
    {
        $get = $this->createRoute(Method::Get, pattern: '/test');
        $post = $this->createRoute(Method::Post, pattern: '/test');
        $root = $this->createRoute(pattern: '/');

        $matcher = new RouteMatcher(new RouteCollection($get, $post, $root));

        $this->assertNull($matcher->match(new ServerRequest('PATCH', '/users/99')));
        $this->assertNull($matcher->match(new ServerRequest('GET', '/nope')));

        $match = $matcher->match(new ServerRequest('POST', '/test'));

        $this->assertInstanceOf(RouteMatch::class, $match);
        $this->assertSame($post->handler, $match->handler);
    }
}
