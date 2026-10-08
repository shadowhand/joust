<?php

declare(strict_types=1);

namespace Joust\Demo;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Psl\Vec\reverse;

final readonly class Pipeline implements RequestHandlerInterface
{
    /**
     * @param list<MiddlewareInterface> $middleware
     */
    public function __construct(
        private array $middleware,
        private RequestHandlerInterface $handler,
    ) {}

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = $this->handler;

        foreach (reverse($this->middleware) as $middleware) {
            $handler = new MiddlewareHandler($middleware, $handler);
        }

        return $handler->handle($request);
    }
}
