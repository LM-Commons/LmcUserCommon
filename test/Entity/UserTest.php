<?php

declare(strict_types=1);

namespace LmcTest\User\Core\Entity;

use Lmc\User\Core\Entity\AbstractUser;
use Lmc\User\Core\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
#[CoversClass(AbstractUser::class)]
class UserTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testSetGetId(): void
    {
        $this->user->setId(1);
        $this->assertEquals(1, $this->user->getId());
    }

    public function testSetGetEmail(): void
    {
        $this->user->setEmail('foo@bar.baz');
        $this->assertEquals('foo@bar.baz', $this->user->getEmail());
    }

    public function testSetGetUsername(): void
    {
        $this->user->setUsername('foo');
        $this->assertEquals('foo', $this->user->getUsername());
    }

    public function testSetGetDisplayName(): void
    {
        $this->user->setDisplayName('foo');
        $this->assertEquals('foo', $this->user->getDisplayName());
    }

    public function testSetGetPassword(): void
    {
        $this->user->setPassword('foo');
        $this->assertEquals('foo', $this->user->getPassword());
    }

    public function testSetGetState(): void
    {
        $this->user->setState(1);
        $this->assertEquals(1, $this->user->getState());
    }
}
