<?php

declare(strict_types=1);

namespace Joust\Response;

/**
 * Provides options for JSON responses.
 *
 * SHOULD be used with {@see JsonResponseFactory}.
 *
 * @api
 */
final readonly class JsonResponseSettings
{
    public function __construct(
        /** @var non-empty-string */
        public string $contentType = 'application/json',
        /** @var non-empty-string */
        public string $charset = 'utf-8',
        public bool $pretty = false,
        /** @var int<0,max> */
        public int $flags = 0,
    ) {}
}
