<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\RouteMatch;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use Psl\Type\Exception\AssertException;

#[CoversClass(RouteMatch::class)]
final class RouteResultTest extends TestCase
{
    public function testInjectAddsResultAsRequestAttribute(): void
    {
        $match = $this->createRouteMatch();

        $request = new ServerRequest('GET', '/')->withAttribute($match::class, $match);

        $this->assertSame($match, $request->getAttribute(RouteMatch::class));
        $this->assertSame($match, RouteMatch::fromRequest($request));
    }

    public function testFromRequestFailsWhenAttributeIsMissing(): void
    {
        $this->expectException(AssertException::class);

        RouteMatch::fromRequest(new ServerRequest('GET', '/'));
    }
}
