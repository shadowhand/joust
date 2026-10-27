<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\Method;
use Joust\RouteCollection;
use PHPUnit\Framework\Attributes\CoversClass;

use function Psl\Json\decode;
use function Psl\Json\encode;
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

    public function testItSerializesAndReconstructsFromJson(): void
    {
        $routes = new RouteCollection(
            $this->createRoute(Method::Get, '/users'),
            $this->createRoute(Method::Post, '/users'),
        );

        $collection = RouteCollection::type()->coerce(decode(encode($routes)));

        $this->assertEquals(values($routes), values($collection));
    }

    public function testItReconstructsAnEmptyCollection(): void
    {
        $collection = RouteCollection::type()->coerce(decode('[]'));

        $this->assertSame([], values($collection));
    }

    public function testTypeAcceptsAnExistingCollection(): void
    {
        $collection = new RouteCollection($this->createRoute());

        $this->assertSame($collection, RouteCollection::type()->coerce($collection));
        $this->assertSame($collection, RouteCollection::type()->assert($collection));
    }
}
