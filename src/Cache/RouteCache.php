<?php

declare(strict_types=1);

namespace Joust\Cache;

use Joust\RouteCollection;
use Joust\RouteList;

use function Psl\File\read;
use function Psl\File\write;
use function Psl\Filesystem\create_directory_for_file;
use function Psl\Json\decode;
use function Psl\Json\encode;

/**
 * Reads and writes a route cache.
 *
 * MAY be configured with {@see RouteCacheSettings}.
 *
 * @api
 */
final readonly class RouteCache
{
    public function __construct(
        private RouteCacheSettings $settings = new RouteCacheSettings(),
    ) {}

    public function read(): RouteCollection
    {
        $cache = read($this->settings->cacheFile);

        return RouteCollection::type()->coerce(decode($cache));
    }

    public function write(RouteList $routes): RouteCollection
    {
        create_directory_for_file($this->settings->cacheFile);

        $list = new RouteCollection(...$routes);

        write($this->settings->cacheFile, encode($list));

        return $list;
    }
}
