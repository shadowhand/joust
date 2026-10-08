<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\Method;
use Joust\Route;
use Joust\RouteHandler;
use Joust\RouteList;
use Joust\RouteMatch;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;

use function Psl\Vec\values;

#[CoversClass(Route::class)]
#[CoversClass(RouteHandler::class)]
#[CoversClass(RouteList::class)]
final class RouteListTest extends TestCase
{
    public function testItCountsRoutes(): void
    {
        $empty = new RouteList();
        $notEmpty = new RouteList($this->createRoute(), $this->createRoute(), $this->createRoute());

        $this->assertCount(0, $empty);
        $this->assertCount(3, $notEmpty);
    }

    public function testIteratesRoutesInOrder(): void
    {
        $first = $this->createRoute();
        $second = $this->createRoute();

        $list = new RouteList($first, $second);

        $this->assertSame([$first, $second], values($list));
    }

    public function testItMatchesRoutesByMethodAndPattern(): void
    {
        $get = $this->createRoute(Method::Get, pattern: '/test');
        $post = $this->createRoute(Method::Post, pattern: '/test');
        $root = $this->createRoute(pattern: '/');

        $list = new RouteList($get, $post, $root);

        $miss = $list->match(new ServerRequest('PATCH', '/users/99'));

        $this->assertNull($miss);

        $match = $list->match(new ServerRequest('POST', '/test'));

        $this->assertInstanceOf(RouteMatch::class, $match);
        $this->assertSame($post->handler, $match->handler);
    }
}
