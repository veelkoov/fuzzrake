<?php

declare(strict_types=1);

namespace App\Twig;

use TRegx\CleanRegex\Pattern;
use TRegx\CleanRegex\PatternList;
use Twig\Attribute\AsTwigFilter;

class ReadabilityExtensions
{
    private const array READABILITY_REGEXES = [
        '\(\?<!.+?\)',
        '\(\?!.+?\)',
        '[()?]',
    ];

    private readonly PatternList $regexPatterns;
    private readonly Pattern $shortUrlPattern;

    public function __construct()
    {
        $this->regexPatterns = Pattern::list(arr_map(self::READABILITY_REGEXES,
            static fn ($item) => Pattern::of($item, 'i')));
        $this->shortUrlPattern = Pattern::of('^https?://(www\.)?|/?$', 'n');
    }

    #[AsTwigFilter('event_url')]
    public function eventUrl(string $url): string
    {
        $url = $this->shortUrlPattern->prune($url);
        $url = str_replace('/user/', '/u/', $url);
        $url = str_replace('/journal/', '/j/', $url);

        if (mb_strlen($url) > 50) {
            $url = mb_substr($url, 0, 40).'...';
        }

        return $url;
    }

    #[AsTwigFilter('human_friendly_regexp')]
    public function humanFriendlyRegexp(string $input): string
    {
        return mb_strtoupper($this->regexPatterns->prune($input));
    }
}
