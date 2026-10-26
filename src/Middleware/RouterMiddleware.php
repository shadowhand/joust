<?php

declare(strict_types=1);

namespace Joust\Middleware;

use Joust\Handler\PipelineHandler;
use Joust\RouteHandler;
use Joust\RouteList;
use Joust\RouteMatch;
use Joust\RouteMatcher;
use Override;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Psl\Vec\map;

/**
 * Provides routing for requests.
 *
 * When a route matches the request URI, the route handler will be invoked with a {@see RouteMatch} attribute.
 * When no route matches, will pass the request to the provided request handler.
 *
 * @api
 */
final readonly class RouterMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ContainerInterface $container,
        private RouteList $routes,
    ) {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $match = new RouteMatcher($this->routes)->match($request);

        if (!$match) {
            return $handler->handle($request);
        }

        $request = $request->withAttribute(RouteMatch::class, $match);

        return $this->pipeline($match->handler)->handle($request);
    }

    private function pipeline(RouteHandler $handler): RequestHandlerInterface
    {
        return new PipelineHandler(map($handler->middleware, $this->lazy(...)), $this->resolve($handler));
    }

    /**
     * @param class-string<MiddlewareInterface> $name
     */
    private function lazy(string $name): LazyMiddleware
    {
        return new LazyMiddleware($this->container, $name);
    }

    private function resolve(RouteHandler $handler): RequestHandlerInterface
    {
        // @mago-expect analysis:mixed-return-statement
        return $this->container->get($handler->name);
    }
}
