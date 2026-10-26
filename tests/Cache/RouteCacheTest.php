<?php

declare(strict_types=1);

namespace Joust\Tests\Cache;

use Errata\Middleware\ProblemMiddleware;
use Joust\Cache\RouteCache;
use Joust\Cache\RouteCacheSettings;
use Joust\Finder\RouteFinder;
use Joust\Finder\RouteFinderSettings;
use Joust\Route;
use Joust\RouteCollection;
use Joust\Tests\Fixture\Routes\GetUsers;
use Joust\Tests\Fixture\Routes\UserActions;
use Joust\Tests\Fixture\TraceMiddleware;
use Joust\Tests\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function Psl\Filesystem\delete_file;
use function Psl\Filesystem\is_file;
use function Psl\Vec\map;
use function Psl\Vec\sort;
use function Psl\Vec\values;

#[CoversClass(RouteCache::class)]
final class RouteCacheTest extends TestCase
{
    private const string DIRECTORY = __DIR__ . '/../Fixture/Routes';
    private const string CACHE = '/tmp/joust-routes.cache';

    #[Override]
    protected function tearDown(): void
    {
        if (is_file(self::CACHE)) {
            delete_file(self::CACHE);
        }
    }

    public function testItWritesTheRoutesToTheCache(): void
    {
        $list = $this->createCache()->write(new RouteFinder(new RouteFinderSettings(self::DIRECTORY)));

        $this->assertTrue(is_file(self::CACHE));

        $handlers = sort(map($list, static fn(Route $route): string => $route->handler->name));

        $this->assertSame([GetUsers::class, UserActions::class, UserActions::class], $handlers);
    }

    public function testItReadsTheRoutesFromTheCache(): void
    {
        $this->createCache()->write(new RouteFinder(new RouteFinderSettings(self::DIRECTORY)));

        $list = $this->createCache()->read();

        $handlers = sort(map($list, static fn(Route $route): string => $route->handler->name));

        $this->assertSame([GetUsers::class, UserActions::class, UserActions::class], $handlers);
    }

    public function testItCarriesHandlerMiddlewareThroughTheCache(): void
    {
        $this->createCache()->write(new RouteFinder(new RouteFinderSettings(self::DIRECTORY)));

        $middleware = [];

        foreach ($this->createCache()->read() as $route) {
            $middleware[$route->handler->name] = $route->handler->middleware;
        }

        $this->assertSame([], $middleware[GetUsers::class] ?? null);
        $this->assertSame([TraceMiddleware::class, ProblemMiddleware::class], $middleware[UserActions::class] ?? null);
    }

    public function testItCachesAnEmptyRouteList(): void
    {
        $this->createCache()->write(new RouteCollection());

        $this->assertSame([], values($this->createCache()->read()));
    }

    private function createCache(): RouteCache
    {
        return new RouteCache(new RouteCacheSettings(self::CACHE));
    }
}
