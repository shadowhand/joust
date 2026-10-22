<?php

declare(strict_types=1);

namespace Joust;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Psl\Type\class_string;
use function Psl\Type\vec;
use function Psl\Vec\values;

/**
 * @api
 */
final readonly class RouteHandler
{
    /** @var list<class-string<MiddlewareInterface>> */
    public array $middleware;

    /**
     * @phpstan-assert class-string<RequestHandlerInterface> $name
     */
    public function __construct(
        public string $name,
        string ...$middleware,
    ) {
        class_string(RequestHandlerInterface::class)->assert($this->name);

        $this->middleware = vec(class_string(MiddlewareInterface::class))->assert(values($middleware));
    }

    /**
     * Returns a copy of this handler with middleware appended to its list.
     */
    public function withMiddleware(string ...$middleware): self
    {
        return new self($this->name, ...$this->middleware, ...$middleware);
    }
}
