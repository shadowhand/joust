<?php

declare(strict_types=1);

namespace Joust\Locator;

use Joust\Attribute\AsRoute;
use Joust\Attribute\Middleware;
use Joust\Route;
use Joust\RouteHandler;
use Joust\RouteList;
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

            yield new Route($route->method, $route->pattern, $handler);
        }
    }
}
