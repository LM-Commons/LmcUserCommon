<?php

declare(strict_types=1);

namespace Lmc\User\Core\Mapper;

use Closure;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Hydrator\HydratorInterface;
use Lmc\User\Core\Entity\UserInterface as EntityUserInterface;

interface UserInterface
{
    public function findByEmail(string $email): ?EntityUserInterface;

    public function findByUsername(string $username): ?EntityUserInterface;

    public function findById(int $id): ?EntityUserInterface;

    public function insert(EntityUserInterface $entity): ResultInterface;

    public function update(EntityUserInterface $entity): ResultInterface;

    public function delete(EntityUserInterface $entity): ResultInterface;
}
