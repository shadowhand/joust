<?php

declare(strict_types=1);

namespace Joust\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Sends a response to the client.
 *
 * @api
 */
interface Emitter
{
    public function emit(ResponseInterface $response): void;
}
