<?php

declare(strict_types=1);

namespace Joust\Tests;

use Errata\Middleware\ProblemMiddleware;
use Joust\RouteHandler;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\Fixture\TraceMiddleware;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteHandler::class)]
final class RouteHandlerTest extends TestCase
{
    public function testItHasNoMiddlewareByDefault(): void
    {
        $handler = new RouteHandler(TestHandler::class);

        $this->assertSame(TestHandler::class, $handler->name);
        $this->assertSame([], $handler->middleware);
    }

    public function testItAcceptsMiddleware(): void
    {
        $handler = new RouteHandler(TestHandler::class, [TraceMiddleware::class, ProblemMiddleware::class]);

        $this->assertSame([TraceMiddleware::class, ProblemMiddleware::class], $handler->middleware);
    }

    public function testWithMiddlewareAppendsToACopy(): void
    {
        $handler = new RouteHandler(TestHandler::class, [TraceMiddleware::class]);

        $copy = $handler->withMiddleware(ProblemMiddleware::class);

        $this->assertSame([TraceMiddleware::class], $handler->middleware);
        $this->assertSame(TestHandler::class, $copy->name);
        $this->assertSame([TraceMiddleware::class, ProblemMiddleware::class], $copy->middleware);
    }
}
