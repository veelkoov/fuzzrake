<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\Collections\StringList;
use App\Utils\Regexp\Replacements;
use InvalidArgumentException;

class SpeciesService
{
    public readonly StringList $validNames;

    /**
     * @param array{replacements: array<string, string>, regex_prefix: string, regex_suffix: string, leave_unchanged: string[], valid_choices: array<string, mixed>} $speciesDefinitions
     */
    public function __construct(
        private readonly array $speciesDefinitions,
    ) {
        $this->validNames = $this->getValidNamesFromArray($this->speciesDefinitions['valid_choices']);
    }

    /**
     * @param array<mixed> $input
     */
    private function getValidNamesFromArray(array $input): StringList
    {
        $result = StringList::mut();

        foreach ($input as $key => $value) {
            $result->add((string) $key);

            if (is_array($value)) {
                $result->addAll($this->getValidNamesFromArray($value));
            } elseif (null !== $value) {
                throw new InvalidArgumentException('Expected an array with string keys and null or array values');
            }
        }

        return $result->unique()->frozen();
    }

    public function getListFixerReplacements(): Replacements
    {
        return new Replacements($this->speciesDefinitions['replacements'], 'i',
            $this->speciesDefinitions['regex_prefix'], $this->speciesDefinitions['regex_suffix']);
    }
}
