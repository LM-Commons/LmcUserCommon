<?php

declare(strict_types=1);

namespace Lmc\User\Common;

class Module
{
    public const LMC_USER_SESSION_STORAGE_NAMESPACE = ConfigProvider::LMC_USER_SESSION_STORAGE_NAMESPACE;

    public function getConfig(): array
    {
        $configProvider = new ConfigProvider();
        return [
            'service_manager' => $configProvider->getDependencies(),
        ];
    }
}
