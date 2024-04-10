<?php

declare(strict_types=1);

namespace App\Data\Fixer;

use App\Utils\PackedStringList;
use App\Utils\Regexp\Replacements;
use App\Utils\StrUtils;

abstract class AbstractListFixer extends StringFixer
{
    private readonly Replacements $replacements;

    /**
     * @param psFixerConfig $lists
     * @param psFixerConfig $strings
     */
    public function __construct(array $lists, array $strings)
    {
        parent::__construct($strings);

        $this->replacements = new Replacements($lists['replacements'], 'i', $lists['regex_prefix'], $lists['regex_suffix']);
    }

    public function fix(string $subject): string
    {
        $items = PackedStringList::split($subject, static::getSeparatorRegexp(), static::getNonsplittable($subject));
        $items = array_filter(array_map(fn (string $item): string => $this->fixItem($item), $items));

        $subject = PackedStringList::pack($items);
        $subject = $this->getReplacements()->do($subject);
        $subject = parent::fix($subject);
        $subject = PackedStringList::unpack($subject);

        if (static::shouldSort()) {
            sort($subject);
        }

        return PackedStringList::pack(array_unique($subject));
    }

    abstract protected static function shouldSort(): bool;

    abstract protected static function getSeparatorRegexp(): string;

    /**
     * @return string[]
     */
    protected function getNonsplittable(string $subject): array
    {
        return [];
    }

    protected function getReplacements(): Replacements
    {
        return $this->replacements;
    }

    private function fixItem(string $subject): string
    {
        $subject = trim($subject);

        if (!str_starts_with($subject, 'http')) {
            $subject = StrUtils::ucfirst($subject);
        }

        return $subject;
    }
}
