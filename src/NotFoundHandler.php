<?php

declare(strict_types=1);

namespace Joust;

use Errata\Http\Client\NotFound;
use Joust\Response\JsonResponseFactory;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Responds with a not found problem. Use it as the fallback handler of an `App`.
 *
 * @api
 */
final readonly class NotFoundHandler implements RequestHandlerInterface
{
    public function __construct(
        private JsonResponseFactory $jsonResponseFactory,
    ) {}

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->jsonResponseFactory->problem(new NotFound());
    }
}
