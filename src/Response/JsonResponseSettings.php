<?php

declare(strict_types=1);

namespace Joust\Response;

use function Psl\Type\non_empty_string;
use function Psl\Type\nullable;
use function Psl\Type\uint;

/**
 * Provides settings for JSON responses.
 *
 * SHOULD be used with {@see JsonResponseFactory}.
 *
 * @api
 */
final readonly class JsonResponseSettings
{
    /**
     * JSON content type.
     *
     * @var non-empty-string
     */
    public string $contentType;

    /**
     * Content type character set.
     *
     * @var non-empty-string
     */
    public string $charset;

    /**
     * JSON encoding flags.
     *
     * @var int<0,max>
     */
    public int $flags;

    /**
     * Enable pretty JSON responses?
     */
    public bool $pretty;

    public function __construct(
        ?string $contentType = null,
        ?string $charset = null,
        ?bool $pretty = null,
        ?int $flags = null,
    ) {
        $this->contentType = nullable(non_empty_string())->assert($contentType) ?? 'application/json';
        $this->charset = nullable(non_empty_string())->assert($charset) ?? 'utf-8';
        $this->flags = nullable(uint())->assert($flags) ?? 0;
        $this->pretty = $pretty ?? false;
    }
}
