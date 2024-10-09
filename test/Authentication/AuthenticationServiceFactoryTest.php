<?php

declare(strict_types=1);

namespace LmcTest\User\Common\Authentication;

use Laminas\Authentication\AuthenticationService;
use Lmc\User\Common\Authentication\Adapter\AdapterChain;
use Lmc\User\Common\Authentication\AuthenticationServiceFactory;
use Lmc\User\Common\Authentication\Storage\Db;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

#[CoversClass(AuthenticationServiceFactory::class)]
class AuthenticationServiceFactoryTest extends TestCase
{
    /**
     * @throws Exception
     * @throws ContainerExceptionInterface
     */
    public function testCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                [AdapterChain::class, $this->createMock(AdapterChain::class)],
                [Db::class, $this->createMock(Db::class)],
            ]);
        $factory = new AuthenticationServiceFactory();
        $this->assertInstanceOf(AuthenticationService::class, $factory($container, ''));
    }
}
