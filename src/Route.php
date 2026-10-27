<?php

declare(strict_types=1);

namespace Joust;

use JsonSerializable;
use League\Uri\UrlPattern;
use League\Uri\UrlPattern\MatchMode;
use League\Uri\UrlPatternBuilder;
use Override;
use Psl\Type\TypeInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Psl\Dict\map;
use function Psl\Type\backed_enum;
use function Psl\Type\bool;
use function Psl\Type\class_string;
use function Psl\Type\converted;
use function Psl\Type\instance_of;
use function Psl\Type\nullable;
use function Psl\Type\shape;
use function Psl\Type\string;
use function Psl\Type\vec;

/**
 * A request handler route.
 *
 * @api
 */
final readonly class Route implements JsonSerializable
{
    /**
     * @return TypeInterface<self>
     */
    public static function type(): TypeInterface
    {
        return converted(
            shape([
                'method' => backed_enum(Method::class),
                'pattern' => shape([
                    'scheme' => nullable(string()),
                    'username' => nullable(string()),
                    'password' => nullable(string()),
                    'host' => nullable(string()),
                    'port' => nullable(string()),
                    'path' => nullable(string()),
                    'query' => nullable(string()),
                    'fragment' => nullable(string()),
                ]),
                'ignoreCase' => bool(),
                'handler' => shape([
                    'name' => class_string(RequestHandlerInterface::class),
                    'middleware' => vec(class_string(MiddlewareInterface::class)),
                ]),
            ]),
            instance_of(self::class),
            // @mago-expect analysis:imprecise-type
            static fn(array $data): self => new self(
                $data['method'],
                new UrlPatternBuilder(...$data['pattern'])->build(
                    matchMode: $data['ignoreCase'] ? MatchMode::CaseInsensitive : MatchMode::CaseSensitive,
                ),
                new RouteHandler($data['handler']['name'], $data['handler']['middleware']),
            ),
        );
    }

    public function __construct(
        public Method $method,
        public UrlPattern $pattern,
        public RouteHandler $handler,
    ) {}

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function jsonSerialize(): array
    {
        $hasVariable = $this->pattern->hasVariable();

        return [
            'method' => $this->method->value,
            // Implicit wildcards must remain omitted to preserve UrlPattern::hasVariable().
            'pattern' => map(
                [
                    'scheme' => $this->pattern->scheme,
                    'username' => $this->pattern->username,
                    'password' => $this->pattern->password,
                    'host' => $this->pattern->host,
                    'port' => $this->pattern->port,
                    'path' => $this->pattern->path,
                    'query' => $this->pattern->query,
                    'fragment' => $this->pattern->fragment,
                ],
                static fn(?string $component): ?string => !$hasVariable && $component === '*' ? null : $component,
            ),
            'ignoreCase' => $this->pattern->matchMode === MatchMode::CaseInsensitive,
            'handler' => [
                'name' => $this->handler->name,
                'middleware' => $this->handler->middleware,
            ],
        ];
    }
}
