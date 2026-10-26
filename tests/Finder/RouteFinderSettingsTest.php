<?php

declare(strict_types=1);

namespace Joust\Tests\Finder;

use Joust\Finder\RouteFinderSettings;
use Joust\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteFinderSettings::class)]
final class RouteFinderSettingsTest extends TestCase
{
    public function testDefaults(): void
    {
        $settings = new RouteFinderSettings();

        $this->assertStringEndsWith('/src', $settings->routeDir);
        $this->assertNull($settings->baseUrl);
    }

    public function testCustomValues(): void
    {
        $settings = new RouteFinderSettings('/app/routes', 'https://example.com');

        $this->assertSame('/app/routes', $settings->routeDir);
        $this->assertSame('https://example.com', $settings->baseUrl);
    }
}
