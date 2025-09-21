<?php

declare(strict_types=1);

namespace App\Utils\Regexp;

use Composer\Pcre\MatchAllStrictGroupsResult;
use Composer\Pcre\MatchResult;
use Composer\Pcre\MatchStrictGroupsResult;
use Composer\Pcre\Preg;
use Composer\Pcre\Regex;
use InvalidArgumentException;

final class Pattern
{
    private const string PLACEHOLDER = '@';
    private const string DELIMITER = '~';
    private const string ESCAPED_DELIMITER = '\~';

    /**
     * @var non-empty-string
     */
    public private(set) string $compiled;

    public function __construct(string $regex, string $flags = '')
    {
        $this->compiled = self::DELIMITER.Pattern::escapeDelimiters($regex).self::DELIMITER."$flags";
    }

    /**
     * @param list<string> $details
     */
    public static function fromTemplate(string $template, array $details, string $flags = ''): self
    {
        if (mb_substr_count($template, self::PLACEHOLDER) !== count($details)) {
            throw new InvalidArgumentException('Mismatched count of placeholders and details.');
        }

        $template = Pattern::escapeDelimiters($template);

        foreach ($details as $detail) {
            $template = str_replace_limit(self::PLACEHOLDER, preg_quote($detail, self::DELIMITER), $template, 1);
        }

        $result = new self('', '');
        $result->compiled = self::DELIMITER.$template.self::DELIMITER.$flags;

        return $result;
    }

    public function prune(string $subject): string
    {
        return Preg::replace($this->compiled, '', $subject);
    }

    /**
     * @return list<string>
     */
    public function split(string $subject): array
    {
        return Preg::split($this->compiled, $subject);
    }

    /**
     * @return list<string>
     */
    public function allMatches(string $subject): array
    {
        // @phpstan-ignore return.type (0 always has non-nulls)
        return Regex::matchAll($this->compiled, $subject)->matches[0];
    }

    public function isMatch(string $subject): bool
    {
        return Preg::isMatch($this->compiled, $subject);
    }

    public function strictMatch(string $subject): MatchStrictGroupsResult
    {
        // @phpstan-ignore composerPcre.maybeUnsafeStrictGroups (TODO: I don't know why)
        return Regex::matchStrictGroups($this->compiled, $subject);
    }

    public function match(string $subject): MatchResult
    {
        return Regex::match($this->compiled, $subject);
    }

    public function strictMatchAll(string $subject): MatchAllStrictGroupsResult
    {
        // @phpstan-ignore composerPcre.maybeUnsafeStrictGroups (TODO: I don't know why)
        return Regex::matchAllStrictGroups($this->compiled, $subject);
    }

    private static function escapeDelimiters(string $subject): string
    {
        return str_replace(self::DELIMITER, self::ESCAPED_DELIMITER, $subject);
    }
}
