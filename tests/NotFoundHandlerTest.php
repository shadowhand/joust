<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\NotFoundHandler;
use Joust\Response\JsonResponseFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NotFoundHandler::class)]
final class NotFoundHandlerTest extends TestCase
{
    public function testItRespondsWithNotFoundProblem(): void
    {
        $factory = new Psr17Factory();

        $response = new NotFoundHandler(new JsonResponseFactory($factory, $factory))->handle(new ServerRequest(
            'GET',
            '/',
        ));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/problem+json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame('{"title":"Not Found","status":404}', (string) $response->getBody());
    }
}
