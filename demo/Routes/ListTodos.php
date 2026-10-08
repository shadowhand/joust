<?php

declare(strict_types=1);

namespace Joust\Demo\Routes;

use Joust\Attribute\AsRoute;
use Joust\Demo\TodoStore;
use Joust\Method;
use Joust\Response\JsonResponseFactory;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[AsRoute(Method::Get, '/todos')]
final readonly class ListTodos implements RequestHandlerInterface
{
    public function __construct(
        private TodoStore $store,
        private JsonResponseFactory $json,
    ) {}

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->json->respond(data: $this->store->all());
    }
}
