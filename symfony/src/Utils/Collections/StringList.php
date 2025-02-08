<?php

declare(strict_types=1);

namespace App\Utils\Collections;

use App\Utils\PackedStringList;

final class StringList extends \Veelkoov\Debris\StringList
{
    public static function unpack(string $input): self
    {
        return new self(PackedStringList::unpack($input));
    }
}
