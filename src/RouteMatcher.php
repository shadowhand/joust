<?php

declare(strict_types=1);

namespace Joust;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Matches a request to a route.
 *
 * @internal
 */
final readonly class RouteMatcher
{
    public function __construct(
        private RouteList $list,
    ) {}

    public function match(ServerRequestInterface $request): ?RouteMatch
    {
        $requestMethod = Method::fromRequest($request);

        foreach ($this->list as $route) {
            if ($route->method !== $requestMethod) {
                continue;
            }

            $result = $route->pattern->extract($request->getUri());

            if ($result) {
                return new RouteMatch($route->handler, $result);
            }
        }

        return null;
    }
}
