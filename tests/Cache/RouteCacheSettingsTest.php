<?php

declare(strict_types=1);

namespace Joust\Tests\Cache;

use Joust\Cache\RouteCacheSettings;
use Joust\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RouteCacheSettings::class)]
final class RouteCacheSettingsTest extends TestCase
{
    public function testDefaultCacheFile(): void
    {
        $settings = new RouteCacheSettings();

        $this->assertStringEndsWith('/var/joust-routes.json.cache', $settings->cacheFile);
    }

    public function testCustomCacheFile(): void
    {
        $settings = new RouteCacheSettings('/tmp/joust-routes.cache');

        $this->assertSame('/tmp/joust-routes.cache', $settings->cacheFile);
    }
}
