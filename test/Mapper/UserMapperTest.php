<?php

declare(strict_types=1);

namespace LmcTest\User\Core\Mapper;

use Exception as BaseException;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\EventManager\Event;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\ServiceManager\ServiceManager;
use Lmc\User\Core\Db\Adapter\MasterSlaveAdapter;
use Lmc\User\Core\Entity\User as Entity;
use Lmc\User\Core\Entity\UserInterface;
use Lmc\User\Core\Mapper\AbstractDbMapper;
use Lmc\User\Core\Mapper\User;
use Lmc\User\Core\Mapper\UserHydrator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

use function array_merge;
use function assert;
use function call_user_func_array;
use function constant;
use function defined;
use function explode;
use function file_get_contents;
use function in_array;
use function is_string;
use function preg_match;
use function sprintf;
use function strtoupper;
use function ucfirst;

#[CoversClass(User::class)]
#[CoversClass(AbstractDbMapper::class)]
final class UserMapperTest extends TestCase
{
    /** @var array<Adapter|bool>  */
    private static array $realAdapters = [];

    protected ContainerInterface $container;

    private static array $a = [];

    protected Adapter $adapter;

    protected User $mapper;

    protected bool $eventCalled           = false;
    protected ?UserInterface $eventEntity = null;
    private ?string $eventName            = null;

    public static function setUpBeforeClass(): void
    {
        // Setup databases
        if (defined('DB_DRIVER')) {
            $driver = constant('DB_DRIVER');
            if (in_array($driver, ['sqlite', 'mysql'])) {
                self::setupAdapter($driver);
            }
        }
    }

    public function setUp(): void
    {
        $this->container = new ServiceManager([]);
        $this->adapter   = new Adapter([
            'driver' => 'Pdo',
            'dsn'    => 'sqlite:memory',
        ]);
        $this->mapper    = new User(
            $this->createMock(Adapter::class),
            'user',
            new UserHydrator(new ClassMethodsHydrator()),
            new Entity()
        );
        parent::setUp();
    }

    public function testConstruct(): void
    {
        $adapter  = $this->createMock(Adapter::class);
        $hydrator = new UserHydrator(new ClassMethodsHydrator());
        $mapper   = new User(
            $adapter,
            'user',
            $hydrator,
            new Entity()
        );
        $this->assertEquals('user', $mapper->getTableName());
        $this->assertEquals($adapter, $mapper->getDbAdapter());
        $this->assertEquals($hydrator, $mapper->getHydrator());
    }

    /**
     * @throws Exception
     */
    public function testConstructMasterSlave(): void
    {
        $adapter       = $this->createMock(Adapter::class);
        $masterAdapter = new MasterSlaveAdapter(
            $adapter,
            $this->createMock(DriverInterface::class)
        );
        $hydrator      = new UserHydrator(new ClassMethodsHydrator());
        $mapper        = new User(
            $masterAdapter,
            'user',
            $hydrator,
            new Entity()
        );
        $this->assertEquals('user', $mapper->getTableName());
        $this->assertEquals($masterAdapter, $mapper->getDbAdapter());
        $this->assertEquals($adapter, $mapper->getDbSlaveAdapter());
        $this->assertEquals($hydrator, $mapper->getHydrator());
    }


    public function testGetSetTableName(): void
    {
        $this->assertEquals('foo', $this->mapper->setTableName('foo')->getTableName());
    }

    /**
     * @throws Exception
     */
    public function testGetSetAdapter(): void
    {
        $newAdapter = $this->createMock(Adapter::class);
        $this->assertEquals($newAdapter, $this->mapper->setDbAdapter($newAdapter)->getDbAdapter());
    }

    /**
     * @throws Exception
     */
    public function testSetDbSlaveAdapter(): void
    {
        $slaveDbAdapter     = $this->createMock(Adapter::class);
        $masterSlaveAdapter = new MasterSlaveAdapter(
            $slaveDbAdapter,
            $this->createMock(DriverInterface::class)
        );
        $this->mapper->setDbAdapter($masterSlaveAdapter);
        $this->assertEquals($slaveDbAdapter, $this->mapper->getDbSlaveAdapter());
    }

    public function testGetSetHydrator(): void
    {
        $newHydrator = new UserHydrator(new ClassMethodsHydrator());
        $this->assertEquals($newHydrator, $this->mapper->setHydrator($newHydrator)->getHydrator());
    }

