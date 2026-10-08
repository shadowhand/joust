<?php

declare(strict_types=1);

namespace Joust\Tests\Cache;

use Joust\Cache\RouteCache;
use Joust\Cache\RouteCollector;
use Joust\Route;
use Joust\Tests\Fixture\Routes\GetUsers;
use Joust\Tests\Fixture\Routes\UserActions;
use Joust\Tests\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function Psl\Filesystem\delete_file;
use function Psl\Filesystem\is_file;
use function Psl\Vec\map;
use function Psl\Vec\sort;

#[CoversClass(RouteCache::class)]
#[CoversClass(RouteCollector::class)]
final class RouteCacheTest extends TestCase
{
    private const string DIRECTORY = __DIR__ . '/../Fixture/Routes';
    private const string EMPTY_DIRECTORY = __DIR__ . '/../Fixture/Empty';
    private const string CACHE = '/tmp/joust-routes.cache';

    #[Override]
    protected function tearDown(): void
    {
        if (is_file(self::CACHE)) {
            delete_file(self::CACHE);
        }
    }

    public function testItRoundTripsToAndFromCache(): void
    {
        $cache = new RouteCache(new RouteCollector());

        $cache->write(self::DIRECTORY, self::CACHE);

        $list = $cache->read(self::CACHE);

        $handlers = sort(map($list, static fn(Route $route): string => $route->handler->name));

        $this->assertSame([GetUsers::class, UserActions::class, UserActions::class], $handlers);
    }

    public function testInYieldsNothingWhenDirectoryHasNoHandlers(): void
    {
        $cache = new RouteCache(new RouteCollector());

        $cache->write(self::EMPTY_DIRECTORY, self::CACHE);

        $list = $cache->read(self::CACHE);

        $this->assertCount(0, $list);
    }
}
