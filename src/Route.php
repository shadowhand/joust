<?php

declare(strict_types=1);

namespace Joust;

use League\Uri\UrlPattern;

/**
 * A request handler route.
 *
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
