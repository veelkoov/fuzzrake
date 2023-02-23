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
        return $this->namedGroup->replace($regex)->callback(function (Detail $match): string {
            return '(?P<'.$match->get('group_name').'_'.($match->index()+1).'>';
        });
    }

    public function remove(string $groupName): string
    {
        return $this->indexPattern->prune($groupName);
    }
}
