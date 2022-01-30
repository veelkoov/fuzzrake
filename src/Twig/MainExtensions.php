<?php

declare(strict_types=1);

namespace App\Twig;

use App\DataDefinitions\Ages;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MainExtensions extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('ages_for_table', fn (Artisan $artisan) => $this->agesForTableFilter($artisan), ['is_safe' => ['html']]),
        ];
    }

    private function agesForTableFilter(Artisan $artisan): string
    {
        return match ($artisan->getAges()) {
            Ages::MINORS => '<i class="ages fa-solid fa-user-minus"></i>',
            Ages::MIXED  => '<i class="ages fa-solid fa-user-plus"></i> <i class="ages fa-solid fa-user-minus"></i>',
            Ages::ADULTS => '',
            default      => match ($artisan->getIsMinor()) {
                true     => '<i class="ages fa-solid fa-user-minus"></i>',
                false    => '',
                default  => '<i class="ages fa-solid fa-user"></i>',
            },
        };
    }
}
