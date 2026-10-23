<?php

declare(strict_types=1);

namespace Joust\Locator;

use Joust\RouteCollection;
use Joust\RouteList;

use function Psl\File\read;
use function Psl\File\write;
use function Psl\Filesystem\create_directory_for_file;
use function Psl\Type\instance_of;
use function serialize;
use function unserialize;

/**
 * Reads and writes a route cache.
 *
 * @api
 */
final readonly class RouteCache
{
    /**
     * @param non-empty-string $file
     */
    public function read(string $file): RouteList
    {
        return instance_of(RouteList::class)->assert(unserialize(read($file)));
    }

    /**
     * @param non-empty-string $file
     */
    public function write(RouteList $routes, string $file): RouteList
    {
        create_directory_for_file($file);

        $list = new RouteCollection(...$routes);

        write($file, serialize($list));

        return $list;
    }
}
