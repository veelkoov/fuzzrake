<?php

declare(strict_types=1);

namespace App\Tracking\Regex;

use TRegx\CleanRegex\Match\Detail;
use TRegx\CleanRegex\Pattern;

readonly class WorkaroundJ
{
    private Pattern $indexPattern;
    private Pattern $namedGroup;

    public function __construct()
    {
        $this->indexPattern = pattern('_\d+$');
        $this->namedGroup = pattern('\(\?P<(?P<group_name>[a-z_]+)>', 'i');
    }

    public function apply(string $regex): string
    {
        $i = 0;

        return $this->namedGroup->replace($regex)->callback(function (Detail $match) use (&$i) {
            ++$i;

            return '(?P<'.$match->group('group_name')->text().'_'.$i.'>';
        });
    }

    public function remove(string $groupName): string
    {
        return $this->indexPattern->prune($groupName);
    }
}
