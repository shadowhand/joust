<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\App;
use Joust\Handler\NotFoundHandler;
use Joust\Locator\RouteCache;
use Joust\Response\JsonResponseFactory;
use Joust\RouteCollection;
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

    public function testItDefaultsToTheContainerNotFoundHandler(): void
    {
        $response = new App($this->container())->handle(new ServerRequest('GET', '/'));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('{"title":"Not Found","status":404}', (string) $response->getBody());
    }

    public function testItRunsMiddlewareInTheOrderAdded(): void
    {
        $container = $this->container([TraceMiddleware::class => new TraceMiddleware('lazy')]);

        $response = $this
            ->createApp($container)
            ->add(new TraceMiddleware('first'))
            ->lazy(TraceMiddleware::class)
            ->handle(new ServerRequest('GET', '/'));

        $this->assertSame(['lazy', 'first'], $response->getHeader('X-Trace'));
    }

    public function testItCollectsRoutes(): void
    {
        $response = $this->createApp()->collectRoutes(self::DIRECTORY)->handle(new ServerRequest('GET', '/users'));

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

    public function testItWritesTheRouteCacheWhenCollecting(): void
    {
        $app = $this->createApp()->collectRoutes(self::DIRECTORY, self::CACHE);

        $this->assertTrue(is_file(self::CACHE));

        $response = $app->handle(new ServerRequest('GET', '/users'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($this->handler->request);
    }

    public function testItReadsAnExistingRouteCache(): void
    {
        new RouteCache()->write(new RouteCollection(), self::CACHE);

        $response = $this->createApp()->cachedRoutes(self::CACHE)->handle(new ServerRequest('GET', '/users'));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertNull($this->handler->request);
    }

    /**
     * @param array<string, mixed> $entries
     */
    private function container(array $entries = []): TestContainer
    {
        return new TestContainer([
            NotFoundHandler::class => new NotFoundHandler(new JsonResponseFactory($this->factory, $this->factory)),
            GetUsers::class => $this->handler,
            ...$entries,
        ]);
    }

    private function createApp(?TestContainer $container = null): App
    {
        $container ??= $this->container();

        return new App($container, new NotFoundHandler(new JsonResponseFactory($this->factory, $this->factory)));
    }
}
