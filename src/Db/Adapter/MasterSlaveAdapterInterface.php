<?php

declare(strict_types=1);

namespace Lmc\User\Common\Db\Adapter;

use Laminas\Db\Adapter\Adapter;

interface MasterSlaveAdapterInterface
{
    public function getSlaveAdapter(): Adapter;
}
