<?php

declare(strict_types=1);

namespace Joust\Middleware;

use Errata\Http\Client\BadRequest;
use Joust\Response\JsonResponseFactory;
use Override;
use Psl\Type\Exception\CoercionException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Psl\Str\Byte\before;
use function Psl\Str\Byte\ends_with;
use function Psl\Str\Byte\lowercase;
use function Psl\Str\Byte\starts_with;
use function Psl\Str\Byte\trim;
use function Psl\Type\json_decoded;
use function Psl\Type\mixed_dict;
use function Psl\Type\mixed_vec;
use function Psl\Type\union;

/**
 * Parses a request body as JSON.
 *
 * Determines if the request has a content type of `application/json`, or similar vendor type.
 * If so, decodes the body and applies it to the request as parsed body.
 * If not, passes the request through without modification.
 *
 * @api
 */
final readonly class ParseJsonMiddleware implements MiddlewareInterface
{
    public function __construct(
        private JsonResponseFactory $json,
    ) {}

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->isJson($request)) {
            return $handler->handle($request);
        }

        $body = (string) $request->getBody();

        if ('' === $body) {
            return $handler->handle($request);
        }

        try {
            $parsed = json_decoded(union(mixed_dict(), mixed_vec()))->coerce($body);
        } catch (CoercionException) {
            return $this->json->problem(new BadRequest(detail: 'The request body is not a JSON object or array.'));
        }

        return $handler->handle($request->withParsedBody($parsed));
    }

    private function isJson(ServerRequestInterface $request): bool
    {
        $contentType = lowercase(trim($request->getHeaderLine('Content-Type')));
        $contentType = before($contentType, needle: ';') ?? $contentType;

        if ($contentType === 'application/json') {
            return true;
        }

        return starts_with($contentType, prefix: 'application/') && ends_with($contentType, suffix: '+json');
    }
}
