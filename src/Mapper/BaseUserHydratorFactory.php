<?php

declare(strict_types=1);

namespace Lmc\User\Common\Mapper;

use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Hydrator\Strategy\ExplodeStrategy;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class BaseUserHydratorFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): HydratorInterface
    {
        $hydrator = new ClassMethodsHydrator();
        $hydrator->addStrategy('roles', new ExplodeStrategy(';'));
        return $hydrator;
    }
}
