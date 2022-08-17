<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Artisan as ArtisanE;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StrUtils;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MxExtensions extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('smart', fn (Artisan|ArtisanE $artisan) => $this->smartFilter($artisan)),
            new TwigFilter('as_str', fn (mixed $value) => $this->asStr($value)),
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
}
