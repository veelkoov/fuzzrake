<?php

declare(strict_types=1);

namespace App\Tracking;

use App\Utils\ConfigurationException;
use TRegx\CleanRegex\Pattern;
use Veelkoov\Debris\Maps\StringToString;
use Veelkoov\Debris\StringList;

class PatternsLoader
{
    public readonly StringToString $cleaners;
    private readonly StringToString $placeholders;
    public readonly StringList $falsePositives;
    public readonly StringList $offersStatuses;

    /**
     * @param array{placeholders: array<mixed>, cleaners: array<mixed>, false_positives: array<mixed>, offers_statuses: array<mixed>} $patterns
     */
    public function __construct(array $patterns)
    {
        $this->cleaners = StringToString::fromUnsafe($patterns['cleaners'])->freeze();

        $this->placeholders = new StringToString();
        $this->loadPlaceholders($patterns['placeholders']);
        $this->placeholders->freeze();

        $this->falsePositives = StringList::fromUnsafe($patterns['false_positives'])->mapInto($this->resolve(...), new StringList())->freeze(); // grep-code-debris-needs-improvements
        $this->offersStatuses = StringList::fromUnsafe($patterns['offers_statuses'])->mapInto($this->resolve(...), new StringList())->freeze(); // grep-code-debris-needs-improvements
    }

    /**
     * @param array<mixed> $placeholders
     */
    private function loadPlaceholders(array $placeholders): string
    {
        $topPlaceholders = new StringList();

        foreach ($placeholders as $key => $value) {
            if (!is_array($value) || !is_string($key)) {
                throw new ConfigurationException("Key '$key' in placeholders is not a string or it does not hold an array.");
            }

            [$placeholder, $groupName] = $this->keyToPlaceholderAndGroupName($key);
            $topPlaceholders->add($placeholder);

            if (array_is_list($value)) {
                $alternatives = implode('|', $value);
            } else {
                $alternatives = $this->loadPlaceholders($value);
            }

            $groupNamePart = '' !== $groupName ? '?P<'.$groupName.'>' : $groupName;

            $this->placeholders->set($placeholder, "({$groupNamePart}{$alternatives})");
        }

        foreach ($this->placeholders->toArray() as $placeholder => $replacement) {
            $this->placeholders->set($placeholder, $this->resolve($replacement));
        }

        return $topPlaceholders->join('|');
    }

    /**
     * @return array{string, string}
     */
    private function keyToPlaceholderAndGroupName(string $key): array
    {
        if (!str_contains($key, '=')) {
            return [$key, ''];
        } else {
            $parts = explode('=', $key, 2);

            if (2 !== count($parts)) {
                throw new ConfigurationException("More than one '=' in placeholders key '$key'.");
            }

            return $parts;
        }
    }

    private function resolve(string $value): string
    {
        foreach ($this->placeholders as $placeholder => $replacement) { // TODO: Profile before merging
            $start = str_starts_with($placeholder, ' ') ? '' : '(?<=^|[^A-Z_])';
            $end = str_ends_with($placeholder, ' ') ? '' : '(?=[^A-Z_]|$)';

            $value = Pattern::inject("$start@$end", [$placeholder])->replace($value)->with($replacement);
        }

        return $value;
    }
}
