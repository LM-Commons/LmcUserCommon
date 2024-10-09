<?php

declare(strict_types=1);

namespace Lmc\User\Common\EventManager;

use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerAwareTrait;

/** @deprecated This class is no longer used */
class EventProvider implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;
}
