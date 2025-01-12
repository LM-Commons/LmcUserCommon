<?php

declare(strict_types=1);

namespace Lmc\User\Common\Authentication\Storage;

use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Lmc\User\Common\Mapper\UserMapperInterface;
use Psr\Container\ContainerInterface;

class DbFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Db
    {
        return new Db(
            $container->get(UserMapperInterface::class),
            new Session()
        );
    }
}
