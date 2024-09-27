<?php

declare(strict_types=1);

namespace Lmc\User\Core\Mapper;

use Laminas\Hydrator\HydratorInterface;
use Lmc\User\Core\Entity\UserInterface as UserEntityInterface;

use function assert;

class UserHydrator implements HydratorInterface
{
    private HydratorInterface $hydrator;

    public function __construct(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * Extract values from an object
     */
    public function extract(object $object): array
    {
        assert($object instanceof UserEntityInterface);

        $data = $this->hydrator->extract($object);
        if ($data['id'] !== null) {
            $data = $this->mapField('id', 'user_id', $data);
        } else {
            unset($data['id']);
        }

        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     */
    public function hydrate(array $data, object $object): UserEntityInterface
    {
        assert($object instanceof UserEntityInterface);

        $data = $this->mapField('user_id', 'id', $data);

        return $this->hydrator->hydrate($data, $object);
    }

    /** @psalm-suppress MixedAssignment */
    protected function mapField(string $keyFrom, string $keyTo, array $array): array
    {
        if (isset($array[$keyFrom])) {
            $array[$keyTo] = $array[$keyFrom];
            unset($array[$keyFrom]);
        }

        return $array;
    }
}
