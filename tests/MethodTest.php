<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\Method;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Method::class)]
final class MethodTest extends TestCase
{
    #[DataProvider('provideRequestMethods')]
    public function testFromRequest(string $method, Method $expected): void
    {
        $request = new ServerRequest($method, '/');

        $this->assertSame($expected, Method::fromRequest($request));
    }

    /**
     * @return iterable<string, array{string, Method}>
     */
    public static function provideRequestMethods(): iterable
    {
        yield 'uppercase' => ['GET', Method::Get];
        yield 'lowercase' => ['put', Method::Put];
        yield 'mixed case' => ['pAtCh', Method::Patch];
        yield 'post' => ['POST', Method::Post];
        yield 'query' => ['QUERY', Method::Query];
        yield 'head' => ['HEAD', Method::Head];
        yield 'delete' => ['DELETE', Method::Delete];
        yield 'connect' => ['CONNECT', Method::Connect];
        yield 'options' => ['OPTIONS', Method::Options];
        yield 'trace' => ['TRACE', Method::Trace];
    }
}
