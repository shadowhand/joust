<?php

declare(strict_types=1);

namespace Joust\Tests\Fixture;

use Joust\Response\Emitter;
use Override;
use Psr\Http\Message\ResponseInterface;

/**
 * Records the response it is asked to emit instead of sending it.
 */
final class TestEmitter implements Emitter
{
    public ?ResponseInterface $response = null;

    #[Override]
    public function emit(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
