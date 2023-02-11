<?php

declare(strict_types=1);

namespace App\Tracking\Regex;

use App\Tracking\Exception\ConfigurationException;
use Psl\Type;
use TRegx\CleanRegex\Pattern;

class PlaceholdersResolver
{
    private readonly Pattern $placeholder;

    /**
     * @var array<string, Pattern>
     */
    private array $placeholderPatternCache = [];

    /**
     * @var array<string, string>
     */
    private array $placeholders = [];

    /**
     * @param psTrckRgxsPlaceholders $placeholders
     */
    public function __construct(array $placeholders)
    {
        $this->placeholder = pattern('^ ?[A-Z_&-]+ ?$', 'n');

        $this->loadPlaceholders($placeholders);
    }

    /**
     * @param array<string, string> $subject
     */
    public function resolve(array &$subject): void
    {
        $changed = false;

        foreach ($subject as &$resolved) {
            foreach ($this->placeholders as $placeholder => $replacement) {
                $pattern = $this->getPlaceholderPattern($placeholder);

                $replace = $pattern->replace($resolved);
                $resolved = $replace->with($replacement);

                $changed = $changed || $replace->count() > 0;
            }
        }

        if ($changed) {
            $this->resolve($subject);
        }
    }

    /**
     * @param psTrckRgxsPlaceholders $placeholders
     */
    private function loadPlaceholders(array $placeholders): void
    {
        $shape = Type\non_empty_dict(
            Type\non_empty_string(),
            Type\union(
                Type\non_empty_vec(Type\non_empty_string()),
                Type\non_empty_dict(
                    Type\non_empty_string(),
                    Type\non_empty_vec(Type\non_empty_string()),
                ),
            ),
        );

        $placeholders = $shape->assert($placeholders);

        $this->loadPlaceholderItem($placeholders, '', '');
        $this->resolve($this->placeholders);
    }

    /**
     * @param psTrckRgxsPlaceholders|array<string, list<string>>|list<string> $input
     */
    private function loadPlaceholderItem(array $input, string $groupName, string $path): string
    {
        if (array_is_list($input)) {
            /** @var list<string> $input grep-phpstan-var-typing */

            return $this->alternative($input, $groupName);
        }

        /** @var psTrckRgxsPlaceholders|array<string, list<string>> $input grep-phpstan-var-typing */
        return $this->loadMapPlaceholderItem($input, $groupName, $path);
    }

    /**
     * @param psTrckRgxsPlaceholders|array<string, list<string>> $input
     */
    private function loadMapPlaceholderItem(array $input, string $groupName, string $path): string
    {
        $placeholders = [];

        foreach ($input as $placeholder => $contents) {
            $parts = explode('=', $placeholder);
            $placeholder = $parts[0];
            $subItemNamedGroup = count($parts) > 1 ? $parts[1] : '';

            if (array_key_exists($placeholder, $this->placeholders)) {
                throw new ConfigurationException("Duplicated placeholder: '$placeholder'");
            }

            if ($this->placeholder->fails($placeholder)) {
                throw new ConfigurationException("Wrong placeholder: '$placeholder'");
            }

            $placeholders[] = $placeholder;

            $this->placeholders[$placeholder] = $this->loadPlaceholderItem($contents, $subItemNamedGroup, "$path/$placeholder");
        }

        return $this->alternative($placeholders, $groupName);
    }

    /**
     * @param list<string> $items
     */
    private function alternative(array $items, string $groupName): string
    {
        $groupName = '' === $groupName ? '' : "?P<$groupName>";

        return "($groupName".implode('|', $items).')';
    }

    private function getPlaceholderPattern(string $placeholder): Pattern
    {
        $start = str_starts_with($placeholder, ' ') ? '' : '(?<=^|[^A-Z_])';
        $end = str_ends_with($placeholder, ' ') ? '' : '(?=[^A-Z_]|$)';

        return $this->placeholderPatternCache[$placeholder] ??= Pattern::inject("$start@$end", [$placeholder]);
    }
}
