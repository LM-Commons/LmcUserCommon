<?php

declare(strict_types=1);

namespace LmcTest\User\Common\Options;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceManager;
use Lmc\User\Common\Options\CommonOptions;
use Lmc\User\Common\Options\CommonOptionsFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;

use function restore_error_handler;
use function set_error_handler;

use const E_USER_DEPRECATED;

#[CoversClass(CommonOptionsFactory::class)]
class CommonOptionsFactoryTest extends TestCase
{
    protected bool $userDeprecationNoticeTriggered = false;

    /**
     * @throws ContainerExceptionInterface
     */
    public function testFactory(): void
    {
        $serviceManager = new ServiceManager([
            'services' => [
                'config' => [
                    'lmc_user' => [],
                ],
            ],
        ]);
        $factory        = new CommonOptionsFactory();
        $this->assertInstanceOf(CommonOptions::class, $factory($serviceManager, ''));
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testFactoryNoConfig(): void
    {
        $serviceManager = new ServiceManager([
            'services' => [
                'config' => [],
            ],
        ]);
        $factory        = new CommonOptionsFactory();
        $this->expectException(ServiceNotCreatedException::class);
        $factory($serviceManager, '');
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testFactoryLmcUserConfig(): void
    {
        $serviceManager = new ServiceManager([
            'services' => [
                'config' => [
                    'lmcuser' => [],
                ],
            ],
        ]);
        $factory        = new CommonOptionsFactory();
        set_error_handler([$this, 'errorHandler']);
        $options = $factory($serviceManager, 'lmcuser');
        restore_error_handler();
        $this->assertTrue($this->userDeprecationNoticeTriggered);
        $this->assertInstanceOf(CommonOptions::class, $options);
    }

    public function errorHandler(int $errno): bool
    {
        if ($errno === E_USER_DEPRECATED) {
            $this->userDeprecationNoticeTriggered = true;
        }
        return true;
    }
}
