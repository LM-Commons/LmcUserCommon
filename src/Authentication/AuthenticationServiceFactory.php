<?php

declare(strict_types=1);

namespace Lmc\User\Core\Authentication;

use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Lmc\User\Core\Authentication\Adapter\AdapterChain;
use Lmc\User\Core\Authentication\Storage\Db;
use Psr\Container\ContainerInterface;

class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): AuthenticationService {
        return new AuthenticationService(
            $container->get(Db::class),
            $container->get(AdapterChain::class)
        );
    }
}
