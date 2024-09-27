<?php

declare(strict_types=1);

namespace Lmc\User\Core\Options;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

use function assert;
use function is_array;
use function trigger_error;

use const E_USER_DEPRECATED;

class CoreOptionsFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): CoreOptions
    {
        $config = $container->get('config');
        assert(is_array($config));

        if (isset($config['lmc_user']) && is_array($config['lmc_user'])) {
            $config = $config['lmc_user'];
        } elseif (isset($config['lmcuser']) && is_array($config['lmcuser'])) {
            $config = $config['lmcuser'];
            trigger_error(
                "Usage of 'lmcuser' config key is deprecated. Use 'lmc_user' instead",
                E_USER_DEPRECATED
            );
        } else {
            throw new ServiceNotCreatedException("Cannot find a configuration for 'lmc_user'");
        }
        return new CoreOptions($config);
    }
}
