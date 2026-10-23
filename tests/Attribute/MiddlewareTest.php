<?php

declare(strict_types=1);

namespace Joust\Tests\Attribute;

use Joust\Attribute\Middleware;
use Joust\Tests\Fixture\TraceMiddleware;
use Joust\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Middleware::class)]
final class MiddlewareTest extends TestCase
{
    public function testItHoldsTheMiddlewareName(): void
    {
        $this->assertSame(TraceMiddleware::class, new Middleware(TraceMiddleware::class)->name);
    }
}
