<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Artisan\Fields;

class UrlFixer extends StringFixer
{
    private $replacements;
    private $commonRegexPrefix;
    private $commonRegexSuffix;

    public function __construct(array $urls, array $strings)
    {
        parent::__construct($strings);

        $this->replacements = $urls['replacements'];
        $this->commonRegexPrefix = $urls['commonRegexPrefix'];
        $this->commonRegexSuffix = $urls['commonRegexSuffix'];
    }

    public function fix(string $fieldName, string $subject): string
    {
        $subject = parent::fix($fieldName, $subject);
        $subject = $this->fixUrlWith($subject, $this->replacements['generic']);

        switch ($fieldName) {
            case Fields::URL_FUR_AFFINITY:
                return $this->fixUrlWith($subject, $this->replacements['fur_affinity']);

            case Fields::URL_TWITTER:
                return $this->fixUrlWith($subject, $this->replacements['twitter']);

            case Fields::URL_INSTAGRAM:
                return $this->fixUrlWith($subject, $this->replacements['instagram']);

            case Fields::URL_FACEBOOK:
                return $this->fixUrlWith($subject, $this->replacements['facebook']);

            case Fields::URL_YOUTUBE:
                return $this->fixUrlWith($subject, $this->replacements['youtube']);

            case Fields::URL_DEVIANTART:
                return $this->fixUrlWith($subject, $this->replacements['deviantart']);

            default:
                return $subject;
        }
    }

    private function fixUrlWith(string $subject, array $replacements)
    {
        return $this->fixWith($replacements, $subject, $this->commonRegexPrefix,
            $this->commonRegexSuffix);
    }
}
