<?php

declare(strict_types=1);

namespace App\Tracker;

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

    public function __construct(
        RegexesProvider $regexPersistence,
    ) {
        $regexes = $regexPersistence->getRegexes();

        $this->groupTranslations = $regexes->getGroupTranslations();
        $this->falsePositives = Arrays::map($regexes->getFalsePositives(), fn ($item) => pattern($item, 's'));
        $this->offerStatuses = Arrays::map($regexes->getOfferStatuses(), fn ($item) => pattern($item, 's'));
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
}
