<?php

declare(strict_types=1);

namespace App\Twig;

use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\SecureValues;
use App\Data\Validator\Validator;
use App\Entity\Artisan as ArtisanE;
use App\IuHandling\Import\SubmissionData;
use App\Repository\SubmissionRepository;
use App\Twig\Utils\SafeFor;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StrUtils;
use Doctrine\ORM\NonUniqueResultException;
use Override;
use TRegx\CleanRegex\Match\Detail;
use TRegx\CleanRegex\Pattern;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function Psl\Iter\contains;

class AdminExtensions extends AbstractExtension
{
    private readonly Pattern $linkPattern;

    /** @noinspection ConstructorTwigExtensionHeavyConstructor TODO: https://github.com/veelkoov/fuzzrake/issues/156 */
    public function __construct(
        private readonly Validator $validator,
        private readonly SubmissionRepository $submissionRepository,
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
            new TwigFilter('get_comments', $this->getComment(...)),
            new TwigFilter('mastodon_at', $this->mastodonAt(...)),
            new TwigFilter('tumblr_at', $this->tumblrAt(...)),
        ];
    }

    private function smartFilter(Artisan|ArtisanE $artisan): Artisan
    {
        if (!($artisan instanceof Artisan)) {
            $artisan = Artisan::wrap($artisan);
        }

        return $artisan;
    }

    /**
     * @param psFieldValue $value
     */
    private function asStr(mixed $value): string
    {
        return StrUtils::asStr($value);
    }

    private function asField(string $name): Field
    {
        return Field::from($name);
    }

    private function difference(Field $field, string $classSuffix, Artisan $subject, Artisan $other): string
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

    private function isValid(Artisan $artisan, Field $field): bool
    {
        return $this->validator->isValid($artisan, $field);
    }

    /**
     * @throws NonUniqueResultException
     */
    private function getComment(SubmissionData $submissionData): string
    {
        return $this->submissionRepository->findByStrId($submissionData->getId())?->getComment() ?? '';
    }

    /**
     * @return psFieldValue
     */
    private function getOptionallyRedactedValue(Field $field, Artisan $subject): mixed
    {
        if (SecureValues::hideOnAdminScreen($field)) {
            return '[redacted]';
        } else {
            return $subject->get($field);
        }
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
}
