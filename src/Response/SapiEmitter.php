<?php

declare(strict_types=1);

namespace Joust\Response;

use Override;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

use function headers_sent;
use function Http\Response\send;
use function ob_get_length;
use function ob_get_level;

/**
 * Sends a response through the PHP SAPI, failing when headers or output have already been sent.
 *
 * @api
 */
final readonly class SapiEmitter implements Emitter
{
    #[Override]
    public function emit(ResponseInterface $response): void
    {
        $file = '';
        $line = 0;

        if (headers_sent($file, $line)) {
            throw new RuntimeException("Unable to emit response: headers already sent in {$file} on line {$line}.");
        }

        if (ob_get_level() > 0 && (int) ob_get_length() > 0) {
            throw new RuntimeException('Unable to emit response: output has already been buffered.');
        }

        send($response);
    }
}
