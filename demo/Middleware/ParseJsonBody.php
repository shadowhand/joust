<?php

declare(strict_types=1);

namespace Joust\Demo\Middleware;

use Errata\Http\Client\BadRequest;
use Joust\Response\JsonResponseFactory;
use Override;
use Psl\Json\Exception\DecodeException;
use Psl\Type;
use Psl\Type\Exception\CoercionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Psl\Json\decode;
use function Psl\Str\Byte\contains;

/**
 * Decodes an application/json request body onto the request, answering with a
 * problem response when the payload cannot be decoded.
 */
final readonly class ParseJsonBody implements MiddlewareInterface
{
    public function __construct(
        private JsonResponseFactory $json,
    ) {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!contains($request->getHeaderLine('Content-Type'), needle: 'application/json')) {
            return $handler->handle($request);
        }

        $body = (string) $request->getBody();

        if ('' === $body) {
            return $handler->handle($request);
        }

        try {
            $parsed = Type\dict(Type\string(), Type\mixed())->coerce(decode($body));
        } catch (DecodeException|CoercionException) {
            return $this->json->problem(new BadRequest(detail: 'The request body is not a JSON object.'));
        }

        return $handler->handle($request->withParsedBody($parsed));
    }
}
