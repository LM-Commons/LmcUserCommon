<?php

declare(strict_types=1);

namespace Lmc\User\Common\Entity;

abstract class AbstractUser implements UserInterface
{
    protected ?int $id = null;

    protected string $username = '';

    protected string $email = '';

    protected string $displayName = '';

    protected string $password = '';

    protected int $state = UserInterface::STATE_INVALID;

    /**
     * {@inheritDoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function setId(int $id): UserInterface
    {
        $this->id = $id;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * {@inheritDoc}
     */
    public function setUsername(string $username): UserInterface
    {
        $this->username = $username;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * {@inheritDoc}
     */
    public function setEmail(string $email): UserInterface
    {
        $this->email = $email;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * {@inheritDoc}
     */
    public function setDisplayName($displayName): UserInterface
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * {@inheritDoc}
     */
    public function setPassword(string $password): UserInterface
    {
        $this->password = $password;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * {@inheritDoc}
     */
    public function setState($state): UserInterface
    {
        $this->state = $state;
        return $this;
    }
}
