<?php

declare(strict_types=1);

namespace Lmc\User\Common\Mapper;

use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Hydrator\Strategy\ExplodeStrategy;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Lmc\User\Common\Options\CommonOptions;
use Psr\Container\ContainerInterface;

use function assert;

final class BaseUserHydratorFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): HydratorInterface
    {
        $hydrator      = new ClassMethodsHydrator();
        $commonOptions = $container->get(CommonOptions::class);
        $delimiter     = $commonOptions->getRolesDelimiter();
        assert(! empty($delimiter));
        $hydrator->addStrategy('roles', new ExplodeStrategy($delimiter));
        return $hydrator;
    }
}
