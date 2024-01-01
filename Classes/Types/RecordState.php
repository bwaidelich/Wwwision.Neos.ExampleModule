<?php
declare(strict_types=1);

namespace Wwwision\Neos\ExampleModule\Types;

enum RecordState : string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
