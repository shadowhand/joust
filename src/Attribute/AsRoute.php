<?php

declare(strict_types=1);

namespace Joust\Attribute;

use Attribute;
use Joust\Method;
use League\Uri\UrlPattern;
use League\Uri\UrlPatternBuilder;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class AsRoute
{
    public Method $method;
    public UrlPattern $pattern;

    /**
     * @param Method|non-empty-string $method
     * @param UrlPattern|string $pattern
     */
    public function __construct(
        Method|string $method = Method::Get,
        UrlPattern|string $pattern = '/',
        ?string $baseUrl = null,
    ) {
        $this->method = $this->createMethod($method);
        $this->pattern = $this->createPattern($pattern, $baseUrl);
    }

    private function createMethod(Method|string $method): Method
    {
        if ($method instanceof Method) {
            return $method;
        }

        return Method::from($method);
    }

    private function createPattern(UrlPattern|string $pattern, ?string $baseUrl): UrlPattern
    {
        if ($pattern instanceof UrlPattern) {
            return $pattern;
        }

        return new UrlPatternBuilder(baseUrl: $baseUrl)
            ->path($pattern)
            ->build();
    }
}
