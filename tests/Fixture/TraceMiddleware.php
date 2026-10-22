<?php

declare(strict_types=1);

namespace Joust\Tests\Fixture;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Adds its name to the X-Trace header of the request it passes on and of the response it returns.
 */
final readonly class TraceMiddleware implements MiddlewareInterface
{
    public function __construct(
        private string $name,
    ) {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request->withAddedHeader('X-Trace', $this->name));

        return $response->withAddedHeader('X-Trace', $this->name);
    }
}
