<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Regexp;
use App\Utils\StringList;
use App\Utils\StrUtils;

abstract class AbstractListFixer extends StringFixer
{
    /**
     * @var string[]
     */
    private array $replacements;

    public function __construct(array $lists, array $strings)
    {
        parent::__construct($strings);

        $this->replacements = $lists['replacements'];
    }

    public function fix(string $fieldName, string $subject): string
    {
        $items = StringList::split($subject, static::getSeparatorRegexp(), static::getNonsplittable());
        $items = array_filter(array_map([$this, 'fixItem'], $items));

        $subject = StringList::pack($items);
        $subject = Regexp::replaceAll($this->getReplacements(), $subject, "#(?<=^|\n)", "(?=\n|$)#i");
        $subject = parent::fix($fieldName, $subject);
        $subject = StringList::unpack($subject);

        if (static::shouldSort()) {
            sort($subject);
        }

        return StringList::pack(array_unique($subject));
    }

    abstract protected static function shouldSort(): bool;

    abstract protected static function getSeparatorRegexp(): string;

    /**
     * @return string[]
     */
    protected function getNonsplittable(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    protected function getReplacements(): array
    {
        return $this->replacements;
    }

    private function fixItem(string $subject): string
    {
        $subject = trim($subject);

        if ('http' !== substr($subject, 0, 4)) {
            $subject = StrUtils::ucfirst($subject);
        }

        return $subject;
    }
}
