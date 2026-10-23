<?php

declare(strict_types=1);

namespace Joust\Attribute;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

use function Psl\Type\class_string;

/**
 * Apply a middleware to a request handler.
 *
 * The middleware will be invoked before the request handler.
 *
 * SHOULD be used in combination with {@see AsRoute}.
 *
 * @api
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class Middleware
{
    /**
     * @assert class-string<MiddlewareInterface> $name
     */
    public function __construct(
        /** @var class-string<MiddlewareInterface> */
        public string $name,
    ) {
        class_string(MiddlewareInterface::class)->assert($this->name);
    }
}
