<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Artisan\Fields;
use App\Utils\Regexp\Utils as Regexp;
use App\Utils\StrUtils;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class Validator
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;

        $this->io->getFormatter()->setStyle('wrong', new OutputFormatterStyle('red'));
    }

    public function validate(Artisan $artisan): void
    {
        foreach (Fields::persisted() as $field) {
            if ($field->validationRegexp() && !Regexp::match($field->validationRegexp(), $artisan->get($field))) {
                $safeValue = StrUtils::strSafeForCli($artisan->get($field));

                $this->io->writeln("wr:{$artisan->getMakerId()}:{$field->name()}:|:<wrong>$safeValue</>|$safeValue|");
            }
        }
    }
}
