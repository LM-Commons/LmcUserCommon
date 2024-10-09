<?php

declare(strict_types=1);

namespace Lmc\User\Common;

use Laminas\Db\Adapter\Adapter;
use Laminas\Hydrator\ClassMethodsHydrator;
use Lmc\User\Common\Mapper\UserHydrator;
use Lmc\User\Common\Mapper\UserHydratorFactory;
use Lmc\User\Common\Mapper\UserInterface;
use Lmc\User\Common\Mapper\UserMapperInterface;
use Lmc\User\Common\Mapper\UserMapperFactory;
use Lmc\User\Common\Options\CommonOptions;
use Lmc\User\Common\Options\CommonOptionsFactory;

class ConfigProvider
{
    public const LMC_USER_SESSION_STORAGE_NAMESPACE = 'LmcUserNamespace';

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
                CommonOptions::class   => CommonOptionsFactory::class,
                UserMapperInterface::class => UserMapperFactory::class,
                UserHydrator::class  => UserHydratorFactory::class,
            ],
        ];
    }
}
