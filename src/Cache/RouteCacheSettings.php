<?php

declare(strict_types=1);

namespace Joust\Cache;

use Joust\HasRootDir;

use function Psl\Type\non_empty_string;

/**
 * Provides settings for route caching.
 *
 * SHOULD be used with {@see RouteCache}.
 *
 * @api
 */
final readonly class RouteCacheSettings
{
    use HasRootDir;

    /**
     * File path to store cached routes.
     *
     * @var non-empty-string
     */
    public string $cacheFile;

    public function __construct(?string $cacheFile = null)
    {
        // @mago-format-ignore-next
        $this->cacheFile = $cacheFile ? non_empty_string()->assert($cacheFile) : $this->rootPath('var/joust-routes.php.cache');
    }
}
