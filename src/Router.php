<?php

declare(strict_types=1);

namespace Joust;

use Joust\Handler\NotFoundHandler;
use League\Uri\UrlPattern;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @api
 */
final readonly class Router
{
    public function __construct(
        private RouteList $routes,
        private ?RouteHandler $defaultHandler = null,
    ) {}

    public function match(ServerRequestInterface $request): RouteResult
    {
        $requestMethod = Method::fromRequest($request);
        $requestUri = (string) $request->getUri();

        foreach ($this->routes as $route) {
            if ($route->method !== $requestMethod) {
                continue;
            }

            $result = $route->pattern->extract($requestUri);

            if ($result) {
                return new RouteResult($route, $result);
            }
        }

        static $wildcardPattern = UrlPattern::from('*');

        $requestHandler = $this->defaultHandler ?? new RouteHandler(NotFoundHandler::class);

        $emptyRoute = new Route($requestMethod, $wildcardPattern, $requestHandler);
        $result = $wildcardPattern->extract($requestUri);

        // @mago-expect analysis:possibly-null-argument
        return new RouteResult($emptyRoute, $result);
    }
}
