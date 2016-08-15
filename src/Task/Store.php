<?php

namespace zaboy\scheduler\Task;

use zaboy\async\StoreAbstract;
use zaboy\rest\DataStore\Interfaces\ReadInterface;

class Store extends StoreAbstract
{
    const SCHEDULE = 'schedule';
    const CALLBACK = 'callback';
    const ACTIVE = 'active';
}