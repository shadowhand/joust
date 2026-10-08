<?php

declare(strict_types=1);

namespace Joust\Demo\Routes;

use Errata\Http\Client\NotFound;
use Joust\Attribute\AsRoute;
use Joust\Demo\TodoStore;
use Joust\Method;
use Joust\Response\JsonResponseFactory;
use Joust\RouteMatch;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[AsRoute(Method::Patch, '/todos/:id/complete')]
final readonly class CompleteTodo implements RequestHandlerInterface
{
    public function __construct(
        private TodoStore $store,
        private JsonResponseFactory $json,
    ) {}

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = RouteMatch::fromRequest($request)->result->path->integer('id');

        $todo = null === $id ? null : $this->store->complete($id);

        return null === $todo
            ? $this->json->problem(new NotFound(detail: "Todo '{$id}' does not exist."))
            : $this->json->respond(data: $todo);
    }
}
