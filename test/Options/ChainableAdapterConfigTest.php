<?php

declare(strict_types=1);

namespace LmcTest\User\Common\Options;

use Lmc\User\Common\Options\ChainableAdapterConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChainableAdapterConfig::class)]
class ChainableAdapterConfigTest extends TestCase
{
    public function testConstruct(): void
    {
        $adapterConfig = new ChainableAdapterConfig([]);
        $this->assertArrayHasKey('name', $adapterConfig->toArray());
        $this->assertArrayHasKey('priority', $adapterConfig->toArray());
        $this->assertArrayHasKey('options', $adapterConfig->toArray());
        $this->assertIsArray($adapterConfig->toArray()['options']);
    }

    public function testGetSet(): void
    {
        $adapterConfig = new ChainableAdapterConfig([]);
        $adapterConfig->setName('foo');
        $adapterConfig->setPriority(1);
        $adapterConfig->setOptions(['foo' => 'bar']);
        $this->assertEquals('foo', $adapterConfig->getName());
        $this->assertEquals(1, $adapterConfig->getPriority());
        $this->assertEquals(['foo' => 'bar'], $adapterConfig->getOptions());
    }
}
