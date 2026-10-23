<?php

declare(strict_types=1);

namespace Joust\Handler;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Psl\Type\instance_of;
use function Psl\Type\vec;
use function Psl\Vec\reverse;

/**
 * Composes multiple middleware into a single request handler.
 *
 * @internal
 */
final readonly class Pipeline implements RequestHandlerInterface
{
    /**
     * @assert list<MiddlewareInterface> $middleware
     */
    public function __construct(
        /** @var list<MiddlewareInterface> $middleware */
        private array $middleware,
        private RequestHandlerInterface $handler,
    ) {
        vec(instance_of(MiddlewareInterface::class))->assert($this->middleware);
    }

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = $this->handler;

        foreach (reverse($this->middleware) as $middleware) {
            $handler = new Pass($middleware, $handler);
        }

        return $handler->handle($request);
    }
}
