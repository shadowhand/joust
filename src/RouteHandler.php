<?php

declare(strict_types=1);

namespace Joust;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Psl\Type\class_string;
use function Psl\Type\vec;

/**
 * A request handler reference.
 *
 * MAY include middleware to be processed before the handler.
 *
 * @api
 */
final readonly class RouteHandler
{
    /**
     * @assert class-string<RequestHandlerInterface> $name
     * @assert list<class-string<MiddlewareInterface>> $middleware
     */
    public function __construct(
        /** @var class-string<RequestHandlerInterface> */
        public string $name,
        /** @var list<class-string<MiddlewareInterface>> */
        public array $middleware = [],
    ) {
        class_string(RequestHandlerInterface::class)->assert($this->name);
        vec(class_string(MiddlewareInterface::class))->assert($this->middleware);
    }

    /**
     * @param class-string<MiddlewareInterface> ...$middleware
     */
    public function withMiddleware(string ...$middleware): self
    {
        return new self($this->name, [...$this->middleware, ...$middleware]);
    }
}
