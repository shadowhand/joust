<?php

declare(strict_types=1);

namespace Joust\Tests\Response;

use Joust\Response\SapiEmitter;
use Joust\Tests\TestCase;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use RuntimeException;

#[CoversClass(SapiEmitter::class)]
final class SapiEmitterTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testItSendsTheResponse(): void
    {
        $this->expectOutputString('{"ok":true}');

        new SapiEmitter()->emit(new Response(201, ['Content-Type' => 'application/json'], '{"ok":true}'));
    }

    public function testItFailsWhenOutputIsBuffered(): void
    {
        // @mago-expect lint:psl-output
        echo 'early';

        $this->expectOutputString('early');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/^Unable to emit response: output has already been buffered\.$/');

        new SapiEmitter()->emit(new Response());
    }
}
