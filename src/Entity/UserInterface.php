<?php

declare(strict_types=1);

namespace Lmc\User\Core\Entity;

interface UserInterface
{
    public const STATE_INVALID  = 0;
    public const STATE_ACTIVE   = 1;
    public const STATE_INACTIVE = 2;
    public const STATE_DELETED  = 3;

    /**
     * Get id
     */
    public function getId(): ?int;

    /**
     * Set id
     */
    public function setId(int $id): UserInterface;

    /**
     * Get username.
     */
    public function getUsername(): string;

    /**
     * Set username.
     */
    public function setUsername(string $username): UserInterface;

    /**
     * Get email.
     */
    public function getEmail(): string;

    /**
     * Set email.
     */
    public function setEmail(string $email): UserInterface;

    /**
     * Get displayName.
     */
    public function getDisplayName(): string;

    /**
     * Set displayName.
     */
    public function setDisplayName(string $displayName): UserInterface;

    /**
     * Get password.
     */
    public function getPassword(): string;

    /**
     * Set password.
     */
    public function setPassword(string $password): UserInterface;

    /**
     * Get state.
     */
    public function getState(): int;

    /**
     * Set state.
     */
    public function setState(int $state): UserInterface;
}
