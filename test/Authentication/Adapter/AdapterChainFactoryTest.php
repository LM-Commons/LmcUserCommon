<?php

declare(strict_types=1);

namespace LmcTest\User\Core\Authentication\Adapter;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Lmc\User\Core\Authentication\Adapter\AdapterChain;
use Lmc\User\Core\Authentication\Adapter\AdapterChainFactory;
use Lmc\User\Core\Options\CoreOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use stdClass;

#[CoversClass(AdapterChainFactory::class)]
class AdapterChainFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function testFactoryDefault(): void
    {
        $coreOptions = new CoreOptions();
        $container   = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')->with(CoreOptions::class)->willReturn($coreOptions);
        $container->expects($this->once())->method('has')->with('EventManager')->willReturn(false);
        $factory = new AdapterChainFactory();
        $this->assertInstanceOf(AdapterChain::class, $factory($container, ''));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function testFactoryEventManager(): void
    {
        $coreOptions  = new CoreOptions();
        $eventManager = $this->createMock(EventManagerInterface::class);
        $container    = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('has')->with('EventManager')->willReturn(true);
        $container->expects($this->exactly(2))->method('get')
            ->willReturnMap([
                [CoreOptions::class, $coreOptions],
                ['EventManager', $eventManager],
            ]);
        $factory = new AdapterChainFactory();
        $adapter = $factory($container, '');
        $this->assertInstanceOf(AdapterChain::class, $adapter);
        $this->assertEquals($eventManager, $adapter->getEventManager());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function testWithChainableAdapter(): void
    {
        $coreOptions  = new CoreOptions([
            'authAdapters' => [
                1 => 'fooClass',
            ],
        ]);
        $eventManager = $this->createMock(EventManagerInterface::class);
        $fooClass     = $this->createMock(ListenerAggregateInterface::class);
        $fooClass->expects($this->once())->method('attach')->with($eventManager, 1);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('has')
            ->willReturnMap([
                ['EventManager', true],
                ['fooClass', true],
            ]);
        $container->expects($this->exactly(3))->method('get')
            ->willReturnMap([
                ['EventManager', $eventManager],
                ['fooClass', $fooClass],
                [CoreOptions::class, $coreOptions],
            ]);
        $factory = new AdapterChainFactory();
        $adapter = $factory($container, '');
        $this->assertInstanceOf(AdapterChain::class, $adapter);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function testChainableAdapterNotExists(): void
    {
        $coreOptions = new CoreOptions([
            'authAdapters' => [
                1 => stdClass::class,
            ],
        ]);
        $container   = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('has')
            ->willReturnMap([
                ['EventManager', false],
                [stdClass::class, false],
            ]);
        $container->expects($this->once())->method('get')
            ->with(CoreOptions::class)
            ->willReturn($coreOptions);
        $factory = new AdapterChainFactory();
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage("Adapter 'stdClass' not found");
        $factory($container, '');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function testWithChainableAdapterNotListenerAggregate(): void
    {
        $coreOptions  = new CoreOptions([
            'authAdapters' => [
                1 => 'fooClass',
            ],
        ]);
        $eventManager = $this->createMock(EventManagerInterface::class);
        $container    = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('has')
            ->willReturnMap([
                ['EventManager', true],
                ['fooClass', true],
            ]);
        $container->expects($this->exactly(3))->method('get')
            ->willReturnMap([
                ['EventManager', $eventManager],
                ['fooClass', new stdClass()],
                [CoreOptions::class, $coreOptions],
            ]);
        $factory = new AdapterChainFactory();
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage("Adapter 'stdClass' is not an instance of 'ListenerAggregateInterface'");
        $factory($container, '');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function testInvalidAdapterConfiguration(): void
    {
        $coreOptions = new CoreOptions([
            'auth_adapters' => [
                100 => 'foo',
            ],
        ]);
        $container   = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('has')
            ->willReturnMap([
                ['EventManager', false],
                ['foo', false],
            ]);
        $container->expects($this->once())->method('get')
            ->with(CoreOptions::class)
            ->willReturn($coreOptions);
        $factory = new AdapterChainFactory();
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage("Adapter 'foo' not found");
        $factory($container, '');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws Exception
     */
    public function testInvalidAdapter(): void
    {
        $coreOptions = new CoreOptions([
            'auth_adapters' => [
                100 => 'foo',
            ],
        ]);
        $container   = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))->method('has')
            ->willReturnMap([
                ['EventManager', false],
                ['foo', true],
            ]);
        $container->expects($this->exactly(2))->method('get')
            ->willReturnMap([
                [CoreOptions::class, $coreOptions],
                ['foo', new stdClass()],
            ]);
        $factory = new AdapterChainFactory();
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage("Adapter 'stdClass' is not an instance of 'ListenerAggregateInterface'");
        $factory($container, '');
    }
    public static function invalidAdapterConfigurationProvider(): array
    {
        return [
            'invalid name'   => [
                100,
                'bar',
            ],
            'invalid type' => [
                1,
                1,
            ],
        ];
    }
}
