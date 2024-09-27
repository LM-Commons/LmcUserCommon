<?php

declare(strict_types=1);

namespace Lmc\User\Core;

use Laminas\Db\Adapter\Adapter;
use Laminas\Hydrator\ClassMethodsHydrator;
use Lmc\User\Core\Mapper\UserHydrator;
use Lmc\User\Core\Mapper\UserHydratorFactory;
use Lmc\User\Core\Mapper\UserInterface;
use Lmc\User\Core\Mapper\UserMapperInterface;
use Lmc\User\Core\Mapper\UserMapperFactory;
use Lmc\User\Core\Options\CoreOptions;
use Lmc\User\Core\Options\CoreOptionsFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'aliases'    => [
                'lmcuser_laminas_db_adapter' => Adapter::class,
                'lmcuser_user_mapper'        => UserMapperInterface::class,
                'lmcuser_user_hydrator'      => UserHydrator::class,
                'lmcuser_base_hydrator'      => 'lmcuser_default_hydrator',
                UserInterface::class         => UserMapperInterface::class,
            ],
            'invokables' => [
                'lmcuser_default_hydrator' => ClassMethodsHydrator::class,
            ],
            'factories'  => [
                CoreOptions::class   => CoreOptionsFactory::class,
                UserMapperInterface::class => UserMapperFactory::class,
                UserHydrator::class  => UserHydratorFactory::class,
            ],
        ];
    }
}
