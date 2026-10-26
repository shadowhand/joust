<?php

declare(strict_types=1);

namespace Joust\Finder;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Joust\RouteList;
use Override;
use Psr\Http\Server\RequestHandlerInterface;
use Traversable;

use function is_subclass_of;
use function Psl\Filesystem\is_directory;
use function Psl\Filesystem\is_readable;
use function Psl\invariant;
use function Psl\Vec\keys;

/**
 * Discovers request handlers for routing.
 *
 * Uses {@see RouteReader} to read attributes from the handlers.
 *
 * @api
 */
final readonly class RouteFinder implements RouteList
{
    public function __construct(
        private RouteFinderSettings $settings = new RouteFinderSettings(),
    ) {}

    #[Override]
    public function getIterator(): Traversable
    {
        invariant(
            is_directory($this->settings->routeDir) && is_readable($this->settings->routeDir),
            message: 'Cannot collect routes from unreadable directory',
        );

        $generator = new ClassMapGenerator(extensions: ['php']);
        $generator->scanPaths($this->settings->routeDir);

        $map = keys($generator->getClassMap()->getMap());

        foreach ($map as $class) {
            if (!is_subclass_of($class, RequestHandlerInterface::class, allow_string: true)) {
                continue;
            }

            yield from new RouteReader($class, $this->settings->baseUrl);
        }
    }
}
