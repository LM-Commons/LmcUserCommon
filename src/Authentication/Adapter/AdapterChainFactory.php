<?php

declare(strict_types=1);

namespace Lmc\User\Common\Authentication\Adapter;

use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Lmc\User\Common\Options\ChainableAdapterConfig;
use Lmc\User\Common\Options\CommonOptions;
use Psr\Container\ContainerInterface;

use function assert;
use function sprintf;

class AdapterChainFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): AdapterChain
    {
        $adapterChain = new AdapterChain();
        if ($container->has('EventManager')) {
            /** @psalm-suppress MixedArgument */
            $adapterChain->setEventManager($container->get('EventManager'));
        }
        /** @var CommonOptions $coreOptions */
        $coreOptions = $container->get(CommonOptions::class);

        foreach ($coreOptions->getAuthAdapters() as $adapterConfig) {
            assert($adapterConfig instanceof ChainableAdapterConfig);
            if ($container->has($adapterConfig->getName())) {
                /** @var ListenerAggregateInterface $chainableAdapter */
                $chainableAdapter = $container->get($adapterConfig->getName());
                if (! $chainableAdapter instanceof ListenerAggregateInterface) {
                    throw new ServiceNotCreatedException(
                        sprintf(
                            "Adapter '%s' is not an instance of 'ListenerAggregateInterface'",
                            $chainableAdapter::class
                        )
                    );
                }
            } else {
                throw new ServiceNotCreatedException(
                    sprintf("Adapter '%s' not found", $adapterConfig->getName())
                );
            }
            $chainableAdapter->attach($adapterChain->getEventManager(), $adapterConfig->getPriority());
        }
        return $adapterChain;
    }
}
