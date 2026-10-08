<?php

declare(strict_types=1);

namespace Joust;

use League\Uri\UrlPattern;

/**
 * @api
 */
final readonly class Route
{
    public function __construct(
        public Method $method,
        public UrlPattern $pattern,
        public RouteHandler $handler,
    ) {}
}
