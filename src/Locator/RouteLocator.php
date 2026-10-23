<?php

declare(strict_types=1);

namespace Joust\Locator;

use CallbackFilterIterator;
use Joust\RouteList;
use Override;
use Psr\Http\Server\RequestHandlerInterface;
use Traversable;
use WyriHaximus\Lister;

use function is_subclass_of;
use function Psl\Filesystem\is_directory;
use function Psl\Filesystem\is_readable;
use function Psl\invariant;

/**
 * Discovers request handlers for routing.
 *
 * Uses {@see RouteReader} to read attributes from the handlers.
 *
 * @api
 */
final readonly class RouteLocator implements RouteList
{
    /** @var iterable<class-string<RequestHandlerInterface>> */
    private iterable $classes;

    public function __construct(
        /** @var non-empty-string */
        private string $directory,
    ) {
        invariant(
            is_directory($this->directory) && is_readable($this->directory),
            message: 'Cannot collect routes from unreadable directory',
        );

        // @mago-expect analysis:invalid-property-assignment-value
        $this->classes = new CallbackFilterIterator(
            Lister::instantiatableClassesInDirectory($this->directory),
            self::isRequestHandler(...),
        );
    }

    private static function isRequestHandler(string $class): bool
    {
        return is_subclass_of($class, RequestHandlerInterface::class, allow_string: true);
    }

    #[Override]
    public function getIterator(): Traversable
    {
        foreach ($this->classes as $class) {
            yield from new RouteReader($class);
        }
    }
}
