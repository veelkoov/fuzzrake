<?php

declare(strict_types=1);

namespace App\DataDefinitions\Fields;

enum Type
{
    case STRING;
    case STR_LIST;
    case DATE;
}
