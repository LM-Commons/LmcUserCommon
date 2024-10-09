<?php

declare(strict_types=1);

namespace LmcTest\User\Common\Authentication\Adapter;

use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Lmc\User\Common\Authentication\Adapter\AdapterChain;
use Lmc\User\Common\Authentication\Adapter\AdapterChainFactory;
use Lmc\User\Common\Options\CommonOptions;
use PHPUnit\Framework\Attributes\CoversClass;
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
        $coreOptions = new CommonOptions();
        $container   = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')->with(CommonOptions::class)->willReturn($coreOptions);
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
        $coreOptions  = new CommonOptions();
        $eventManager = $this->createMock(EventManagerInterface::class);
        $container    = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('has')->with('EventManager')->willReturn(true);
        $container->expects($this->exactly(2))->method('get')
            ->willReturnMap([
                [CommonOptions::class, $coreOptions],
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
        $coreOptions  = new CommonOptions([
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
                [CommonOptions::class, $coreOptions],
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
        $coreOptions = new CommonOptions([
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
            ->with(CommonOptions::class)
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
        $coreOptions  = new CommonOptions([
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
                [CommonOptions::class, $coreOptions],
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
        $coreOptions = new CommonOptions([
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
            ->with(CommonOptions::class)
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
        $coreOptions = new CommonOptions([
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
                [CommonOptions::class, $coreOptions],
                ['foo', new stdClass()],
            ]);
        $factory = new AdapterChainFactory();
        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage("Adapter 'stdClass' is not an instance of 'ListenerAggregateInterface'");
        $factory($container, '');
    }
}
