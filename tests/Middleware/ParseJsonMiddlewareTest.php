<?php

declare(strict_types=1);

namespace Joust\Tests\Middleware;

use Joust\Middleware\ParseJsonMiddleware;
use Joust\Response\JsonResponseFactory;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\ServerRequestInterface;

use function Psl\Json\decode;

#[CoversClass(ParseJsonMiddleware::class)]
final class ParseJsonMiddlewareTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function jsonContentTypeProvider(): iterable
    {
        yield 'application/json' => ['application/json'];
        yield 'application/json with charset' => ['application/json; charset=utf-8'];
        yield 'vendor type' => ['application/vnd.api+json'];
        yield 'vendor type with charset' => ['application/vnd.api+json; charset=utf-8'];
        yield 'vendor type with mixed case' => ['Application/VND.API+JSON; Charset=UTF-8'];
    }

    #[DataProvider('jsonContentTypeProvider')]
    public function testItParsesJsonMediaTypes(string $contentType): void
    {
        $handler = new TestHandler(new Psr17Factory());

        $response = $this->middleware()->process($this->request('{"title":"Write tests"}', $contentType), $handler);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['title' => 'Write tests'], $handler->request?->getParsedBody());
    }

    public function testItParsesAJsonArrayBody(): void
    {
        $handler = new TestHandler(new Psr17Factory());

        $response = $this->middleware()->process($this->request('[{"title":"Write tests"}]'), $handler);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([['title' => 'Write tests']], $handler->request?->getParsedBody());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function nonJsonContentTypeProvider(): iterable
    {
        yield 'text' => ['text/plain'];
        yield 'text json' => ['text/json'];
        yield 'javascript' => ['application/javascript'];
        yield 'xml' => ['application/xml; charset=utf-8'];
    }

    #[DataProvider('nonJsonContentTypeProvider')]
    public function testItIgnoresNonJsonRequests(string $contentType): void
    {
        $handler = new TestHandler(new Psr17Factory());

        $response = $this->middleware()->process($this->request('{"title":"Write tests"}', $contentType), $handler);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull($handler->request?->getParsedBody());
    }

    public function testItIgnoresAnEmptyJsonBody(): void
    {
        $handler = new TestHandler(new Psr17Factory());

        $response = $this->middleware()->process($this->request(''), $handler);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull($handler->request?->getParsedBody());
    }

    public function testItRejectsInvalidJson(): void
    {
        $handler = new TestHandler(new Psr17Factory());

        $response = $this->middleware()->process($this->request('{"title":'), $handler);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('application/problem+json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        $this->assertNull($handler->request);
        $this->assertSame(
            'The request body is not a JSON object or array.',
            decode((string) $response->getBody())['detail'] ?? null,
        );
    }

    public function testItRejectsJsonThatIsNotAnObjectOrArray(): void
    {
        $handler = new TestHandler(new Psr17Factory());

        $response = $this->middleware()->process($this->request('"Write tests"'), $handler);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertNull($handler->request);
    }

    private function middleware(): ParseJsonMiddleware
    {
        $factory = new Psr17Factory();

        return new ParseJsonMiddleware(new JsonResponseFactory($factory, $factory));
    }

    private function request(
        string $body,
        string $contentType = 'application/json; charset=utf-8',
    ): ServerRequestInterface {
        return new ServerRequest('POST', '/todos', ['Content-Type' => $contentType], $body);
    }
}
