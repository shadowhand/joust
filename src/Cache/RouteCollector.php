<?php

declare(strict_types=1);

namespace Joust\Cache;

use Generator;
use Joust\Attribute\AsRoute;
use Joust\Route;
use Joust\RouteHandler;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use WyriHaximus\Lister;

use function is_subclass_of;

/**
 * @api
 */
final readonly class RouteCollector
{
    /**
     * @return iterable<int, Route>
     */
    public function in(string $directory): iterable
    {
        /** @var list<class-string> $classes */
        $classes = Lister::instantiatableClassesInDirectory($directory);

        foreach ($classes as $class) {
            if (!$this->isRequestHandler($class)) {
                continue;
            }

            yield from $this->on($class);
        }
    }

    /**
     * @return Generator<int, Route>
     */
    public function on(string $class): iterable
    {
        $handler = new RouteHandler($class);

        $attributes = new ReflectionClass($class)->getAttributes(AsRoute::class);

        foreach ($attributes as $attr) {
            $route = $attr->newInstance();

            yield new Route($route->method, $route->pattern, $handler);
        }
    }

    /**
     * @phpstan-assert-if-true class-string<RequestHandlerInterface> $class
     */
    private function isRequestHandler(string $class): bool
    {
        return is_subclass_of($class, class: RequestHandlerInterface::class, allow_string: true);
    }
}
