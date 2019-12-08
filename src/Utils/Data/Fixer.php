<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Artisan\Fields;
use App\Utils\Data\Fixer\ContactAllowedFixer;
use App\Utils\Data\Fixer\CountryFixer;
use App\Utils\Data\Fixer\DefinedListFixer;
use App\Utils\Data\Fixer\FixerInterface;
use App\Utils\Data\Fixer\FreeListFixer;
use App\Utils\Data\Fixer\IntroFixer;
use App\Utils\Data\Fixer\LanguagesFixer;
use App\Utils\Data\Fixer\NoopFixer;
use App\Utils\Data\Fixer\SinceFixer;
use App\Utils\Data\Fixer\SpeciesListFixer;
use App\Utils\Data\Fixer\StringFixer;
use App\Utils\Data\Fixer\UrlFixer;
use App\Utils\Regexp\Utils as Regexp;
use App\Utils\StrUtils;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class Fixer
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var Differ
     */
    private $differ;

    /**
     * @var bool
     */
    private $showDiff;

    /**
     * @var StringFixer
     */
    private $stringFixer;

    /**
     * @var DefinedListFixer
     */
    private $definedListFixer;

    /**
     * @var FreeListFixer
     */
    private $freeListFixer;

    /**
     * @var SpeciesListFixer
     */
    private $speciesListFixer;

    /**
     * @var UrlFixer
     */
    private $urlFixer;

    /**
     * @var ContactAllowedFixer
     */
    private $contactAllowedFixer;

    /**
     * @var CountryFixer
     */
    private $countryFixer;

    /**
     * @var LanguagesFixer
     */
    private $languagesFixer;

    /**
     * @var SinceFixer
     */
    private $sinceFixer;

    /**
     * @var NoopFixer
     */
    private $noopFixer;

    /**
     * @var IntroFixer
     */
    private $introFixer;

    public function __construct(SymfonyStyle $io, bool $showDiff)
    {
        $this->io = $io;
        $this->io->getFormatter()->setStyle('wrong', new OutputFormatterStyle('red'));

        $this->differ = new Differ($io);
        $this->stringFixer = new StringFixer();
        $this->definedListFixer = new DefinedListFixer();
        $this->freeListFixer = new FreeListFixer();
        $this->speciesListFixer = new SpeciesListFixer();
        $this->urlFixer = new UrlFixer();
        $this->noopFixer = new NoopFixer();
        $this->sinceFixer = new SinceFixer();
        $this->languagesFixer = new LanguagesFixer();
        $this->countryFixer = new CountryFixer();
        $this->introFixer = new IntroFixer();
        $this->contactAllowedFixer = new ContactAllowedFixer();

        $this->showDiff = $showDiff;
    }

    public function fixArtisanData(Artisan $artisan): Artisan
    {
        $originalArtisan = clone $artisan;

        foreach (Fields::persisted() as $field) {
            $fixer = $this->getFixer($field);

            $artisan->set($field, $fixer->fix($field->name(), $artisan->get($field)));
        }

        if ($this->showDiff) {
            $this->differ->showDiff($originalArtisan, $artisan);
        }

        return $artisan;
    }

    public function validateArtisanData(Artisan $artisan): void
    {
        foreach (Fields::persisted() as $field) {
            if ($field->validationRegexp() && !Regexp::match($field->validationRegexp(), $artisan->get($field))) {
                $safeValue = StrUtils::strSafeForCli($artisan->get($field));
                $this->io->writeln("wr:{$artisan->getMakerId()}:{$field->name()}:|:<wrong>$safeValue</>|$safeValue|");
            }
        }
    }

    private function getFixer(\App\Utils\Artisan\Field $field): FixerInterface
    {
        switch ($field->name()) {
            case Fields::NAME:
            case Fields::STATE:
            case Fields::CITY:
            case Fields::URL_OTHER:
            case Fields::PAYMENT_PLANS:
            case Fields::NOTES:
                return $this->stringFixer;

            case Fields::SPECIES_DOES:
            case Fields::SPECIES_DOESNT:
                return $this->speciesListFixer;

            case Fields::PRODUCTION_MODELS:
            case Fields::FEATURES:
            case Fields::STYLES:
            case Fields::ORDER_TYPES:
                return $this->definedListFixer;

            case Fields::FORMER_MAKER_IDS:
            case Fields::OTHER_FEATURES:
            case Fields::OTHER_ORDER_TYPES:
            case Fields::OTHER_STYLES:
            case Fields::URL_SCRITCH_PHOTO:
            case Fields::URL_SCRITCH_MINIATURE:
                return $this->freeListFixer;

            case Fields::URL_CST:
            case Fields::URL_DEVIANTART:
            case Fields::URL_FACEBOOK:
            case Fields::URL_FAQ:
            case Fields::URL_FUR_AFFINITY:
            case Fields::URL_FURSUITREVIEW:
            case Fields::URL_INSTAGRAM:
            case Fields::URL_PRICES:
            case Fields::URL_TUMBLR:
            case Fields::URL_TWITTER:
            case Fields::URL_YOUTUBE:
            case Fields::URL_WEBSITE:
            case Fields::URL_QUEUE:
                return $this->urlFixer;

            case Fields::SINCE:
                return $this->sinceFixer;

            case Fields::COUNTRY:
                return $this->countryFixer;

            case Fields::INTRO:
                return $this->introFixer;

            case Fields::LANGUAGES:
                return $this->languagesFixer;

            case Fields::CONTACT_ALLOWED:
                return $this->contactAllowedFixer;

            default:
                return $this->noopFixer;
        }
    }
}
