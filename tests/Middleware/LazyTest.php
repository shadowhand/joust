<?php

declare(strict_types=1);

namespace Joust\Tests\Middleware;

use Joust\Middleware\Lazy;
use Joust\Tests\Fixture\TestContainer;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\Fixture\TraceMiddleware;
use Joust\Tests\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use TypeError;

#[CoversClass(Lazy::class)]
final class LazyTest extends TestCase
{
    public function testItResolvesMiddlewareFromTheContainer(): void
    {
        $container = new TestContainer([TraceMiddleware::class => new TraceMiddleware('lazy')]);
        $handler = new TestHandler(new Psr17Factory());

        $response = new Lazy($container, TraceMiddleware::class)->process(new ServerRequest('GET', '/'), $handler);

        $this->assertSame(['lazy'], $response->getHeader('X-Trace'));
    }

    public function testItFailsWhenTheContainerEntryIsNotMiddleware(): void
    {
        $factory = new Psr17Factory();
        $container = new TestContainer([TraceMiddleware::class => new TestHandler($factory)]);

        $this->expectException(TypeError::class);

        new Lazy($container, TraceMiddleware::class)->process(new ServerRequest('GET', '/'), new TestHandler($factory));
    }
}
