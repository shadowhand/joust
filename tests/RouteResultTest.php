<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\RouteResult;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use Psl\Type\Exception\AssertException;

#[CoversClass(RouteResult::class)]
final class RouteResultTest extends TestCase
{
    public function testInjectAddsResultAsRequestAttribute(): void
    {
        $result = $this->createRouteResult($this->createRoute());

        $injected = $result->inject(new ServerRequest('GET', '/'));

        $this->assertSame($result, $injected->getAttribute(RouteResult::class));
        $this->assertSame($result, RouteResult::fromRequest($injected));
    }

    public function testFromRequestFailsWhenAttributeIsMissing(): void
    {
        $this->expectException(AssertException::class);

        RouteResult::fromRequest(new ServerRequest('GET', '/'));
    }
}
