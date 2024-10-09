<?php

declare(strict_types=1);

namespace LmcTest\User\Common\Mapper;

use Laminas\Db\Adapter\Adapter;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceManager;
use Lmc\User\Common\ConfigProvider;
use Lmc\User\Common\Mapper\User;
use Lmc\User\Common\Mapper\UserMapperFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use stdClass;

#[CoversClass(UserMapperFactory::class)]
class UserMapperFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function testInvoke(): void
    {
        $configProvider = new ConfigProvider();
        $container      = new ServiceManager($configProvider->getDependencies());
        $container->setService('config', [
            'lmc_user' => [],
        ]);
        $container->setService(Adapter::class, $this->createMock(Adapter::class));
        $factory = new UserMapperFactory();
        $this->assertInstanceOf(User::class, $factory($container, ''));
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testInvalidDbAdapter(): void
    {
        $configProvider = new ConfigProvider();
        $container      = new ServiceManager($configProvider->getDependencies());
        $container->setService('config', [
            'lmc_user' => [],
        ]);
        $container->setService(Adapter::class, new stdClass());
        $this->expectException(ServiceNotCreatedException::class);
        $factory = new UserMapperFactory();
        $factory($container, '');
    }

    /**
     * @throws ContainerExceptionInterface|Exception
     */
    public function testInvalidHydrator(): void
    {
        $configProvider = new ConfigProvider();
        $container      = new ServiceManager($configProvider->getDependencies());
        $container->setService('config', [
            'lmc_user' => [],
        ]);
        $container->setService('lmcuser_user_hydrator', new stdClass());
        $container->setService(Adapter::class, $this->createMock(Adapter::class));
        $this->expectException(ServiceNotCreatedException::class);
        $factory = new UserMapperFactory();
        $factory($container, '');
    }
}
