<?php

declare(strict_types=1);

namespace Joust\Finder;

use Joust\Attribute\AsRoute;
use Joust\Attribute\Middleware;
use Joust\Route;
use Joust\RouteHandler;
use Joust\RouteList;
use League\Uri\UrlPattern;
use League\Uri\UrlPatternBuilder;
use Override;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use Traversable;

use function Psl\Type\class_string;

/**
 * Discovers routes on request handlers.
 *
 * Creates routes from {@see AsRoute} attributes.
 * Applies middleware from {@see Middleware} attributes.
 *
 * @api
 */
final readonly class RouteReader implements RouteList
{
    /**
     * @assert class-string<RequestHandlerInterface> $class
     */
    public function __construct(
        /** @var class-string<RequestHandlerInterface> */
        private string $class,
        /** @var non-empty-string|null */
        private ?string $baseUrl = null,
    ) {
        class_string(RequestHandlerInterface::class)->assert($class);
    }

    #[Override]
    public function getIterator(): Traversable
    {
        $handler = new RouteHandler($this->class);
        $reflection = new ReflectionClass($this->class);

        foreach ($reflection->getAttributes(Middleware::class) as $attr) {
            $handler = $handler->withMiddleware($attr->newInstance()->name);
        }

        foreach ($reflection->getAttributes(AsRoute::class) as $attr) {
            $route = $attr->newInstance();
            $pattern = $this->buildPattern($route);

            yield new Route($route->method, $pattern, $handler);
        }
    }

    private function buildPattern(AsRoute $route): UrlPattern
    {
        return new UrlPatternBuilder(
            baseUrl: $route->baseUrl ?? $this->baseUrl,
            scheme: $route->scheme,
            username: $route->username,
            password: $route->password,
            host: $route->host,
            path: $route->path,
            query: $route->query,
            fragment: $route->fragment,
        )->build();
    }
}
