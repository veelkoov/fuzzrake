<?php

declare(strict_types=1);

namespace App\Utils\Data\Validator;

use App\Utils\Artisan\Field;
use App\Utils\StringList;

class SpeciesListValidator implements ValidatorInterface
{
    /**
     * @var string|array[]
     */
    private $validChoices;

    public function __construct(array $species)
    {
        $this->validChoices = $this->gatherValidChoices($species['valid_choices']);
    }

    public function validate(Field $field, $subject): bool
    {
        foreach (StringList::unpack($subject) as $specie) {
            if (!in_array($specie, $this->validChoices)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string[] $species
     */
    private function gatherValidChoices(array $species): array
    {
        $result = array_keys($species);

        foreach ($species as $specie => $subspecies) {
            if (is_array($subspecies)) {
                $result = array_merge($result, $this->gatherValidChoices($subspecies));
            }
        }

        return $result;
    }
}
