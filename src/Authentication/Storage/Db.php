<?php

declare(strict_types=1);

namespace Lmc\User\Common\Authentication\Storage;

use Laminas\Authentication\Exception\ExceptionInterface;
use Laminas\Authentication\Storage\StorageInterface;
use Lmc\User\Common\Entity\UserInterface;
use Lmc\User\Common\Mapper\UserMapperInterface;

use function is_int;
use function is_scalar;

class Db implements StorageInterface
{
    protected StorageInterface $storage;

    protected UserMapperInterface $userMapper;

    protected mixed $resolvedIdentity;

    public function __construct(UserMapperInterface $userMapper, StorageInterface $storage)
    {
        $this->userMapper       = $userMapper;
        $this->storage          = $storage;
        $this->resolvedIdentity = null;
    }

    /**
     * Returns true if and only if storage is empty
     *
     * @throws ExceptionInterface
     */
    public function isEmpty(): bool
    {
        if ($this->getStorage()->isEmpty()) {
            return true;
        }
        /** @var ?int $identity */
        $identity = $this->getStorage()->read();
        if ($identity === null) {
            $this->clear();
            return true;
        }

        return false;
    }

    /**
     * Returns the contents of storage
     *
     * Behavior is undefined when storage is empty.
     *
     * @throws ExceptionInterface
     */
    public function read(): mixed
    {
        if (null !== $this->resolvedIdentity) {
            return $this->resolvedIdentity;
        }

        /** @var UserInterface|scalar|null $identity */
        $identity = $this->getStorage()->read();

        if (is_int($identity) || is_scalar($identity)) {
            $identity = $this->getMapper()->findById($identity);
        }

        if ($identity) {
            $this->resolvedIdentity = $identity;
        } else {
            $this->resolvedIdentity = null;
        }

        return $this->resolvedIdentity;
    }

    /**
     * Writes $contents to storage
     *
     * @param mixed $contents
     * @throws ExceptionInterface
     */
    public function write($contents): void
    {
        $this->resolvedIdentity = null;
        $this->getStorage()->write($contents);
    }

    /**
     * Clears contents from storage
     *
     * @throws ExceptionInterface
     */
    public function clear(): void
    {
        $this->resolvedIdentity = null;
        $this->getStorage()->clear();
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * setStorage
     *
     * @deprecated the mapper should be injected in the constructor
     */
    public function setStorage(StorageInterface $storage): static
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * getMapper
     */
    public function getMapper(): UserMapperInterface
    {
        return $this->userMapper;
    }

    /**
     * setMapper
     *
     * @deprecated the mapper should be injected in the constructor
     */
    public function setMapper(UserMapperInterface $userMapper): static
    {
        $this->userMapper = $userMapper;
        return $this;
    }
}
