<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\App;
use Joust\Cache\RouteCache;
use Joust\Cache\RouteCollector;
use Joust\NotFoundHandler;
use Joust\Response\JsonResponseFactory;
use Joust\Tests\Fixture\Routes\GetUsers;
use Joust\Tests\Fixture\TestContainer;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\Fixture\TraceMiddleware;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;

use function Psl\Filesystem\delete_file;
use function Psl\Filesystem\is_file;

#[CoversClass(App::class)]
final class AppTest extends TestCase
{
    private const string DIRECTORY = __DIR__ . '/Fixture/Routes';
    private const string EMPTY_DIRECTORY = __DIR__ . '/Fixture/Empty';
    private const string CACHE = '/tmp/joust-app-routes.cache';

    private Psr17Factory $factory;
    private TestHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
        $this->handler = new TestHandler($this->factory);
    }

    #[Override]
    protected function tearDown(): void
    {
        if (is_file(self::CACHE)) {
            delete_file(self::CACHE);
        }
    }

    public function testItCallsTheFallbackWithoutMiddleware(): void
    {
        $response = $this->createApp()->handle(new ServerRequest('GET', '/'));

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testItRunsMiddlewareInTheOrderAdded(): void
    {
        $container = new TestContainer([TraceMiddleware::class => new TraceMiddleware('lazy')]);

        $response = $this
            ->createApp($container)
            ->add(new TraceMiddleware('first'))
            ->add(TraceMiddleware::class)
            ->handle(new ServerRequest('GET', '/'));

        $this->assertSame(['lazy', 'first'], $response->getHeader('X-Trace'));
    }

    public function testItCollectsRoutes(): void
    {
        $app = $this->createApp()->collectRoutes(self::DIRECTORY);

        $response = $app->handle(new ServerRequest('GET', '/users'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($this->handler->request);
    }

    public function testItAddsCollectedRoutesAtThePointCalled(): void
    {
        $response = $this
            ->createApp()
            ->add(new TraceMiddleware('before'))
            ->collectRoutes(self::DIRECTORY)
            ->add(new TraceMiddleware('after'))
            ->handle(new ServerRequest('GET', '/users'));

        $this->assertSame(['before'], $response->getHeader('X-Trace'));
    }

    public function testItWritesTheRouteCacheWhenMissing(): void
    {
        $app = $this->createApp()->cacheRoutes(self::DIRECTORY, self::CACHE);

        $this->assertTrue(is_file(self::CACHE));

        $response = $app->handle(new ServerRequest('GET', '/users'));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testItReadsAnExistingRouteCache(): void
    {
        new RouteCache(new RouteCollector())->write(self::EMPTY_DIRECTORY, self::CACHE);

        $app = $this->createApp()->cacheRoutes(self::DIRECTORY, self::CACHE);

        $response = $app->handle(new ServerRequest('GET', '/users'));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertNull($this->handler->request);
    }

    private function createApp(?TestContainer $container = null): App
    {
        $container ??= new TestContainer([GetUsers::class => $this->handler]);

        return new App($container, new NotFoundHandler(new JsonResponseFactory($this->factory, $this->factory)));
    }
}
