<?php

declare(strict_types=1);

namespace Joust\Cache;

use Joust\RouteList;

use function Psl\File\read;
use function Psl\File\write;
use function Psl\Filesystem\create_directory_for_file;
use function Psl\Type\instance_of;
use function Psl\Vec\values;
use function serialize;
use function unserialize;

final readonly class RouteCache
{
    public function __construct(
        private RouteCollector $collector,
    ) {}

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
    public function write(string $source, string $file): void
    {
        create_directory_for_file($file);

        $routes = new RouteList(...values($this->collector->in($source)));

        write($file, serialize($routes));
    }
}
