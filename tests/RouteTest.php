<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\Method;
use Joust\Route;
use Joust\RouteHandler;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\Fixture\TraceMiddleware;
use League\Uri\UrlPattern;
use League\Uri\UrlPattern\MatchMode;
use League\Uri\UrlPatternBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Psl\Type\Exception\CoercionException;

use function Psl\Json\decode;
use function Psl\Json\encode;

#[CoversClass(Route::class)]
final class RouteTest extends TestCase
{
    public function testExposesItsParts(): void
    {
        $pattern = UrlPattern::from('/users/:id', baseUrl: 'https://example.com');
        $handler = new RouteHandler(TestHandler::class);
        $route = new Route(Method::Get, $pattern, $handler);

        $this->assertSame(Method::Get, $route->method);
        $this->assertSame($pattern, $route->pattern);
        $this->assertSame($handler, $route->handler);
    }

    public function testItSerializesToJson(): void
    {
        $route = new Route(
            Method::Post,
            new UrlPatternBuilder(path: '/users/:id')->build(matchMode: MatchMode::CaseInsensitive),
            new RouteHandler(TestHandler::class, [TraceMiddleware::class]),
        );

        $this->assertSame(
            [
                'method' => 'POST',
                'pattern' => [
                    'scheme' => '*',
                    'username' => '*',
                    // @mago-expect lint:no-literal-password -- A URL-pattern wildcard, not a credential.
                    'password' => '*',
                    'host' => '*',
                    'port' => '*',
                    'path' => '/users/:id',
                    'query' => '*',
                    'fragment' => '*',
                ],
                'ignoreCase' => true,
                'handler' => [
                    'name' => TestHandler::class,
                    'middleware' => [TraceMiddleware::class],
                ],
            ],
            decode(encode($route)),
        );
    }

    #[DataProvider('patterns')]
    public function testItReconstructsFromJson(UrlPattern $pattern, string $url): void
    {
        $route = new Route(Method::Patch, $pattern, new RouteHandler(TestHandler::class, [TraceMiddleware::class]));

        $restored = Route::type()->coerce(decode(encode($route)));

        $this->assertEquals($route, $restored);
        $this->assertSame($pattern->hasVariable(), $restored->pattern->hasVariable());
        $this->assertTrue($restored->pattern->match($url));
        $this->assertEquals($pattern->extract($url), $restored->pattern->extract($url));
    }

    /**
     * @return iterable<string, array{UrlPattern, string}>
     */
    public static function patterns(): iterable
    {
        yield 'unconstrained' => [new UrlPatternBuilder()->build(), 'http://example.com:8080/anything'];
        yield 'relative' => [new UrlPatternBuilder(path: '/users/:id')->build(), 'https://example.com:8443/users/42'];
        yield 'fixed path' => [new UrlPatternBuilder(path: '/users')->build(), 'https://example.com:8443/users'];
        yield 'explicit wildcard' => [new UrlPatternBuilder(path: '*')->build(), 'https://example.com/anything'];
        yield 'fixed host with empty port' => [
            new UrlPatternBuilder(host: 'example.com')->build(),
            'https://example.com/anything',
        ];
        yield 'fixed host with wildcard port' => [
            new UrlPatternBuilder(host: 'example.com', port: '*')->build(),
            'https://example.com:8443/anything',
        ];
        yield 'base URL' => [
            new UrlPatternBuilder(path: 'users/:id', baseUrl: 'https://example.com/api/')->build(),
            'https://example.com/api/users/42',
        ];
        yield 'all components and case insensitive' => [
            new UrlPatternBuilder(
                scheme: 'https',
                username: ':user',
                // @mago-expect lint:no-literal-password -- A URL-pattern parameter, not a credential.
                password: ':password',
                host: ':subdomain.example.com',
                port: '8443',
                path: '/Users/:id(\\d+)',
                query: 'q=:query',
                fragment: ':section',
            )->build(matchMode: MatchMode::CaseInsensitive),
            'https://alice:secret@api.example.com:8443/users/42?q=active#details',
        ];
    }

    public function testTypeAcceptsAnExistingRoute(): void
    {
        $route = $this->createRoute();

        $this->assertSame($route, Route::type()->coerce($route));
        $this->assertSame($route, Route::type()->assert($route));
    }

    #[DataProvider('invalidData')]
    public function testTypeRejectsInvalidData(string $field, mixed $value): void
    {
        $data = $this->createRoute()->jsonSerialize();
        $data[$field] = $value;

        $this->expectException(CoercionException::class);

        Route::type()->coerce($data);
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function invalidData(): iterable
    {
        yield 'unknown method' => ['method', 'INVALID'];
        yield 'incomplete pattern' => ['pattern', ['path' => '/users']];
        yield 'invalid case sensitivity' => ['ignoreCase', []];
        yield 'invalid handler' => ['handler', ['name' => Route::class, 'middleware' => []]];
        yield 'invalid middleware' => ['handler', ['name' => TestHandler::class, 'middleware' => [TestHandler::class]]];
    }
}
