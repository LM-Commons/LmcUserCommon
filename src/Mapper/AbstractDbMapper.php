<?php

declare(strict_types=1);

namespace Lmc\User\Core\Mapper;

use Closure;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\Hydrator\HydratorInterface;
use Lmc\User\Core\Db\Adapter\MasterSlaveAdapterInterface;
use Lmc\User\Core\Entity\UserInterface as UserEntityInterface;

abstract class AbstractDbMapper implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    protected Adapter $dbAdapter;

    protected ?Adapter $dbSlaveAdapter = null;

    protected HydratorInterface $hydrator;

    protected ?HydratingResultSet $resultSetPrototype = null;

    protected UserEntityInterface $entityPrototype;

    protected string $tableName = 'user';

    protected ?Sql $sql = null;

    protected ?Sql $slaveSql = null;

    public function __construct(
        Adapter $dbAdapter,
        string $tableName,
        HydratorInterface $hydrator,
        UserEntityInterface $entityPrototype
    ) {
        $this->setDbAdapter($dbAdapter);
        $this->tableName       = $tableName;
        $this->hydrator        = $hydrator;
        $this->entityPrototype = $entityPrototype;
    }

    protected function getSelect(?string $tableName = null): Select
    {
        return $this->getSlaveSql()->select($tableName ?? $this->getTableName());
    }

    protected function innerSelect(
        Select $select,
        ?UserEntityInterface $entityPrototype = null,
        ?HydratorInterface $hydrator = null,
    ): HydratingResultSet {
        $statement = $this->getSlaveSql()->prepareStatementForSqlObject($select);
        $resultSet = new HydratingResultSet(
            $hydrator ?: $this->getHydrator(),
            $entityPrototype ?: $this->getEntityPrototype()
        );
        $resultSet->initialize($statement->execute());
        return $resultSet;
    }

    protected function innerInsert(
        UserEntityInterface $entity,
        ?string $tableName = null,
        ?HydratorInterface $hydrator = null
    ): ResultInterface {
        $sql     = $this->getSql()->setTable($tableName ?? $this->getTableName());
        $insert  = $sql->insert();
        $rowData = $this->entityToArray($entity, $hydrator);
        $insert->values($rowData);
        $statement = $sql->prepareStatementForSqlObject($insert);
        return $statement->execute();
    }

    protected function innerUpdate(
        UserEntityInterface $entity,
        string|array|Closure $where,
        ?string $tableName = null,
        ?HydratorInterface $hydrator = null
    ): ResultInterface {
        $sql     = $this->getSql()->setTable($tableName ?? $this->getTableName());
        $update  = $sql->update();
        $rowData = $this->entityToArray($entity, $hydrator);
        $update->set($rowData)->where($where);
        $statement = $sql->prepareStatementForSqlObject($update);
        return $statement->execute();
    }

    protected function innerDelete(
        UserEntityInterface $entity,
        string|array|Closure $where,
        ?string $tableName = null
    ): ResultInterface {
        $sql    = $this->getSql()->setTable($tableName ?? $this->getTableName());
        $delete = $sql->delete();
        $delete->where($where);
        $statement = $sql->prepareStatementForSqlObject($delete);
        return $statement->execute();
    }

    protected function getSql(): Sql
    {
        if ($this->sql === null) {
            $this->sql = new Sql($this->dbAdapter);
        }
        return $this->sql;
    }

    protected function getSlaveSql(): Sql
    {
        if ($this->slaveSql === null) {
            $this->slaveSql = new Sql($this->getDbAdapter());
        }
        return $this->slaveSql;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName): AbstractDbMapper
    {
        $this->tableName = $tableName;
        return $this;
    }

    protected function entityToArray(
        UserEntityInterface $entity,
        ?HydratorInterface $hydrator
    ): array {
        if ($hydrator === null) {
            $hydrator = $this->getHydrator();
        }
        return $hydrator->extract($entity);
    }

    public function getEntityPrototype(): UserEntityInterface
    {
        return $this->entityPrototype;
    }

    public function setEntityPrototype(UserEntityInterface $entityPrototype): AbstractDbMapper
    {
        $this->entityPrototype = $entityPrototype;
        return $this;
    }

    public function getDbAdapter(): Adapter
    {
        return $this->dbAdapter;
    }

    public function setDbAdapter(Adapter $dbAdapter): AbstractDbMapper
    {
        $this->dbAdapter = $dbAdapter;
        if ($dbAdapter instanceof MasterSlaveAdapterInterface) {
            $this->setDbSlaveAdapter($dbAdapter->getSlaveAdapter());
        }
        return $this;
    }

    public function getDbSlaveAdapter(): Adapter
    {
        return $this->dbSlaveAdapter ?: $this->dbAdapter;
    }

    public function setDbSlaveAdapter(Adapter $dbSlaveAdapter): AbstractDbMapper
    {
        $this->dbSlaveAdapter = $dbSlaveAdapter;
        return $this;
    }

    public function getHydrator(): HydratorInterface
    {
        return $this->hydrator;
    }

    public function setHydrator(HydratorInterface $hydrator): AbstractDbMapper
    {
        $this->hydrator           = $hydrator;
        $this->resultSetPrototype = null;
        return $this;
    }
}
