<?php

declare(strict_types=1);

namespace LmcTest\User\Core;

use Lmc\User\Core\ConfigProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigProvider::class)]
class ConfigProviderTest extends TestCase
{
    public function testConfigProvider(): void
    {
        $configProvider = new ConfigProvider();
        $this->assertIsArray($configProvider());
        $this->assertArrayHasKey('dependencies', $configProvider());
        $this->assertIsArray($configProvider->getDependencies());
        $this->assertArrayHasKey('factories', $configProvider->getDependencies());
        $this->assertArrayHasKey('aliases', $configProvider->getDependencies());
        $this->assertArrayHasKey('invokables', $configProvider->getDependencies());
    }
}
