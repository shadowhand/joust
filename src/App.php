<?php

declare(strict_types=1);

namespace Joust;

use Override;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function headers_sent;
use function Http\Response\send;
use function ob_get_length;
use function ob_get_level;
use function Psl\invariant;

/**
 * Application that handles HTTP requests.
 *
 * MAY add middleware to be processed before the request.
 * MAY add routes discovered at runtime.
 * MAY add cached routes.
 * MAY be used as a request handler.
 *
 * @api
 */
final class App implements RequestHandlerInterface
{
    /** @var list<MiddlewareInterface> */
    private array $middleware = [];

    private RequestHandlerInterface $fallback;

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
        $this->fallback = $this->container->get(Handler\NotFoundHandler::class);
    }

    public function setFallback(RequestHandlerInterface $fallback): static
    {
        $this->fallback = $fallback;

        return $this;
    }

    public function add(MiddlewareInterface $middleware): static
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * @param class-string<MiddlewareInterface> $middleware
     */
    public function lazy(string $middleware): static
    {
        return $this->add(new Middleware\LazyMiddleware($this->container, $middleware));
    }

    public function liveRouter(): static
    {
        $routes = $this->container->get(Finder\RouteFinder::class);

        return $this->add(new Middleware\RouterMiddleware($this->container, $routes));
    }

    public function cachedRouter(): static
    {
        $routes = $this->container->get(Cache\RouteCache::class)->read();

        return $this->add(new Middleware\RouterMiddleware($this->container, $routes));
    }

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new Handler\PipelineHandler($this->middleware, $this->fallback)->handle($request);
    }

    public function run(ServerRequestInterface $request): void
    {
        $response = $this->handle($request);

        invariant(!headers_sent(), message: 'Cannot emit response, headers already sent');
        invariant(
            ob_get_level() === 0 || ob_get_length() === 0,
            message: 'Cannot emit response, output has already been buffered',
        );

        send($response);
    }
}
