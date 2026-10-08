<?php

declare(strict_types=1);

namespace Joust\Tests\Response;

use Errata\Http\Client\NotFound;
use Joust\Response\JsonResponseFactory;
use Joust\Response\JsonResponseSettings;
use Joust\Tests\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(JsonResponseFactory::class)]
final class JsonResponseFactoryTest extends TestCase
{
    public function testRespondUsesDefaults(): void
    {
        $factory = new Psr17Factory();
        $json = new JsonResponseFactory($factory, $factory);

        $response = $json->respond();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame('[]', (string) $response->getBody());
    }

    public function testRespondEncodesStatusAndData(): void
    {
        $factory = new Psr17Factory();
        $json = new JsonResponseFactory($factory, $factory);

        $response = $json->respond(404, ['error' => 'NotFound']);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('{"error":"NotFound"}', (string) $response->getBody());
    }

    public function testRespondUsesExplicitContentType(): void
    {
        $factory = new Psr17Factory();
        $json = new JsonResponseFactory($factory, $factory);

        $response = $json->respond(201, ['a' => 1], 'application/vnd.api+json');

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('application/vnd.api+json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame('{"a":1}', (string) $response->getBody());
    }

    public function testRespondKeepsContentTypeThatAlreadyDeclaresCharset(): void
    {
        $factory = new Psr17Factory();
        $json = new JsonResponseFactory($factory, $factory);

        $response = $json->respond(200, [], 'text/plain; charset=iso-8859-1');

        $this->assertSame('text/plain; charset=iso-8859-1', $response->getHeaderLine('Content-Type'));
    }

    public function testRespondUsesCustomSettings(): void
    {
        $factory = new Psr17Factory();
        $settings = new JsonResponseSettings(contentType: 'text/plain', charset: 'iso-8859-1', pretty: true);
        $json = new JsonResponseFactory($factory, $factory, $settings);

        $response = $json->respond(201, ['a' => 1]);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('text/plain; charset=iso-8859-1', $response->getHeaderLine('Content-Type'));
        $this->assertSame("{\n    \"a\": 1\n}", (string) $response->getBody());
    }

    public function testProblemRendersApiProblem(): void
    {
        $factory = new Psr17Factory();
        $json = new JsonResponseFactory($factory, $factory);

        $response = $json->problem(new NotFound());

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('application/problem+json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertSame('{"title":"Not Found","status":404}', (string) $response->getBody());
    }
}
