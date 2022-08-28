<?php

declare(strict_types=1);

namespace App\Twig;

use App\DataDefinitions\Fields\Field;
use App\Entity\Artisan as ArtisanE;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;
use App\Utils\StrUtils;
use TRegx\CleanRegex\Pattern;
use TRegx\CleanRegex\Replace\Details\ReplaceDetail;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function Psl\Iter\contains;

class MxExtensions extends AbstractExtension
{
    private const HTML = ['is_safe' => ['html']];
    private readonly Pattern $linkPattern;

    public function __construct()
    {
        $this->linkPattern = pattern('https?://[^ ,;\n<>"]+', 'i');
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('smart', fn (Artisan|ArtisanE $artisan) => $this->smartFilter($artisan)),
            new TwigFilter('as_str', fn (mixed $value) => $this->asStr($value)),
            new TwigFilter('as_field', fn (string $name) => $this->asField($name)),
            new TwigFilter('difference',
                function (Field $field, bool $isNew, Artisan $current, Artisan $other): string {
                    return $this->difference($field, $isNew, $current, $other);
                }, self::HTML,
            ),
            new TwigFilter('link_urls', fn (string $input) => $this->linkUrls($input), self::HTML),
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

    private function difference(Field $field, bool $isNew, Artisan $subject, Artisan $other): string
    {
        if (!$field->isList()) {
            $class = $isNew ? 'text-success' : 'text-danger';

            return '<span class="'.$class.'">'.htmlspecialchars(StrUtils::asStr($subject->get($field))).'</span>';
        }

        $bsClass = $isNew ? 'bg-success' : 'bg-danger';

        $result = '';

        $subjectItems = StringList::unpack($subject->getString($field));
        $otherItems = StringList::unpack($other->getString($field));

        foreach ($subjectItems as $item) {
            $itemClass = contains($otherItems, $item) ? 'bg-secondary' : $bsClass;
            $text = htmlspecialchars($item);

            $result .= " <span class=\"badge rounded-pill $itemClass\">$text</span> ";
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
}
