<?php

declare(strict_types=1);

namespace Joust\Demo\Routes;

use Joust\Attribute\AsRoute;
use Joust\Method;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

#[AsRoute(Method::Get, '/crash')]
final readonly class Crash implements RequestHandlerInterface
{
    #[Override]
    public function handle(ServerRequestInterface $request): never
    {
        throw new RuntimeException('Demo crash! Watch errata turn this into a problem response.');
    }
}
