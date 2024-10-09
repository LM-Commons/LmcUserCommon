<?php

declare(strict_types=1);

namespace LmcTest\User\Core\Authentication\Storage;

use Laminas\Authentication\Exception\ExceptionInterface;
use Laminas\Authentication\Storage\Session;
use Laminas\Authentication\Storage\StorageInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\Hydrator\HydratorInterface;
use Lmc\User\Core\Authentication\Storage\Db;
use Lmc\User\Core\Entity\User;
use Lmc\User\Core\Entity\UserInterface;
use Lmc\User\Core\Mapper\User as UserMapper;
use Lmc\User\Core\Mapper\UserMapperInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Db::class)]
class DbTest extends TestCase
{
    /**
     * @throws Exception|ExceptionInterface
     */
    public function testIsEmptyWhenStorageIsEmpty(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())->method('isEmpty')->willReturn(true);
        $db = new Db(
            $this->createMock(UserMapperInterface::class),
            $storage
        );
        $this->assertTrue($db->isEmpty());
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function testIsEmptyWhenIdentityIsNull(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())->method('isEmpty')->willReturn(false);
        $storage->expects($this->once())->method('read')->willReturn(null);
        $db = new Db(
            $this->createMock(UserMapperInterface::class),
            $storage
        );
        $this->assertTrue($db->isEmpty());
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function testIsEmptyWhenIdentityIsNotNull(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())->method('isEmpty')->willReturn(false);
        $storage->expects($this->once())->method('read')->willReturn(new User());
        $db = new Db(
            $this->createMock(UserMapperInterface::class),
            $storage
        );
        $this->assertFalse($db->isEmpty());
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function testReadWithResolvedEntitySet(): void
    {
        $db                 = new Db(
            $this->createMock(UserMapperInterface::class),
            $this->createMock(StorageInterface::class)
        );
        $reflectionClass    = new ReflectionClass(Db::class);
        $reflectionProperty = $reflectionClass->getProperty('resolvedIdentity');
        $reflectionProperty->setValue($db, 'lmcUser');

        $this->assertSame('lmcUser', $db->read());
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function testReadWithoutResolvedEntitySetIdentityIntUserFound(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $mapper  = $this->createMock(UserMapperInterface::class);
        $storage->expects($this->once())
            ->method('read')
            ->willReturn(1);

        $user = $this->createMock(User::class);
        $user->setUsername('lmcUser');

        $mapper->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($user);

        $db = new Db($mapper, $storage);

        $result = $db->read();

        $this->assertSame($user, $result);
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function testReadWithoutResolvedEntitySetIdentityIntUserNotFound(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $mapper  = $this->createMock(UserMapperInterface::class);
        $storage->expects($this->once())
            ->method('read')
            ->willReturn(1);

        $mapper->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn(null);

        $db = new Db($mapper, $storage);

        $result = $db->read();

        $this->assertNull($result);
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function testReadWithoutResolvedEntitySetIdentityObject(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $mapper  = $this->createMock(UserMapperInterface::class);

        $user = $this->createMock(User::class);
        $user->setUsername('lmcUser');

        $storage->expects($this->once())
            ->method('read')
            ->willReturn($user);

        $db = new Db($mapper, $storage);

        $result = $db->read();

        $this->assertSame($user, $result);
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function testWrite(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $mapper  = $this->createMock(UserMapperInterface::class);

        $reflectionClass    = new ReflectionClass(Db::class);
        $reflectionProperty = $reflectionClass->getProperty('resolvedIdentity');

        $storage->expects($this->once())
            ->method('write')
            ->with('lmcUser');

        $db = new Db($mapper, $storage);

        $db->write('lmcUser');

        $this->assertNull($reflectionProperty->getValue($db));
    }

    /**
     * @throws ExceptionInterface
     * @throws Exception
     */
    public function testClear(): void
    {
        $reflectionClass    = new ReflectionClass(Db::class);
        $reflectionProperty = $reflectionClass->getProperty('resolvedIdentity');
        //$reflectionProperty->setAccessible(true);

        $storage = $this->createMock(StorageInterface::class);
        $mapper  = $this->createMock(UserMapperInterface::class);

        $storage->expects($this->once())
            ->method('clear');

        $db = new Db($mapper, $storage);

        $db->clear();

        $this->assertNull($reflectionProperty->getValue($db));
    }

    /**
     * @throws Exception
     */
    public function testSetGetMapper(): void
    {
        $mapper = new UserMapper(
            $this->createMock(Adapter::class),
            'foo',
            $this->createMock(HydratorInterface::class),
            $this->createMock(UserInterface::class)
        );
        $mapper->setTableName('lmcUser');

        $db = new Db(
            $this->createMock(UserMapperInterface::class),
            $this->createMock(StorageInterface::class),
        );
        $db->setMapper($mapper);

        $this->assertInstanceOf(UserMapper::class, $db->getMapper());
        $this->assertSame('lmcUser', $db->getMapper()->getTableName());
    }

    /**
     * @throws Exception
     */
    public function testSetGetStorage(): void
    {
        $db = new Db(
            $this->createMock(UserMapperInterface::class),
            $this->createMock(StorageInterface::class),
        );

        $storage = new Session('LmcUserStorage');
        $db->setStorage($storage);

        $this->assertInstanceOf(Session::class, $db->getStorage());
    }
}
