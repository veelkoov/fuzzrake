<?php

declare(strict_types=1);

namespace App\Tracker;

use App\Utils\Regexp\Replacements;
use Nette\Utils\Arrays;
use TRegx\CleanRegex\Pattern;

class PatternProvider
{
    /**
     * @var Pattern[]
     */
    private array $falsePositives;

    /**
     * @var Pattern[]
     */
    private array $offerStatuses;

    /**
     * @var string[][]
     */
    private array $groupTranslations;

    private Replacements $cleaners;

    public function __construct(
        RegexesProvider $regexPersistence,
    ) {
        $regexes = $regexPersistence->getRegexes();

        $this->groupTranslations = $regexes->getGroupTranslations();
        $this->falsePositives = Arrays::map($regexes->getFalsePositives(), fn ($item) => pattern($item, 's'));
        $this->offerStatuses = Arrays::map($regexes->getOfferStatuses(), fn ($item) => pattern($item, 's'));
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
