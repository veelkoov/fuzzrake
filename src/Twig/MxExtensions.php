<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Artisan as ArtisanE;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MxExtensions extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('smart', $this->smartFilter(...)),
        ];
    }

    private function smartFilter(Artisan|ArtisanE $artisan): Artisan
    {
        if (!($artisan instanceof Artisan)) {
            $artisan = Artisan::wrap($artisan);
        }

        return $artisan;
    }
}
