<?php

declare(strict_types=1);

namespace Lmc\User\Common\Mapper;

use Closure;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Lmc\User\Common\Entity\UserInterface as EntityUserInterface;

use function assert;

class User extends AbstractDbMapper implements UserMapperInterface
{
    protected string $table = 'user';

    public function findByEmail(string $email): ?EntityUserInterface
    {
        $select = $this->getSelect()->where(['email' => $email]);
        $entity = $this->innerSelect($select)->current();
        assert($entity instanceof EntityUserInterface || $entity === null);
        $this->getEventManager()->trigger('find', $this, ['entity' => $entity]);
        return $entity;
    }

    public function findByUsername(string $username): ?EntityUserInterface
    {
        $select = $this->getSelect()->where(['username' => $username]);
        $entity = $this->innerSelect($select)->current();
        assert($entity instanceof EntityUserInterface || $entity === null);
        $this->getEventManager()->trigger('find', $this, ['entity' => $entity]);
        return $entity;
    }

    public function findById(int $id): ?EntityUserInterface
    {
        $select = $this->getSelect()->where(['user_id' => $id]);
        $entity = $this->innerSelect($select)->current();
        assert($entity instanceof EntityUserInterface || $entity === null);
        $this->getEventManager()->trigger('find', $this, ['entity' => $entity]);
        return $entity;
    }

    public function insert(EntityUserInterface $entity): ResultInterface
    {
        $result = $this->innerInsert($entity);
        assert($result instanceof ResultInterface);
        $id = (int) $result->getGeneratedValue();
        $entity->setId($id);
        return $result;
    }

    public function update(EntityUserInterface $entity): ResultInterface
    {
        $where = ['user_id' => $entity->getId()];
        return $this->innerUpdate($entity, $where);
    }

    public function delete(
        EntityUserInterface $entity,
        string|array|Closure|null $where = null,
        ?string $tableName = null
    ): ResultInterface {
        if ($where === null) {
            $where = ['user_id' => $entity->getId()];
        }
        return $this->innerDelete($entity, $where, $tableName);
    }
}
