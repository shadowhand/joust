<?php

declare(strict_types=1);

namespace Joust\Finder;

use Joust\HasRootDir;

use function Psl\Type\non_empty_string;
use function Psl\Type\nullable;

/**
 * Provides settings for route finding.
 *
 * SHOULD be used with {@see RouteFinder}.
 *
 * @api
 */
final readonly class RouteFinderSettings
{
    use HasRootDir;

    /**
     * Directory to scan for routes.
     *
     * @var non-empty-string
     */
    public string $routeDir;

    /**
     * Base URL of application.
     *
     * @var non-empty-string|null
     */
    public ?string $baseUrl;

    public function __construct(?string $routeDir = null, ?string $baseUrl = null)
    {
        $this->routeDir = $routeDir ? non_empty_string()->assert($routeDir) : $this->rootPath('src');
        $this->baseUrl = nullable(non_empty_string())->assert($baseUrl);
    }
}
