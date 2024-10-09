<?php

declare(strict_types=1);

namespace LmcTest\User\Core\Options;

use InvalidArgumentException;
use Lmc\User\Core\Entity\User;
use Lmc\User\Core\Options\CoreOptions;
use LmcTest\User\Core\Assets\TestUserEntity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(CoreOptions::class)]
class CoreOptionsTest extends TestCase
{
    public function testCoreOptionsDefault(): void
    {
        $coreOptions = new CoreOptions();
        $this->assertEquals('user', $coreOptions->getTableName());
        $this->assertEquals(User::class, $coreOptions->getUserEntityClass());
        $this->assertIsArray($coreOptions->getAuthAdapters());
        $this->assertEmpty($coreOptions->getAuthAdapters());
    }

    public function testCoreOptionsCustoms(): void
    {
        $coreOptions = new CoreOptions([
            'tableName'       => 'foo',
            'userEntityClass' => TestUserEntity::class,
        ]);
        $this->assertEquals('foo', $coreOptions->getTableName());
        $this->assertEquals(TestUserEntity::class, $coreOptions->getUserEntityClass());
        $this->assertIsArray($coreOptions->getAuthAdapters());
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

    #[DataProvider('configProvider')]
    public function testAuthAdapterConfig(array $config, array $expectedResult, ?bool $expectException = false): void
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }
        $coreOptions  = new CoreOptions($config);
        $authAdapters = $coreOptions->getAuthAdapters();
        if (! $expectException) {
            $results = [];
            foreach ($authAdapters as $authAdapter) {
                $results[] = $authAdapter->toArray();
            }
            $this->assertEquals($expectedResult, $results);
        }
    }

    public function testFindAuthAdapterByName(): void
    {
        $coreOptions = new CoreOptions([
            'auth_adapters' => [
                100 => [
                    'name' => 'foo',
                ],
            ],
        ]);
        $this->assertEquals('foo', $coreOptions->findAuthAdapterByName('foo')->getName());
        $this->assertNull($coreOptions->findAuthAdapterByName('bar'));
    }

    public static function configProvider(): array
    {
        return [
            'priority-name'             => [
                [
                    'authAdapters' => [
                        5 => 'ClassName',
                    ],
                ],
                [
                    [
                        'name'     => 'ClassName',
                        'priority' => 5,
                        "options"  => [],
                    ],
                ],
                false,
            ],
            'adapter-config'            => [
                [
                    'authAdapters' => [
                        5 => [
                            'name'    => 'ClassName',
                            "options" => [],
                        ],
                    ],
                ],
                [
                    [
                        'name'     => 'ClassName',
                        'priority' => 5,
                        "options"  => [],
                    ],
                ],
                false,
            ],
            'adapter-config-no-options' => [
                [
                    'authAdapters' => [
                        5 => [
                            'name' => 'ClassName',
                        ],
                    ],
                ],
                [
                    [
                        'name'     => 'ClassName',
                        'priority' => 5,
                        "options"  => [],
                    ],
                ],
                false,
            ],
            'multi-adapter-config'      => [
                [
                    'authAdapters' => [
                        5   => [
                            'name'    => 'ClassName',
                            "options" => [],
                        ],
                        100 => [
                            'name'    => 'SecondClassName',
                            "options" => [],
                        ],
                    ],
                ],
                [
                    [
                        'name'     => 'ClassName',
                        'priority' => 5,
                        "options"  => [],
                    ],
                    [
                        'name'     => 'SecondClassName',
                        'priority' => 100,
                        "options"  => [],
                    ],
                ],
                false,
            ],
            'invalid-config-noname'     => [
                [
                    'authAdapters' => [
                        5 => [
                            "options" => [],
                        ],
                    ],
                ],
                [],
                true,
            ],
            'no-priority'               => [
                [
                    'authAdapters' => [
                        [
                            'name'    => 'ClassName',
                            "options" => [],
                        ],
                        [
                            'name'    => 'ClassName',
                            "options" => [],
                        ],
                    ],
                ],
                [],
                true,
            ],
            'invalid-priority'               => [
                [
                    'authAdapters' => [
                        'foo' => [
                            'name'    => 'ClassName',
                            "options" => [],
                        ],
                        [
                            'name'    => 'ClassName',
                            "options" => [],
                        ],
                    ],
                ],
                [],
                true,
            ],
        ];
    }
}
