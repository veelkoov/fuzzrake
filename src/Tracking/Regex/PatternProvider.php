<?php

declare(strict_types=1);

namespace App\Tracking\Regex;

use App\Utils\Regexp\Replacements;
use TRegx\CleanRegex\Pattern;

use function Psl\Vec\map;

class PatternProvider
{
    /**
     * @var Pattern[]
     */
    private readonly array $falsePositives;

    /**
     * @var Pattern[]
     */
    private readonly array $offerStatuses;

    /**
     * @var string[][]
     */
    private readonly array $groupTranslations;

    private readonly Replacements $cleaners;

    public function __construct(
        RegexesProvider $regexPersistence,
    ) {
        $regexes = $regexPersistence->getRegexes();

        $this->groupTranslations = $regexes->getGroupTranslations();
        $this->falsePositives = map($regexes->getFalsePositives(), fn ($item) => pattern($item, 's'));
        $this->offerStatuses = map($regexes->getOfferStatuses(), fn ($item) => pattern($item, 's'));
        $this->cleaners = new Replacements($regexes->getCleaners(), 's', '', '');
    }

    /**
     * @return Pattern[]
     */
    public function getOfferStatuses(): array
    {
        return $this->offerStatuses;
    }

    /**
     * @return Pattern[]
     */
    public function getFalsePositives(): array
    {
        return $this->falsePositives;
    }

    /**
     * @return string[][]
     */
    public function getGroupTranslations(): array
    {
        return $this->groupTranslations;
    }

    public function getCleaners(): Replacements
    {
        return $this->cleaners;
    }
}
