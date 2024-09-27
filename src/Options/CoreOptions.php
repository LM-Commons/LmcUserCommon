<?php

declare(strict_types=1);

namespace Lmc\User\Core\Options;

use Laminas\Stdlib\AbstractOptions;
use Lmc\User\Core\Entity\User;
use Lmc\User\Core\Entity\UserInterface;
use Webmozart\Assert\Assert;

/**
 * @template TValue
 */
class CoreOptions extends AbstractOptions
{
    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore,WebimpressCodingStandard.NamingConventions.ValidVariableName.NotCamelCapsProperty
    /**
     * Turn off strict options mode
     *
     * @var bool $__strictMode__
     */
    protected $__strictMode__ = false;
    // phpcs:enable


    protected string $userEntityClass = User::class;

    protected string $tableName = 'user';

     /**
      * set user entity class name
      */
    public function setUserEntityClass(string $userEntityClass): CoreOptions
    {
        Assert::classExists($userEntityClass);
        Assert::implementsInterface($userEntityClass, UserInterface::class,);
        $this->userEntityClass = $userEntityClass;
        return $this;
    }

    /**
     * get user entity class name
     */
    public function getUserEntityClass(): string
    {
        return $this->userEntityClass;
    }

    /**
     * set user table name
     */
    public function setTableName(string $tableName): CoreOptions
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * get user table name
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
}
