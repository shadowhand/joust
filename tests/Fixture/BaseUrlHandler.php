<?php

declare(strict_types=1);

namespace Joust\Tests\Fixture;

use Joust\Attribute\AsRoute;
use Joust\Method;
use LogicException;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Declares its own base URL, so it ignores the one configured on the reader.
 */
#[AsRoute(Method::Get, baseUrl: 'https://api.example.com', path: '/status')]
final class BaseUrlHandler implements RequestHandlerInterface
{
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new LogicException('Not implemented.');
    }
}
