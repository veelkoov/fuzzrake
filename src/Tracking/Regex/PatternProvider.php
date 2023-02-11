<?php

declare(strict_types=1);

namespace App\Tracking\Regex;

use App\Utils\Regexp\Replacements;
use TRegx\CleanRegex\Pattern;
use TRegx\CleanRegex\PatternList;

use function Psl\Vec\map;

readonly class PatternProvider
{
    /**
     * @var list<Pattern>
     */
    private array $offerStatuses;

    private PatternList $falsePositives;
    private Replacements $cleaners;

    public function __construct(
        RegexesProvider $regexPersistence,
    ) {
        $regexes = $regexPersistence->getRegexes();

        $this->falsePositives = Pattern::list(map($regexes->getFalsePositives(), fn ($item) => pattern($item, 'sn')));
        $this->offerStatuses = map($regexes->getOfferStatuses(), fn ($item) => pattern($item, 'sn'));
        $this->cleaners = new Replacements($regexes->getCleaners(), 's', '', '');
    }

    /**
     * @return list<Pattern>
     */
    public function getOfferStatuses(): array
    {
        return $this->offerStatuses;
    }

    public function getFalsePositives(): PatternList
    {
        return $this->falsePositives;
    }

    public function getCleaners(): Replacements
    {
        return $this->cleaners;
    }
}
