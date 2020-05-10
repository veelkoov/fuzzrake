<?php

declare(strict_types=1);

namespace App\Utils\DataInput;

use App\Entity\Artisan;
use App\Utils\Data\Printer;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\StrUtils;

class Messaging
{
    private Printer $printer;
    private Manager $manager;

    public function __construct(Printer $printer, Manager $manager)
    {
        $this->printer = $printer;
        $this->manager = $manager;
    }

    public function reportIgnoredItem(ImportItem $item): void
    {
        $this->printer->writeln("{$item->getIdStrSafe()} ignored until {$this->manager->getIgnoredUntilDate($item)->format('Y-m-d')}");
    }

    public function reportMoreThanOneMatchedArtisans(Artisan $artisan, array $results): void
    {
        $namesList = implode(', ', array_map(function (Artisan $artisan) {
            return StrUtils::artisanNamesSafeForCli($artisan);
        }, $results));

        $this->printer->warning('Was looking for: '.StrUtils::artisanNamesSafeForCli($artisan).'. Found more than one: '.$namesList);
    }

    public function reportNewMaker(ImportItem $item): void
    {
        $monthLater = DateTimeUtils::getMonthLaterYmd();
        $makerId = $item->getMakerId();

        $this->printer->warning("New maker: {$item->getNamesStrSafe()}");
        $this->printer->writeln([
            Manager::CMD_MATCH_NAME.":$makerId:ABCDEFGHIJ:",
            Manager::CMD_ACK_NEW.":$makerId:",
            Manager::CMD_REJECT.":$makerId:{$item->getHash()}:",
            Manager::CMD_IGNORE_UNTIL.":$makerId:{$item->getHash()}:$monthLater:",
        ]);
    }

    public function reportChangedMakerId(ImportItem $item): void
    {
        $this->printer->warning($item->getNamesStrSafe().' changed their maker ID from '.$item->getOriginalEntity()->getMakerId()
            .' to '.$item->getFixedEntity()->getMakerId());
    }

    public function reportNewPasscode(ImportItem $item): void
    {
        $hash = $item->getHash();
        $makerId = $item->getMakerId();

        $this->printer->warning("{$item->getNamesStrSafe()} set new passcode: {$item->getProvidedPasscode()}");
        $this->printer->writeln(Manager::CMD_SET_PIN.":$makerId:$hash:");
        $this->printer->writeln(Manager::CMD_REJECT.":$makerId:$hash:");
    }

    public function reportInvalidPasscode(ImportItem $item, string $expectedPasscode): void
    {
        $weekLater = DateTimeUtils::getWeekLaterYmd();
        $makerId = $item->getMakerId();
        $hash = $item->getHash();

        $this->printer->warning("{$item->getNamesStrSafe()} provided invalid passcode '{$item->getProvidedPasscode()}' (expected: '$expectedPasscode')");
        $this->printer->writeln([
            Manager::CMD_IGNORE_PIN.":$makerId:$hash:",
            Manager::CMD_REJECT.":$makerId:$hash:",
            Manager::CMD_SET_PIN.":$makerId:$hash:",
            Manager::CMD_IGNORE_UNTIL.":$makerId:$hash:$weekLater:",
            '',
        ]);
        $this->printer->writeln($item->getDiff()->getDescription());
        $this->printer->writeln('Contact info: '.$item->getOriginalEntity()->getContactInfoOriginal());
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
}
