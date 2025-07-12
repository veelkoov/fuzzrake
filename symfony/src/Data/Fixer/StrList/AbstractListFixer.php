<?php

declare(strict_types=1);

namespace App\Data\Fixer\StrList;

use App\Data\Fixer\StrListFixerInterface;
use App\Utils\Collections\Arrays;
use App\Utils\PackedStringList;
use Override;

abstract class AbstractListFixer implements StrListFixerInterface
{
    #[Override]
    public function fix(array $subject): array
    {
        if (1 === count($subject) && null !== static::getSeparatorRegexp()) {
            $subject = PackedStringList::split(Arrays::single($subject), static::getSeparatorRegexp(), static::getNonsplittable($subject));
        }

        $subject = arr_map($subject, trim(...));
        $subject = iter_filter($subject, static fn (string $item) => '' !== $item);

        if (static::shouldSort()) {
            sort($subject);
        }

        return arr_map($subject, static::fixItem(...));
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
