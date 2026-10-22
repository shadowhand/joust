<?php

declare(strict_types=1);

namespace Joust\Tests\Fixture;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * A second trace middleware, so that tests can declare two distinct middleware classes.
 */
final readonly class OtherTraceMiddleware implements MiddlewareInterface
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
