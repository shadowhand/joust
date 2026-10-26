<?php

declare(strict_types=1);

namespace Joust\Tests\Fixture\Routes;

use Errata\Middleware\ProblemMiddleware;
use Joust\Attribute\AsRoute;
use Joust\Attribute\Middleware;
use Joust\Method;
use Joust\Tests\Fixture\TraceMiddleware;
use LogicException;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[AsRoute(Method::Get, path: '/users/:id')]
#[AsRoute(Method::Delete, path: '/users/:id')]
#[Middleware(TraceMiddleware::class)]
#[Middleware(ProblemMiddleware::class)]
final class UserActions implements RequestHandlerInterface
{
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new LogicException('Not implemented.');
    }
}
