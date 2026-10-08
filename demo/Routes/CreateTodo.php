<?php

declare(strict_types=1);

namespace Joust\Demo\Routes;

use Errata\Http\Client\UnprocessableContent;
use Joust\Attribute\AsRoute;
use Joust\Demo\TodoStore;
use Joust\Method;
use Joust\Response\JsonResponseFactory;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function is_array;
use function is_string;
use function Psl\Str\trim;

#[AsRoute(Method::Post, '/todos')]
final readonly class CreateTodo implements RequestHandlerInterface
{
    public function __construct(
        private TodoStore $store,
        private JsonResponseFactory $json,
    ) {}

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $body = $request->getParsedBody();
        $title = is_array($body) && is_string($body['title'] ?? null) ? trim($body['title']) : '';

        if ('' === $title) {
            return $this->json->problem(new UnprocessableContent(detail: 'A todo title is required.'))->withStatus(
                422,
                'Unprocessable Content',
            );
        }

        return $this->json->respond(201, $this->store->create($title));
    }
}
