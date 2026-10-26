<?php

declare(strict_types=1);

namespace Joust\Tests\Handler;

use Joust\Handler\PassHandler;
use Joust\Handler\PipelineHandler;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\Fixture\TraceMiddleware;
use Joust\Tests\TestCase;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PipelineHandler::class)]
#[CoversClass(PassHandler::class)]
final class PipelineHandlerTest extends TestCase
{
    public function testItRunsMiddlewareInOrderBeforeTheHandler(): void
    {
        $handler = new TestHandler(new Psr17Factory());

        $pipeline = new PipelineHandler([new TraceMiddleware('outer'), new TraceMiddleware('inner')], $handler);

        $response = $pipeline->handle(new ServerRequest('GET', '/'));

        $this->assertNotNull($handler->request);
        $this->assertSame(['outer', 'inner'], $handler->request->getHeader('X-Trace'));
        $this->assertSame(['inner', 'outer'], $response->getHeader('X-Trace'));
    }

    public function testItCallsTheHandlerWithoutMiddleware(): void
    {
        $handler = new TestHandler(new Psr17Factory());

        $response = new PipelineHandler([], $handler)->handle(new ServerRequest('GET', '/'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($handler->request);
    }
}
