<?php

declare(strict_types=1);

namespace Lmc\User\Common\Options;

use Laminas\Stdlib\AbstractOptions;

class ChainableAdapterConfig extends AbstractOptions
{
    public const DEFAULT_PRIORITY = 100;

    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore,WebimpressCodingStandard.NamingConventions.ValidVariableName.NotCamelCapsProperty
    /**
     * Turn off strict options mode
     *
     * @var bool $__strictMode__
     */
    protected $__strictMode__ = false;
    // phpcs:enable

    protected string $name = '';

    protected int $priority = self::DEFAULT_PRIORITY;

    protected array $options = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
