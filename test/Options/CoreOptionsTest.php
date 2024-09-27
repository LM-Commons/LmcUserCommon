<?php

declare(strict_types=1);

namespace LmcTest\User\Core\Options;

use Lmc\User\Core\Entity\User;
use Lmc\User\Core\Options\CoreOptions;
use LmcTest\User\Core\Assets\TestUserEntity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(CoreOptions::class)]
class CoreOptionsTest extends TestCase
{
    public function testCoreOptionsDefault(): void
    {
        $coreOptions = new CoreOptions();
        $this->assertEquals('user', $coreOptions->getTableName());
        $this->assertEquals(User::class, $coreOptions->getUserEntityClass());
    }

    public function testCoreOptionsCustoms(): void
    {
        $coreOptions = new CoreOptions([
            'tableName'       => 'foo',
            'userEntityClass' => TestUserEntity::class,
        ]);
        $this->assertEquals('foo', $coreOptions->getTableName());
        $this->assertEquals(TestUserEntity::class, $coreOptions->getUserEntityClass());
    }

    public function testCoreOptionsSetGet(): void
    {
        $coreOptions = new CoreOptions();
        $this->assertEquals('foo', $coreOptions->setTableName('foo')->getTableName());
        $this->assertEquals(
            TestUserEntity::class,
            $coreOptions->setUserEntityClass(TestUserEntity::class)
                ->getUserEntityClass()
        );
    }

    public function testNotExistUserEntityClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CoreOptions([
            'userEntityClass' => 'foo',
        ]);
    }

    public function testInvalidUserEntityClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CoreOptions([
            'userEntityClass' => stdClass::class,
        ]);
    }
}
