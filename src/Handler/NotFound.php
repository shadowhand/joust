<?php

declare(strict_types=1);

namespace Joust\Handler;

use Errata\Http\Client\NotFound as Problem;
use Joust\Response\JsonResponseFactory;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Always responds with a 404 Not Found.
 *
 * @api
 */
final readonly class NotFound implements RequestHandlerInterface
{
    public function __construct(
        private JsonResponseFactory $jsonResponseFactory,
    ) {}

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->jsonResponseFactory->problem(new Problem());
    }
}
