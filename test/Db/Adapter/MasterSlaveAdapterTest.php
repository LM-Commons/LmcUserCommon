<?php

declare(strict_types=1);

namespace LmcTest\User\Core\Db\Adapter;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\ResultSet\ResultSetInterface;
use Lmc\User\Core\Db\Adapter\MasterSlaveAdapter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(MasterSlaveAdapter::class)]
class MasterSlaveAdapterTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testConstruct()
    {
        $slave  = $this->createMock(Adapter::class);
        $master = new MasterSlaveAdapter(
            $slave,
            $this->createMock(DriverInterface::class),
            $this->createMock(PlatformInterface::class),
            $this->createMock(ResultSetInterface::class)
        );
        $this->assertEquals($slave, $master->getSlaveAdapter());
    }
}
