<?php

declare(strict_types=1);

namespace Joust;

use Errata\Http\Client\NotFound;
use Joust\Response\JsonResponseFactory;
use Override;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @api
 */
final readonly class Router implements MiddlewareInterface, RequestHandlerInterface
{
    public function __construct(
        private ContainerInterface $container,
        private JsonResponseFactory $jsonResponseFactory,
        private RouteList $routeList,
    ) {}

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $match = $this->routeList->match($request);

        if ($match) {
            return $this->execute($request, $match);
        }

        return $this->jsonResponseFactory->problem(new NotFound());
    }

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $match = $this->routeList->match($request);

        if ($match) {
            return $this->execute($request, $match);
        }

        return $handler->handle($request);
    }

    private function execute(ServerRequestInterface $request, RouteMatch $match): ResponseInterface
    {
        $request = $request->withAttribute(RouteMatch::class, $match);

        return $this->resolve($match->handler)->handle($request);
    }

    private function resolve(RouteHandler $handler): RequestHandlerInterface
    {
        // @mago-expect analysis:mixed-return-statement
        return $this->container->get($handler->name);
    }
}
