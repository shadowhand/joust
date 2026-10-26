<?php

declare(strict_types=1);

namespace Joust\Tests\Response;

use Joust\Response\JsonResponseSettings;
use Joust\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psl\Type\Exception\AssertException;

use const JSON_INVALID_UTF8_IGNORE;

#[CoversClass(JsonResponseSettings::class)]
final class JsonResponseSettingsTest extends TestCase
{
    public function testDefaults(): void
    {
        $settings = new JsonResponseSettings();

        $this->assertSame('application/json', $settings->contentType);
        $this->assertSame('utf-8', $settings->charset);
        $this->assertFalse($settings->pretty);
        $this->assertSame(0, $settings->flags);
    }

    public function testCustomValues(): void
    {
        $settings = new JsonResponseSettings(
            contentType: 'text/plain',
            charset: 'iso-8859-1',
            pretty: true,
            flags: JSON_INVALID_UTF8_IGNORE,
        );

        $this->assertSame('text/plain', $settings->contentType);
        $this->assertSame('iso-8859-1', $settings->charset);
        $this->assertTrue($settings->pretty);
        $this->assertSame(JSON_INVALID_UTF8_IGNORE, $settings->flags);
    }

    public function testRejectsAnEmptyContentType(): void
    {
        $this->expectException(AssertException::class);

        new JsonResponseSettings(contentType: '');
    }

    public function testRejectsNegativeFlags(): void
    {
        $this->expectException(AssertException::class);

        new JsonResponseSettings(flags: -1);
    }
}
