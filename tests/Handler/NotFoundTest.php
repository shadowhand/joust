<?php

declare(strict_types=1);

namespace Joust\Tests\Handler;

use Joust\Handler\NotFound;
use Joust\Response\JsonResponseFactory;
use Joust\Tests\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NotFound::class)]
final class NotFoundTest extends TestCase
{
    public function testItRespondsWithNotFoundProblem(): void
    {
        $factory = new Psr17Factory();

        $response = new NotFound(new JsonResponseFactory($factory, $factory))->handle(new ServerRequest('GET', '/'));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/problem+json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame('{"title":"Not Found","status":404}', (string) $response->getBody());
    }
}
