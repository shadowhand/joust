<?php

declare(strict_types=1);

namespace Joust\Tests\Attribute;

use Joust\Attribute\Middleware;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\Fixture\TraceMiddleware;
use Joust\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psl\Type\Exception\AssertException;

#[CoversClass(Middleware::class)]
final class MiddlewareTest extends TestCase
{
    public function testItHoldsTheMiddlewareName(): void
    {
        $this->assertSame(TraceMiddleware::class, new Middleware(TraceMiddleware::class)->name);
    }

    public function testItRejectsClassesThatAreNotMiddleware(): void
    {
        $this->expectException(AssertException::class);

        new Middleware(TestHandler::class);
    }
}
