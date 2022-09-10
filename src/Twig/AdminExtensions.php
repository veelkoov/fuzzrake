<?php

declare(strict_types=1);

namespace App\Twig;

use App\DataDefinitions\Fields\Field;
use App\Entity\Artisan as ArtisanE;
use App\IuHandling\Import\SubmissionData;
use App\Repository\SubmissionRepository;
use App\Twig\Utils\SafeFor;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\Validator;
use App\Utils\StringList;
use App\Utils\StrUtils;
use Doctrine\ORM\NonUniqueResultException;
use TRegx\CleanRegex\Pattern;
use TRegx\CleanRegex\Replace\Details\ReplaceDetail;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function Psl\Iter\contains;

class AdminExtensions extends AbstractExtension
{
    private readonly Pattern $linkPattern;

    /** @noinspection ConstructorTwigExtensionHeavyConstructor TODO: Remove necessity for this */
    public function __construct(
        private readonly Validator $validator,
        private readonly SubmissionRepository $submissionRepository,
    ) {
        $this->linkPattern = pattern('https?://[^ ,;\n<>"]+', 'i');
    }

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
            $class = "text-$classSuffix";

            return '<span class="'.$class.'">'.htmlspecialchars(StrUtils::asStr($subject->get($field))).'</span>';
        }

        $bsClass = "badge-outline-$classSuffix";

        $result = '';

        $subjectItems = StringList::unpack($subject->getString($field));
        $otherItems = StringList::unpack($other->getString($field));

        foreach ($subjectItems as $item) {
            $itemClass = contains($otherItems, $item) ? 'badge-outline-secondary' : $bsClass;
            $text = htmlspecialchars($item);

            $result .= " <span class=\"badge $itemClass\">$text</span> ";
        }

        return $result;
    }

    private function linkUrls(string $input): string
    {
        return $this->linkPattern->replace($input)->all()->callback(function (ReplaceDetail $detail): string {
            $url = htmlspecialchars($detail->text());

            return "<a href=\"$url\">$url</a>";
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
}
