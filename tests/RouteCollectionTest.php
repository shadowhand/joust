<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\RouteCollection;
use PHPUnit\Framework\Attributes\CoversClass;

use function Psl\Vec\values;

#[CoversClass(RouteCollection::class)]
final class RouteCollectionTest extends TestCase
{
    public function testItCountsRoutes(): void
    {
        $empty = new RouteCollection();
        $notEmpty = new RouteCollection($this->createRoute(), $this->createRoute(), $this->createRoute());

        $this->assertCount(0, $empty);
        $this->assertCount(3, $notEmpty);
    }

    public function testIteratesRoutesInOrder(): void
    {
        $first = $this->createRoute();
        $second = $this->createRoute();

        $list = new RouteCollection($first, $second);

        $this->assertSame([$first, $second], values($list));
    }
}
