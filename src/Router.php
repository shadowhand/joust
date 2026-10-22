<?php

declare(strict_types=1);

namespace Joust;

use Override;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Psl\Vec\map;

/**
 * @api
 */
final readonly class Router implements MiddlewareInterface
{
    public function __construct(
        private ContainerInterface $container,
        private RouteList $routeList,
    ) {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $match = $this->routeList->match($request);

        if (!$match) {
            return $handler->handle($request);
        }

        $request = $request->withAttribute(RouteMatch::class, $match);

        return $this->pipeline($match->handler)->handle($request);
    }

    private function pipeline(RouteHandler $handler): RequestHandlerInterface
    {
        $middleware = map(
            $handler->middleware,
            fn(string $name): MiddlewareInterface => new LazyMiddleware($this->container, $name),
        );

        return new Pipeline($middleware, $this->resolve($handler));
    }

    private function resolve(RouteHandler $handler): RequestHandlerInterface
    {
        // @mago-expect analysis:mixed-return-statement
        return $this->container->get($handler->name);
    }
}
