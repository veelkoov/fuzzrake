<?php

declare(strict_types=1);

namespace App\Twig\Utils;

use TRegx\CleanRegex\Pattern;
use TRegx\CleanRegex\PatternList;

use function Psl\Vec\map;

class HumanFriendly
{
    private const REGEX_PATTERNS = [
        '\(\?<!.+?\)',
        '\(\?!.+?\)',
        '[()?]',
    ];

    private readonly PatternList $regexPatterns;
    private readonly Pattern $shortUrlPattern;

    public function __construct()
    {
        $this->regexPatterns = Pattern::list(map(self::REGEX_PATTERNS, fn ($item) => pattern($item, 'i')));
        $this->shortUrlPattern = Pattern::of('^https?://(www\.)?|/?$', 'n');
    }

    public function shortUrl(string $url): string
    {
        $url = $this->shortUrlPattern->prune($url);
        $url = str_replace('/user/', '/u/', $url);
        $url = str_replace('/journal/', '/j/', $url);

        if (strlen($url) > 50) {
            $url = substr($url, 0, 40).'...';
        }

        return $url;
    }

    public function regex(string $input): string
    {
        return strtoupper($this->regexPatterns->prune($input));
    }
}
