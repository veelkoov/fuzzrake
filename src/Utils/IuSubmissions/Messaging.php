<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\Entity\Artisan;
use App\Utils\Data\Manager;
use App\Utils\Data\Printer;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\StrUtils;

class Messaging
{
    public function __construct(
        private Printer $printer,
        private Manager $manager,
    ) {
    }

    public function reportIgnoredItem(ImportItem $item): void
    {
        $this->printer->writeln("{$item->getIdStrSafe()} ignored until {$this->manager->getIgnoredUntilDate($item)->format('Y-m-d')}");
    }

    public function reportMoreThanOneMatchedArtisans(Artisan $artisan, array $results): void
    {
        $namesList = implode(', ', array_map(fn (Artisan $artisan) => StrUtils::artisanNamesSafeForCli($artisan), $results));

        $this->printer->warning('Was looking for: '.StrUtils::artisanNamesSafeForCli($artisan).'. Found more than one: '.$namesList);
    }

    public function reportNewMaker(ImportItem $item): void
    {
        $this->printer->warning("New maker: {$item->getNamesStrSafe()}");
        $this->printer->writeln([
            Manager::CMD_WITH.' '.$item->getId().': // '.$item->getMakerId(),
            '    '.Manager::CMD_ACCEPT,
            '    '.Manager::CMD_REJECT,
            '    '.Manager::CMD_IGNORE_UNTIL.' '.DateTimeUtils::getMonthLaterYmd(),
            '    '.Manager::CMD_MATCH_TO_NAME.' |ABCDEFGHIJ|',
        ]);
    }

    public function reportChangedMakerId(ImportItem $item): void
    {
        $this->printer->warning($item->getNamesStrSafe().' changed their maker ID from '.$item->getOriginalEntity()->getMakerId()
            .' to '.$item->getFixedEntity()->getMakerId());
    }

    public function reportInvalidPassword(ImportItem $item): void
    {
        $tomorrow = DateTimeUtils::getTomorrowYmd();

        $this->printer->warning("{$item->getNamesStrSafe()} provided invalid password");
        $this->printer->writeln([
            Manager::CMD_WITH.' '.$item->getId().': // '.$item->getMakerId(),
            '    '.Manager::CMD_REJECT,
            '    '.Manager::CMD_IGNORE_UNTIL." $tomorrow",
            '',
        ]);
        $this->emitDiffAndContactDetails($item);
    }

    public function reportUpdates(ImportItem $item): void
    {
        if (!empty($item->getReplaced())) {
            $this->printer->writeln([
                $item->getIdStrSafe().' replaced',
                implode(" replaced\n", $item->getReplaced()),
                '',
            ]);
        }
    }

    private function emitDiffAndContactDetails(ImportItem $item): void
    {
        $this->printer->writeln($item->getDiff()->getDescriptionCliSafe());
        $this->printer->writeln('Contact info: '
            .($item->getOriginalEntity()->getContactAllowed() ?: '-')
            .'/'.$item->getFixedEntity()->getContactAllowed()
            .' '.($item->getOriginalEntity()->getContactInfoOriginal() ?: '?'));
    }

    public function reportValid(ImportItem $item): void
    {
        if ($item->getDiff()->hasAnythingChanged()) {
            $this->printer->success('Accepted for import');
        }
    }
}
