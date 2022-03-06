<?php

declare(strict_types=1);

namespace App\Twig;

use App\DataDefinitions\Ages;
use App\DataDefinitions\NewArtisan;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use DateTimeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MainExtensions extends AbstractExtension
{
    private readonly DateTimeInterface $newCutoff;

    public function __construct()
    {
        $this->newCutoff = NewArtisan::getCutoffDate();
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('ages_for_table', fn (Artisan $artisan) => $this->agesForTableFilter($artisan), ['is_safe' => ['html']]),
            new TwigFilter('is_new', fn (Artisan $artisan) => $this->isNewFilter($artisan)),
        ];
    }

    private function agesForTableFilter(Artisan $artisan): string
    {
        $result = match ($artisan->getAges()) {
            Ages::MINORS => '<i class="ages fa-solid fa-user-minus"></i>',
            Ages::MIXED  => '<i class="ages fa-solid fa-user-plus"></i> <i class="ages fa-solid fa-user-minus"></i>',
            Ages::ADULTS => '',
            default      => match ($artisan->getIsMinor()) {
                true     => '<i class="ages fa-solid fa-user-minus"></i>',
                false    => '',
                default  => '<i class="ages fa-solid fa-user"></i>',
            },
        };

        return '' === $result ? '' : "&nbsp;$result";
    }

    private function isNewFilter(Artisan $artisan): bool
    {
        return null !== $artisan->getDateAdded() && $artisan->getDateAdded() > $this->newCutoff;
    }
}
