<?php

declare(strict_types=1);

namespace LmcTest\User\Common\Authentication\Storage;

use Lmc\User\Common\Authentication\Storage\Db;
use Lmc\User\Common\Authentication\Storage\DbFactory;
use Lmc\User\Common\Mapper\UserMapperInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

#[CoversClass(DbFactory::class)]
class DbFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testCreateDb(): void
    {
        $mapper    = $this->createMock(UserMapperInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())->method('get')
            ->with(UserMapperInterface::class)
            ->willReturn($mapper);
        $factory = new DbFactory();
        $this->assertInstanceOf(Db::class, $factory($container, ''));
    }
}
