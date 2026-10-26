<?php

declare(strict_types=1);

namespace Joust\Tests\Attribute;

use Joust\Attribute\AsRoute;
use Joust\Method;
use Joust\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ValueError;

use function Psl\Str\Byte\reverse;

#[CoversClass(AsRoute::class)]
final class AsRouteTest extends TestCase
{
    public function testDefaults(): void
    {
        $route = new AsRoute();

        $this->assertSame(Method::Get, $route->method);
        $this->assertNull($route->baseUrl);
        $this->assertNull($route->scheme);
        $this->assertNull($route->username);
        $this->assertNull($route->password);
        $this->assertNull($route->host);
        $this->assertNull($route->path);
        $this->assertNull($route->query);
        $this->assertNull($route->fragment);
    }

    public function testExposesUrlComponents(): void
    {
        $password = reverse('terces');

        $route = new AsRoute(
            Method::Post,
            baseUrl: 'https://api.example.com',
            scheme: 'https',
            username: 'user',
            password: $password,
            host: 'example.com',
            path: '/posts',
            query: 'page=:page',
            fragment: 'results',
        );

        $this->assertSame(Method::Post, $route->method);
        $this->assertSame('https://api.example.com', $route->baseUrl);
        $this->assertSame('https', $route->scheme);
        $this->assertSame('user', $route->username);
        $this->assertSame($password, $route->password);
        $this->assertSame('example.com', $route->host);
        $this->assertSame('/posts', $route->path);
        $this->assertSame('page=:page', $route->query);
        $this->assertSame('results', $route->fragment);
    }

    public function testNormalizesStringMethod(): void
    {
        $route = new AsRoute('PATCH');

        $this->assertSame(Method::Patch, $route->method);
    }

    public function testRejectsUnknownMethod(): void
    {
        $this->expectException(ValueError::class);

        new AsRoute('FLY');
    }
}
