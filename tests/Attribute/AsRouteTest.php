<?php

declare(strict_types=1);

namespace Joust\Tests\Attribute;

use Joust\Attribute\AsRoute;
use Joust\Method;
use Joust\Tests\TestCase;
use League\Uri\UrlPattern;
use PHPUnit\Framework\Attributes\CoversClass;
use ValueError;

#[CoversClass(AsRoute::class)]
final class AsRouteTest extends TestCase
{
    public function testDefaults(): void
    {
        $route = new AsRoute();

        $this->assertSame(Method::Get, $route->method);
        $this->assertSame('/', $route->pattern->path);
    }

    public function testNormalizesMethodAndTemplate(): void
    {
        $route = new AsRoute(Method::Post, '/posts');

        $this->assertSame(Method::Post, $route->method);
        $this->assertSame('/posts', $route->pattern->path);
    }

    public function testNormalizesStringMethod(): void
    {
        $route = new AsRoute('PATCH');

        $this->assertSame(Method::Patch, $route->method);
    }

    public function testAcceptsUriTemplateInstance(): void
    {
        $pattern = UrlPattern::from('/users/{id}');

        $route = new AsRoute(Method::Get, $pattern);

        $this->assertSame($pattern, $route->pattern);
    }

    public function testRejectsUnknownMethod(): void
    {
        $this->expectException(ValueError::class);

        new AsRoute('FLY');
    }
}
