<?php

declare(strict_types=1);

namespace LmcTest\User\Core\Mapper;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceManager;
use Lmc\User\Core\ConfigProvider;
use Lmc\User\Core\Mapper\UserHydrator;
use Lmc\User\Core\Mapper\UserHydratorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use stdClass;

#[CoversClass(UserHydratorFactory::class)]
class UserHydratorFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function testInvoke(): void
    {
        $configProvider = new ConfigProvider();
        $container      = new ServiceManager($configProvider->getDependencies());
        $this->assertInstanceOf(UserHydrator::class, $container->get(UserHydrator::class));
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testInvalidBaseHydrator(): void
    {
        $container = new ServiceManager([
            'services' => [
                'lmcuser_base_hydrator' => new stdClass(),
            ],
        ]);
        $factory   = new UserHydratorFactory();
        $this->expectException(ServiceNotCreatedException::class);
        $factory($container, '');
    }
}
