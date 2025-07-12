<?php

declare(strict_types=1);

namespace App\Twig;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\SecureValues;
use App\Entity\Creator as CreatorE;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DataQuery;
use App\Utils\StrUtils;
use Composer\Pcre\Preg;
use Twig\Attribute\AsTwigFilter;

class AdminExtensions
{
    #[AsTwigFilter('smart')]
    public function smartFilter(Creator|CreatorE $creator): Creator
    {
        if (!($creator instanceof Creator)) {
            $creator = Creator::wrap($creator);
        }

        return $creator;
    }

    /**
     * @param psPhpFieldValue $value
     */
    #[AsTwigFilter('as_str')]
    public function asStr(mixed $value): string
    {
        return StrUtils::asStr($value);
    }

    #[AsTwigFilter('as_field')]
    public function asField(string $name): Field
    {
        return Field::from($name);
    }

    #[AsTwigFilter('difference', isSafe: ['html'])]
    public function difference(Field $field, string $classSuffix, Creator $subject, Creator $other): string
    {
        if (!$field->isList()) {
            $value = $this->getOptionallyRedactedValue($field, $subject);
            $class = "text-$classSuffix";
            $text = htmlspecialchars(StrUtils::asStr($value));

            return "<span class=\"$class\">$text</span>";
        }

        $bsClass = "badge-outline-$classSuffix";

        $result = '';

        $subjectItems = $subject->getStringList($field);
        $otherItems = $other->getStringList($field);

        foreach ($subjectItems as $item) {
            $itemClass = arr_contains($otherItems, $item) ? 'badge-outline-secondary' : $bsClass;
            $text = htmlspecialchars($item);

            $result .= " <span class=\"submission-list-item badge $itemClass\" title=\"$text\">$text</span> ";
        }

        return $result;
    }

    /**
     * @return psPhpFieldValue
     */
    private function getOptionallyRedactedValue(Field $field, Creator $subject): mixed
    {
        if (SecureValues::hideOnAdminScreen($field)) {
            return '[redacted]';
        } else {
            return $subject->get($field);
        }
    }

    #[AsTwigFilter('link_urls', isSafe: ['html'])]
    public function linkUrls(string $input): string
    {
        return Preg::replace(
            '#(?<!title=")https?://[^ ,\n<>"]+#i',
            '<a href="$0" target="_blank">$0</a>',
            $input,
        );
    }

    #[AsTwigFilter('bluesky_at')]
    public function blueskyAt(string $blueskyUrl): string
    {
        return Preg::replace('#^https://[^/]+/profile/([^/\#?]+).*$#', '@$1', $blueskyUrl);
    }

    #[AsTwigFilter('mastodon_at')]
    public function mastodonAt(string $mastodonUrl): string
    {
        return Preg::replace('#^https://([^/]+)/([^/\#?]+).*$#', '$2@$1', $mastodonUrl);
    }

    #[AsTwigFilter('tumblr_at')]
    public function tumblrAt(string $tumblrUrl): string
    {
        return Preg::replace('#^https://www\.tumblr\.com/([^/\#?]+).*$#', '@$1 _FIX_', $tumblrUrl);
    }

    /**
     * @param list<string> $input
     */
    #[AsTwigFilter('filter_by_query')]
    public function filterFilterByQuery(array $input, DataQuery $query): string
    {
        return implode(', ', $query->filterList($input));
    }
}
