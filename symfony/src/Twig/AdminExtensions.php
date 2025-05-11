<?php

declare(strict_types=1);

namespace App\Twig;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\SecureValues;
use App\Data\Validator\Validator;
use App\Entity\Creator as CreatorE;
use App\Twig\Utils\SafeFor;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DataQuery;
use App\Utils\StrUtils;
use Override;
use TRegx\CleanRegex\Match\Detail;
use TRegx\CleanRegex\Pattern;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function Psl\Iter\contains;

class AdminExtensions extends AbstractExtension
{
    private readonly Pattern $linkPattern;

    public function __construct(
        private readonly Validator $validator,
    ) {
        $this->linkPattern = pattern('(?<!title=")https?://[^ ,\n<>"]+', 'i');
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('smart', $this->smartFilter(...)),
            new TwigFilter('as_str', $this->asStr(...)),
            new TwigFilter('as_field', $this->asField(...)),
            new TwigFilter('difference', $this->difference(...), SafeFor::HTML),
            new TwigFilter('link_urls', $this->linkUrls(...), SafeFor::HTML),
            new TwigFilter('is_valid', $this->isValid(...)),
            new TwigFilter('bluesky_at', $this->blueskyAt(...)),
            new TwigFilter('mastodon_at', $this->mastodonAt(...)),
            new TwigFilter('tumblr_at', $this->tumblrAt(...)),
            new TwigFilter('filter_by_query', $this->filterFilterByQuery(...)),
        ];
    }

    private function smartFilter(Creator|CreatorE $creator): Creator
    {
        if (!($creator instanceof Creator)) {
            $creator = Creator::wrap($creator);
        }

        return $creator;
    }

    /**
     * @param psPhpFieldValue $value
     */
    private function asStr(mixed $value): string
    {
        return StrUtils::asStr($value);
    }

    private function asField(string $name): Field
    {
        return Field::from($name);
    }

    private function difference(Field $field, string $classSuffix, Creator $subject, Creator $other): string
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
            $itemClass = contains($otherItems, $item) ? 'badge-outline-secondary' : $bsClass;
            $text = htmlspecialchars($item);

            $result .= " <span class=\"submission-list-item badge $itemClass\" title=\"$text\">$text</span> ";
        }

        return $result;
    }

    public function linkUrls(string $input): string
    {
        return $this->linkPattern->replace($input)->callback(function (Detail $detail): string {
            $url = $detail->text();

            return "<a href=\"$url\" target=\"_blank\">$url</a>";
        });
    }

    private function isValid(Creator $creator, Field $field): bool
    {
        return $this->validator->isValid($creator, $field);
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

    private function blueskyAt(string $blueskyUrl): string
    {
        return Pattern::of('^https://[^/]+/profile/([^/#?]+).*')
            ->replace($blueskyUrl)
            ->withReferences('@$1');
    }

    private function mastodonAt(string $mastodonUrl): string
    {
        return Pattern::of('^https://([^/]+)/([^/#?]+).*')
            ->replace($mastodonUrl)
            ->withReferences('$2@$1');
    }

    private function tumblrAt(string $mastodonUrl): string
    {
        return Pattern::of('^https://www\.tumblr\.com/([^/#?]+).*')
            ->replace($mastodonUrl)
            ->withReferences('@$1 _FIX_');
    }

    /**
     * @param list<string> $input
     */
    public function filterFilterByQuery(array $input, DataQuery $query): string
    {
        return implode(', ', $query->filterList($input));
    }
}
