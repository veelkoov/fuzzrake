<?php

namespace App\Data\Fixer\StrList;

use App\Data\Fixer\StrListFixerInterface;
use App\Utils\Arrays\Arrays;
use App\Utils\PackedStringList;
use Psl\Vec;

abstract class AbstractListFixer implements StrListFixerInterface
{
    public function fix(array $subject): array
    {
        if (1 === count($subject) && null !== static::getSeparatorRegexp()) {
            $subject = PackedStringList::split(Arrays::single($subject), static::getSeparatorRegexp(), static::getNonsplittable($subject));
        }

        $subject = Vec\map($subject, trim(...));
        $subject = Vec\filter($subject, fn (string $item): bool => '' !== $item);

        if (static::shouldSort()) {
            sort($subject);
        }

        return Vec\map($subject, static::fixItem(...));
    }

    abstract protected function fixItem(string $subject): string;

    protected function shouldSort(): bool
    {
        return false;
    }

    protected function getSeparatorRegexp(): ?string
    {
        return '[,]|[, ]and ';
    }

    /**
     * @param list<string> $subject
     *
     * @return list<string>
     */
    protected function getNonsplittable(array $subject): array
    {
        return [];
    }
}
