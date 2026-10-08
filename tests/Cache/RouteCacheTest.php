<?php

declare(strict_types=1);

namespace Joust\Tests\Cache;

use Joust\Cache\RouteCache;
use Joust\Cache\RouteCollector;
use Joust\Method;
use Joust\Route;
use Joust\RouteList;
use Joust\Tests\Fixture\Routes\GetUsers;
use Joust\Tests\Fixture\Routes\UserActions;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\TestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use Psl\Exception\InvariantViolationException;
use Psl\Type\Exception\AssertException;

use function file_put_contents;
use function glob;
use function is_dir;
use function is_file;
use function mkdir;
use function Psl\Vec\map;
use function Psl\Vec\sort;
use function Psl\Vec\values;
use function restore_error_handler;
use function rmdir;
use function serialize;
use function set_error_handler;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

#[CoversClass(RouteCache::class)]
final class RouteCacheTest extends TestCase
{
    private const string SOURCE = __DIR__ . '/../Fixture/Routes';

    private string $directory;

    #[Override]
    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/joust-route-cache-' . uniqid();

        mkdir($this->directory, permissions: 0o755, recursive: true);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->remove($this->directory);
    }

    public function testReadReturnsCachedRoutes(): void
    {
        $file = $this->directory . '/routes.cache';
        file_put_contents($file, serialize(new RouteList($this->createRoute(Method::Get, '/users'))));

        $routes = new RouteCache(new RouteCollector())->read($file);

        $this->assertCount(1, $routes);
        $this->assertSame(['GET /users ' . TestHandler::class], $this->routesIn($routes));
    }

    public function testReadFailsWhenCacheDoesNotExist(): void
    {
        $this->expectException(InvariantViolationException::class);

        new RouteCache(new RouteCollector())->read($this->directory . '/missing.cache');
    }

    public function testReadFailsWhenCacheIsNotARouteList(): void
    {
        $file = $this->directory . '/routes.cache';
        file_put_contents($file, serialize('not a route list'));

        $this->expectException(AssertException::class);

        new RouteCache(new RouteCollector())->read($file);
    }

    public function testWriteCachesCollectedRoutes(): void
    {
        $file = $this->directory . '/routes.cache';

        new RouteCache(new RouteCollector())->write(self::SOURCE, $file);

        $this->assertFileExists($file);
        $this->assertSame(
            [
                'DELETE /users/{id} ' . UserActions::class,
                'GET /users ' . GetUsers::class,
                'GET /users/{id} ' . UserActions::class,
            ],
            $this->routesIn(new RouteCache(new RouteCollector())->read($file)),
        );
    }

    public function testWriteCreatesMissingDirectory(): void
    {
        $file = $this->directory . '/nested/deeper/routes.cache';

        new RouteCache(new RouteCollector())->write(self::SOURCE, $file);

        $this->assertFileExists($file);
    }

    public function testWriteOverwritesExistingCache(): void
    {
        $file = $this->directory . '/routes.cache';
        file_put_contents($file, serialize(new RouteList()));

        new RouteCache(new RouteCollector())->write(self::SOURCE, $file);

        $this->assertCount(3, new RouteCache(new RouteCollector())->read($file));
    }

    public function testWriteFailsWhenDirectoryCannotBeCreated(): void
    {
        $blocker = $this->directory . '/blocker';
        file_put_contents($blocker, data: 'not a directory');

        $this->expectException(InvariantViolationException::class);

        $this->ignoringWarnings(static function () use ($blocker): void {
            new RouteCache(new RouteCollector())->write(self::SOURCE, $blocker . '/routes.cache');
        });
    }

    public function testWriteFailsWhenFileCannotBeWritten(): void
    {
        $file = $this->directory . '/routes.cache';
        mkdir($file);

        $this->expectException(InvariantViolationException::class);

        $this->ignoringWarnings(static function () use ($file): void {
            new RouteCache(new RouteCollector())->write(self::SOURCE, $file);
        });
    }

    /**
     * @return list<string>
     */
    private function routesIn(RouteList $routes): array
    {
        return sort(map(
            values($routes),
            static fn(Route $route): string => "{$route->method->value} {$route->pattern->path} {$route->handler->name}",
        ));
    }

    /**
     * @param callable(): void $callback
     */
    private function ignoringWarnings(callable $callback): void
    {
        set_error_handler(static fn(): bool => true);

        try {
            $callback();
        } finally {
            restore_error_handler();
        }
    }

    private function remove(string $path): void
    {
        if (is_file($path)) {
            unlink($path);

            return;
        }

        if (!is_dir($path)) {
            return;
        }

        foreach ($this->children($path) as $child) {
            $this->remove($child);
        }

        rmdir($path);
    }

    /**
     * @return list<string>
     */
    private function children(string $path): array
    {
        $children = glob($path . '/*');

        return $children === false ? [] : $children;
    }
}
