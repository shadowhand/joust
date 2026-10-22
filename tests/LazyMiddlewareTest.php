<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\LazyMiddleware;
use Joust\Tests\Fixture\TestContainer;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\Fixture\TraceMiddleware;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use Psl\Type\Exception\AssertException;

#[CoversClass(LazyMiddleware::class)]
final class LazyMiddlewareTest extends TestCase
{
    public function testItResolvesMiddlewareFromTheContainer(): void
    {
        $container = new TestContainer([TraceMiddleware::class => new TraceMiddleware('lazy')]);
        $handler = new TestHandler(new Psr17Factory());

        $response = new LazyMiddleware($container, TraceMiddleware::class)->process(
            new ServerRequest('GET', '/'),
            $handler,
        );

        $this->assertSame(['lazy'], $response->getHeader('X-Trace'));
    }

    public function testItRejectsClassesThatAreNotMiddleware(): void
    {
        $this->expectException(AssertException::class);

        new LazyMiddleware(new TestContainer(), TestHandler::class);
    }

    public function testItRejectsContainerEntriesThatAreNotMiddleware(): void
    {
        $factory = new Psr17Factory();
        $container = new TestContainer([TraceMiddleware::class => new TestHandler($factory)]);

        $this->expectException(AssertException::class);

        new LazyMiddleware($container, TraceMiddleware::class)->process(
            new ServerRequest('GET', '/'),
            new TestHandler($factory),
        );
    }
}
