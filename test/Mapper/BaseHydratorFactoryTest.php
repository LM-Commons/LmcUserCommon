<?php

declare(strict_types=1);

namespace LmcTest\User\Common\Mapper;

use Laminas\Hydrator\HydratorInterface;
use Laminas\ServiceManager\ServiceManager;
use Lmc\User\Common\ConfigProvider;
use Lmc\User\Common\Mapper\BaseUserHydratorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BaseUserHydratorFactory::class)]
final class BaseHydratorFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $configProvider = new ConfigProvider();
        $container      = new ServiceManager($configProvider->getDependencies());
        $this->assertInstanceOf(HydratorInterface::class, $container->get('lmcuser_default_hydrator'));
    }
}
