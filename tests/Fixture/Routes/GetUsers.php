<?php

declare(strict_types=1);

namespace Joust\Tests\Fixture\Routes;

use Joust\Attribute\AsRoute;
use Joust\Method;
use LogicException;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[AsRoute(Method::Get, path: '/users')]
final class GetUsers implements RequestHandlerInterface
{
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new LogicException('Not implemented.');
    }
}
