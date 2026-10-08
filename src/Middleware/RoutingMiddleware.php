<?php

declare(strict_types=1);

namespace Joust\Middleware;

use Joust\RouteHandler;
use Joust\RouteList;
use Joust\RouteMatch;
use Override;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @api
 */
final readonly class RoutingMiddleware implements MiddlewareInterface
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

        return $this->resolve($match->handler)->handle($request);
    }

    private function resolve(RouteHandler $handler): RequestHandlerInterface
    {
        // @mago-expect analysis:mixed-return-statement
        return $this->container->get($handler->name);
    }
}