    public function testGetSetPrototype(): void
    {
        $newEntity = new Entity();
        $this->assertEquals($newEntity, $this->mapper->setEntityPrototype($newEntity)->getEntityPrototype());
    }

    #[DataProvider('providerTestFindBy')]
    public function testFindBy(
        string $method,
        array $args,
        array $eventListener,
        bool $expectingResult,
        int $expectedId,
    ): void {
        foreach (self::$realAdapters as $driver => $adapter) {
            assert(is_string($driver));
            if ($adapter instanceof Adapter) {
                $this->setupUserTable($adapter, $driver);
                $this->mapper->setDbAdapter($adapter);
                $return = call_user_func_array([$this->mapper, $method], $args);

                if ($expectingResult) {
                    $this->assertIsObject($return);
                    $this->assertInstanceOf(Entity::class, $return);
                    $this->assertEquals($expectedId, $return->getId());
                } else {
                    $this->assertNull($return);
                }
            }
        }
    }

    public function testFindEventListener(): void
    {
        foreach (self::$realAdapters as $driver => $adapter) {
            if ($adapter instanceof Adapter) {
                $this->setupUserTable(self::$realAdapters[$driver], $driver);
                $this->mapper->setDbAdapter(self::$realAdapters[$driver]);
                $this->mapper->getEventManager()->attach('find', [$this, 'onEvent']);
                $this->eventCalled = false;
                $this->eventName   = null;
                $this->eventEntity = null;
                $entity            = $this->mapper->findById(1);
                $this->assertTrue($this->eventCalled);
                $this->assertEquals('find', $this->eventName);
                $this->assertEquals($entity, $this->eventEntity);
            }
        }
    }

    public function testFindEventListenerNotFound(): void
    {
        foreach (self::$realAdapters as $driver => $adapter) {
            if ($adapter instanceof Adapter) {
                $this->setupUserTable(self::$realAdapters[$driver], $driver);
                $this->mapper->setDbAdapter(self::$realAdapters[$driver]);
                $this->mapper->getEventManager()->attach('find', [$this, 'onEvent']);
                $this->eventCalled = false;
                $this->eventName   = null;
                $this->eventEntity = null;
                $this->mapper->findById(4);
                $this->assertTrue($this->eventCalled);
                $this->assertEquals('find', $this->eventName);
                $this->assertNull($this->eventEntity);
            }
        }
    }

    public function testMultipleFind(): void
    {
        foreach (self::$realAdapters as $driver => $adapter) {
            if ($adapter instanceof Adapter) {
                $this->setupUserTable($adapter, $driver);
                $this->mapper->setDbAdapter($adapter);
                $entity = $this->mapper->findById(1);
                $this->assertEquals(1, $entity->getId());
                $entity = $this->mapper->findById(2);
                $this->assertEquals(2, $entity->getId());
            }
        }
    }

    public function testInsert(): void
    {
        $entity = new Entity();
        $entity->setEmail('foo@bar.com');
        $entity->setUsername('foo');
        $entity->setPassword('foo');
        $entity->setDisplayName('foo');
        $entity->setState(UserInterface::STATE_ACTIVE);

        foreach (self::$realAdapters as $driver => $adapter) {
            assert(is_string($driver));
            if ($adapter instanceof Adapter) {
                $this->setupUserTable($adapter, $driver);
                $this->mapper->setDbAdapter($adapter);
                $this->mapper->insert($entity);
                $this->assertNotNull($entity->getId());
                $fetchedEntity = $this->mapper->findById($entity->getId());
                $this->assertEquals($entity, $fetchedEntity);
            }
        }
    }

    public function testUpdate(): void
    {
        foreach (self::$realAdapters as $driver => $adapter) {
            if ($adapter instanceof Adapter) {
                $this->setupUserTable($adapter, $driver);
                $this->mapper->setDbAdapter($adapter);
                $entity = $this->mapper->findById(1);
                $entity->setEmail('foo@bar.com');
                $this->mapper->update($entity);
                $updatedEntity = $this->mapper->findById(1);
                $this->assertEquals('foo@bar.com', $updatedEntity->getEmail());
            }
        }
    }

