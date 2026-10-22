<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\MiddlewareHandler;
use Joust\Pipeline;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\Fixture\TraceMiddleware;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Pipeline::class)]
#[CoversClass(MiddlewareHandler::class)]
final class PipelineTest extends TestCase
{
    public function testItRunsMiddlewareInOrderBeforeTheHandler(): void
    {
        $handler = new TestHandler(new Psr17Factory());

        $pipeline = new Pipeline([new TraceMiddleware('outer'), new TraceMiddleware('inner')], $handler);

        $response = $pipeline->handle(new ServerRequest('GET', '/'));

        $this->assertNotNull($handler->request);
        $this->assertSame(['outer', 'inner'], $handler->request->getHeader('X-Trace'));
        $this->assertSame(['inner', 'outer'], $response->getHeader('X-Trace'));
    }

    public function testItCallsTheHandlerWithoutMiddleware(): void
    {
        $handler = new TestHandler(new Psr17Factory());

        $response = new Pipeline([], $handler)->handle(new ServerRequest('GET', '/'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($handler->request);
    }
}
