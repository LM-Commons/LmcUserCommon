<?php

declare(strict_types=1);

namespace Lmc\User\Core\Mapper;

use Laminas\Db\Adapter\Adapter;
use Laminas\Hydrator\HydratorInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Lmc\User\Core\Options\CoreOptions;
use Psr\Container\ContainerInterface;

use function gettype;
use function is_object;
use function sprintf;

class UserMapperFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): User
    {
        /** @var CoreOptions $options */
        $options   = $container->get(CoreOptions::class);
        $dbAdapter = $container->get('lmcuser_laminas_db_adapter');
        if (! $dbAdapter instanceof Adapter) {
            throw new ServiceNotCreatedException(
                sprintf(
                    "'lmcuser_laminas_db_adapter' does not resolve is not a valid database adapter; received '%s'",
                    is_object($dbAdapter) ? $dbAdapter::class : gettype($dbAdapter)
                )
            );
        }

        $hydrator = $container->get('lmcuser_user_hydrator');
        if (! $hydrator instanceof HydratorInterface) {
            throw new ServiceNotCreatedException(
                sprintf(
                    "'lmcuser_user_hydrator' does not resolve is not a valid hydrator; received '%s'",
                    is_object($hydrator) ? $hydrator::class : gettype($hydrator)
                )
            );
        }

        $entityClass = $options->getUserEntityClass();

        /**
         * @psalm-suppress InvalidStringClass
         * @psalm-suppress ArgumentTypeCoercion
         */
        return new User(
            $dbAdapter,
            $options->getTableName(),
            $hydrator,
            new $entityClass()
        );
    }
}
