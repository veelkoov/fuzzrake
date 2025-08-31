<?php

declare(strict_types=1);

namespace App\Tracking\Patterns;

use App\Utils\ConfigurationException;
use Composer\Pcre\Preg;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Veelkoov\Debris\Lists\StringList;
use Veelkoov\Debris\Maps\StringToString;

class RegexesLoader
{
    public readonly StringToString $cleaners;
    private readonly StringToString $tokensReplacements;
    public readonly StringList $falsePositives;
    public readonly StringList $offersStatuses;

    /**
     * @param array{tokens_replacements: array<mixed>, cleaners: array<mixed>, false_positives: array<mixed>, offers_statuses: array<mixed>} $patterns
     */
    public function __construct(
        #[Autowire(param: 'tracking')]
        array $patterns,
    ) {
        $this->cleaners = StringToString::fromUnsafe($patterns['cleaners'])->freeze();

        $this->tokensReplacements = new StringToString();
        $this->loadTokensReplacements($patterns['tokens_replacements']);
        $this->tokensReplacements->freeze();

        $this->falsePositives = StringList::fromUnsafe($patterns['false_positives'])->map($this->resolve(...))->freeze();
        $this->offersStatuses = StringList::fromUnsafe($patterns['offers_statuses'])->map($this->resolve(...))->freeze();
    }

    /**
     * @param array<mixed> $tokensReplacements
     */
    private function loadTokensReplacements(array $tokensReplacements): string
    {
        $topTokens = new StringList();

        foreach ($tokensReplacements as $key => $value) {
            if (!is_array($value) || !is_string($key)) {
                throw new ConfigurationException("Key '$key' in tokens replacements is not a string or it does not hold an array.");
            }

            [$token, $groupName] = Token::extractGroupName($key);
            $topTokens->add($token);

            if (array_is_list($value)) {
                $alternatives = implode('|', $value);
            } else {
                $alternatives = $this->loadTokensReplacements($value);
            }

            $groupNamePart = '' !== $groupName ? '?P<'.$groupName.'>' : $groupName;

            $this->tokensReplacements->set($token, "({$groupNamePart}{$alternatives})");
        }

        foreach ($this->tokensReplacements->toArray() as $token => $replacement) {
            $this->tokensReplacements->set($token, $this->resolve($replacement));
        }

        return $topTokens->join('|');
    }

    private function resolve(string $subject): string
    {
        return Preg::replace(
            $this->tokensReplacements->getKeys()->map(Token::getPattern(...))->getValuesArray(),
            $this->tokensReplacements->getValuesArray(),
            $subject);
    }
}
