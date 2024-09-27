<?php

declare(strict_types=1);

namespace LmcTest\User\Core\Options;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceManager;
use Lmc\User\Core\Options\CoreOptions;
use Lmc\User\Core\Options\CoreOptionsFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;

use function restore_error_handler;
use function set_error_handler;

use const E_USER_DEPRECATED;

#[CoversClass(CoreOptionsFactory::class)]
class CoreOptionsFactoryTest extends TestCase
{
    protected bool $userDeprecationNoticeTriggered = false;

    public function testFactory(): void
    {
        $serviceManager = new ServiceManager([
            'services' => [
                'config' => [
                    'lmc_user' => [],
                ],
            ],
        ]);
        $factory        = new CoreOptionsFactory();
        $this->assertInstanceOf(CoreOptions::class, $factory($serviceManager, ''));
    }

    public function testFactoryNoConfig(): void
    {
        $serviceManager = new ServiceManager([
            'services' => [
                'config' => [],
            ],
        ]);
        $factory        = new CoreOptionsFactory();
        $this->expectException(ServiceNotCreatedException::class);
        $options = $factory($serviceManager, '');
    }

    public function testFactoryLmcUserConfig(): void
    {
        $serviceManager = new ServiceManager([
            'services' => [
                'config' => [
                    'lmcuser' => [],
                ],
            ],
        ]);
        $factory        = new CoreOptionsFactory();
        set_error_handler([$this, 'errorHandler']);
        $options = $factory($serviceManager, 'lmcuser');
        restore_error_handler();
        $this->assertTrue($this->userDeprecationNoticeTriggered);
        $this->assertInstanceOf(CoreOptions::class, $options);
    }

    public function errorHandler(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if ($errno === E_USER_DEPRECATED) {
            $this->userDeprecationNoticeTriggered = true;
        }
        return true;
    }
}
