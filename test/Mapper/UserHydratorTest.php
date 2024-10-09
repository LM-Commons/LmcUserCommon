<?php

declare(strict_types=1);

namespace LmcTest\User\Common\Mapper;

use Laminas\Hydrator\ClassMethodsHydrator;
use Lmc\User\Common\Entity\User as EntityUser;
use Lmc\User\Common\Mapper\UserHydrator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserHydrator::class)]
class UserHydratorTest extends TestCase
{
    public function testHydrate(): void
    {
        $hydrator = new UserHydrator(new ClassMethodsHydrator());
        $entity   = new EntityUser();
        $entity   = $hydrator->hydrate([
            'username'     => 'foo',
            'email'        => 'foo@bar.com',
            'display_name' => 'bar',
            'password'     => 'xyz',
            'state'        => 1,
            'user_id'      => 0,
        ], $entity);
        $this->assertEquals('foo', $entity->getUsername());
        $this->assertEquals('foo@bar.com', $entity->getEmail());
        $this->assertEquals('bar', $entity->getDisplayName());
        $this->assertEquals('xyz', $entity->getPassword());
        $this->assertEquals(1, $entity->getState());
        $this->assertEquals(0, $entity->getId());
    }

    public function testExtract(): void
    {
        $hydrator = new UserHydrator(new ClassMethodsHydrator());
        $entity   = new EntityUser();
        $entity->setUsername('foo');
        $entity->setEmail('foo@bar.com');
        $entity->setDisplayName('bar');
        $entity->setPassword('xyz');
        $entity->setState(1);
        $entity->setId(0);
        $data = $hydrator->extract($entity);
        $this->assertArrayNotHasKey('id', $data);
        $this->assertEquals([
            'username'     => 'foo',
            'email'        => 'foo@bar.com',
            'display_name' => 'bar',
            'password'     => 'xyz',
            'state'        => 1,
            'user_id'      => 0,
        ], $data);
    }

    public function testExtractNullId()
    {
        $entity   = new EntityUser();
        $hydrator = new UserHydrator(new ClassMethodsHydrator());
        $data     = $hydrator->extract($entity);
        $this->assertArrayNotHasKey('id', $data);
    }
}
