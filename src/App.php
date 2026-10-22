<?php

declare(strict_types=1);

namespace Joust;

use Joust\Cache\RouteCache;
use Joust\Cache\RouteCollector;
use Joust\Response\Emitter;
use Joust\Response\JsonResponseFactory;
use Joust\Response\SapiEmitter;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Override;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
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

    private readonly RequestHandlerInterface $fallback;
    private readonly ServerRequestCreatorInterface $requestCreator;

    /**
     * The PSR-17 factory builds the default `NotFoundHandler` fallback and the requests created from globals.
     */
    public function __construct(
        private readonly ContainerInterface $container,
        ResponseFactoryInterface&ServerRequestFactoryInterface&StreamFactoryInterface&UploadedFileFactoryInterface&UriFactoryInterface $factory,
        ?RequestHandlerInterface $fallback = null,
        ?ServerRequestCreatorInterface $requestCreator = null,
        private readonly Emitter $emitter = new SapiEmitter(),
    ) {
        $this->fallback = $fallback ?? new NotFoundHandler(new JsonResponseFactory($factory, $factory));
        $this->requestCreator = $requestCreator ?? new ServerRequestCreator($factory, $factory, $factory, $factory);
    }

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

    /**
     * Handle the request created from globals and emit the response.
     */
    public function run(): void
    {
        $this->emitter->emit($this->handle($this->requestCreator->fromGlobals()));
    }
}
