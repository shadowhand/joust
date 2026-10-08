<?php

declare(strict_types=1);

namespace Joust\Response;

use Errata\Problem;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

use function Psl\Json\encode;
use function Psl\Str\Byte\contains;

/**
 * @api
 */
final readonly class JsonResponseFactory
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
        private JsonResponseSettings $settings = new JsonResponseSettings(),
    ) {}

    public function problem(Problem $problem): ResponseInterface
    {
        return $this->respond($problem->status ?? 500, $problem, 'application/problem+json');
    }

    public function respond(int $status = 200, mixed $data = [], ?string $type = null): ResponseInterface
    {
        $type ??= $this->settings->contentType;

        if (!contains($type, needle: 'charset')) {
            $type = "{$type}; charset={$this->settings->charset}";
        }

        $response = $this->responseFactory->createResponse($status)->withHeader('Content-Type', $type);
        $stream = $this->streamFactory->createStream(encode($data, $this->settings->pretty, $this->settings->flags));

        return $response->withBody($stream);
    }
}
