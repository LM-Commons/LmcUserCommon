<?php

declare(strict_types = 1);

namespace Lmc\User\Common\Db\Adapter;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\ResultSet\ResultSetInterface;

class MasterSlaveAdapter extends Adapter implements MasterSlaveAdapterInterface
{
    /**
     * slave adapter
     */
    protected Adapter $slaveAdapter;

    public function __construct(
        Adapter $slaveAdapter,
        DriverInterface $driver,
        PlatformInterface $platform = null,
        ResultSetInterface $queryResultPrototype = null
    ) {
        $this->slaveAdapter = $slaveAdapter;
        parent::__construct($driver, $platform, $queryResultPrototype);
    }

    public function getSlaveAdapter(): Adapter
    {
        return $this->slaveAdapter;
    }
}
