<?php

declare(strict_types=1);

namespace Joust\Attribute;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

use function Psl\Type\class_string;

/**
 * Adds middleware to the routes of a request handler. Middleware runs in the order it is declared.
 *
 * @api
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Middleware
{
    /**
     * @phpstan-assert class-string<MiddlewareInterface> $name
     */
    public function __construct(
        public string $name,
    ) {
        class_string(MiddlewareInterface::class)->assert($this->name);
    }
}
