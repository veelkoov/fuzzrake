<?php

declare(strict_types=1);

namespace App\Twig;

use App\DataDefinitions\Ages;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use DateTimeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MainExtensions extends AbstractExtension
{
    private readonly DateTimeInterface $newCutoff;

    /**
     * @throws DateTimeException
     */
    public function __construct()
    {
        $this->newCutoff = DateTimeUtils::getUtcAt('-42 days'); // grep-amount-of-days-considered-new
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
