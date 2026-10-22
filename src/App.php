<?php

declare(strict_types=1);

namespace Joust;

use Joust\Cache\RouteCache;
use Joust\Cache\RouteCollector;
use Override;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function is_string;
use function Psl\Filesystem\is_file;
use function Psl\Vec\values;

/**
 * An HTTP application: middleware runs in the order it is added, then the fallback handler.
 *
 * @api
 */
final class App implements RequestHandlerInterface
{
    /** @var list<MiddlewareInterface> */
    private array $middleware = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly RequestHandlerInterface $fallback,
    ) {}

    /**
     * Add middleware to the end of the pipeline. A class name is resolved from the container when a request reaches it.
     *
     * @param MiddlewareInterface|class-string<MiddlewareInterface> $middleware
     */
    public function add(MiddlewareInterface|string $middleware): static
    {
        if (is_string($middleware)) {
            $middleware = new LazyMiddleware($this->container, $middleware);
        }

        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Add a router for the routes discovered in a directory.
     */
    public function collectRoutes(string $directory): static
    {
        $routes = new RouteList(...values(new RouteCollector()->in($directory)));

        return $this->add(new Router($this->container, $routes));
    }

    /**
     * Add a router for the routes in a cache file, discovering routes in a directory and writing the cache first when
     * the file does not exist.
     *
     * @param non-empty-string $file
     */
    public function cacheRoutes(string $directory, string $file): static
    {
        $cache = new RouteCache(new RouteCollector());

        if (!is_file($file)) {
            $cache->write($directory, $file);
        }

        return $this->add(new Router($this->container, $cache->read($file)));
    }

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Pipeline($this->middleware, $this->fallback)->handle($request);
    }
}
