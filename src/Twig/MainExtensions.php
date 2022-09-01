<?php

declare(strict_types=1);

namespace App\Twig;

use App\DataDefinitions\Ages;
use App\DataDefinitions\NewArtisan;
use App\Twig\Utils\SafeFor;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use DateTimeImmutable;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MainExtensions extends AbstractExtension
{
    private readonly DateTimeImmutable $newCutoff;

    public function __construct()
    {
        $this->newCutoff = NewArtisan::getCutoffDate();
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('ages_for_table', $this->agesForTableFilter(...), SafeFor::HTML),
            new TwigFilter('is_new', $this->isNewFilter(...)),
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

    private function isNewFilter(Artisan $artisan): bool
    {
        return null !== $artisan->getDateAdded() && $artisan->getDateAdded() > $this->newCutoff;
    }
}