    public function testDelete(): void
    {
        foreach (self::$realAdapters as $driver => $adapter) {
            if ($adapter instanceof Adapter) {
                $this->setupUserTable($adapter, $driver);
                $this->mapper->setDbAdapter($adapter);
                $entity = $this->mapper->findById(1);
                $this->mapper->delete($entity);
                $updatedEntity = $this->mapper->findById(1);
                $this->assertNull($updatedEntity);
            }
        }
    }

    private static function setupAdapter(string $driver): void
    {
        $upCase = strtoupper($driver);
        if (
            ! defined(sprintf('DB_%s_DSN', $upCase))
            || ! defined(sprintf('DB_%s_USERNAME', $upCase))
            || ! defined(sprintf('DB_%s_PASSWORD', $upCase))
            || ! defined(sprintf('DB_%s_SCHEMA', $upCase))
        ) {
            return;
        }

        try {
            $connection = [
                'driver' => sprintf('Pdo_%s', ucfirst($driver)),
                'dsn'    => constant(sprintf('DB_%s_DSN', $upCase)),
            ];
            if (constant(sprintf('DB_%s_USERNAME', $upCase)) !== '') {
                $connection['username'] = (string) constant(sprintf('DB_%s_USERNAME', $upCase));
                $connection['password'] = (string) constant(sprintf('DB_%s_PASSWORD', $upCase));
            }
            $adapter = new Adapter($connection);
            self::setupSqlDatabase($adapter, (string) constant(sprintf('DB_%s_SCHEMA', $upCase)));
            self::$realAdapters[$driver] = $adapter;
        } catch (BaseException $exception) {
            self::$realAdapters[$driver] = false;
        }
    }

    private static function setupSqlDatabase(Adapter $adapter, string $schemaPath): void
    {
        $queryStack = ['DROP TABLE IF EXISTS user'];
        //$queryStack   = array_merge($queryStack, explode(';', file_get_contents($schemaPath)));
        //$queryStack   = array_merge($queryStack, explode(';', file_get_contents(__DIR__ . '/_files/user.sql')));

        foreach ($queryStack as $query) {
            /** @var string $query */
            if (! preg_match('/\S+/', $query)) {
                continue;
            }
            $adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
        }
    }

    private function setupUserTable(Adapter $adapter, string $driver): void
    {
        $upCase     = strtoupper($driver);
        $schemaPath = (string) constant(sprintf('DB_%s_SCHEMA', $upCase));
        $queryStack = ['DROP TABLE IF EXISTS user'];
        $queryStack = array_merge($queryStack, explode(';', file_get_contents($schemaPath)));
        $queryStack = array_merge($queryStack, explode(';', file_get_contents(__DIR__ . '/_files/user.sql')));
        foreach ($queryStack as $query) {
            /** @var string $query */
            if (! preg_match('/\S+/', $query)) {
                continue;
            }
            $adapter->query($query, Adapter::QUERY_MODE_EXECUTE);
        }
    }

    public static function providerTestFindBy(): array
    {
        $user = new Entity();
        $user->setEmail('lmc-user@github.com');
        $user->setUsername('lmc-user');
        $user->setDisplayName('Zfc-User');
        $user->setId(1);
        $user->setState(1);
        $user->setPassword('lmc-user');

        return [
            'findById = 1'                      => [
                'findById', // method
                [1], // method args
                [], // listener
                true, //expected
                1, // id
            ],
            'findByEmail = lmc-user@github.com' => [
                'findByEmail',
                ['lmc-user@github.com'],
                [],
                true,
                1,
            ],
            'findByUsername = lmc-user'         => [
                'findByUsername',
                ['lmc-user'],
                [],
                true,
                1,
            ],
            'findById = 2'                      => [
                'findById',
                [2],
                [],
                true,
                2,
            ],
            'findById = 3'                      => [
                'findById',
                [3],
                [],
                true,
                3,
            ],
            'findById = 4'                      => [
                'findById',
                [4],
                [],
                false,
                0,
            ],
            'findByEmail = foo'                 => [
                'findByEmail',
                ['foo'],
                [],
                false,
                0,
            ],
            'findByUsername = foo'              => [
                'findByUsername',
                ['foo'],
                [],
                false,
                0,
            ],
        ];
    }

    public function onEvent(Event $event): void
    {
        $this->assertEquals('find', $event->getName());
        $this->eventCalled = true;
        $this->eventEntity = $event->getParam('entity');
        $this->eventName   = $event->getName();
    }
}
