<?php

declare(strict_types=1);

namespace App\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Artisan\ContactPermit;
use App\Utils\Artisan\Fields;
use App\Utils\Data\Fixer\DefinedListFixer;
use App\Utils\Data\Fixer\FreeListFixer;
use App\Utils\Data\Fixer\StringFixer;
use App\Utils\Regexp\Utils as Regexp;
use App\Utils\StrUtils;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class Fixer
{
    const LANGUAGE_REGEXP = '#(?<prefix>a small bit of |bit of |a little |some |moderate |basic |elementary |slight |limited )?(?<language>.+)(?<suffix> \(limited\))?#i';

    const COUNTRIES_REPLACEMENTS = [
        'argentina'                                     => 'AR',
        'australia'                                     => 'AU',
        'belgium'                                       => 'BE',
        'canada'                                        => 'CA',
        'costa rica'                                    => 'CR',
        'czech republic'                                => 'CZ',
        'd[ea]nmark'                                    => 'DK',
        'germany'                                       => 'DE',
        'finland'                                       => 'FI',
        'france'                                        => 'FR',
        'uk|england|united kingdom'                     => 'GB',
        'ireland'                                       => 'IE',
        'italia|italy'                                  => 'IT',
        'mexico'                                        => 'MX',
        '(the )?netherlands'                            => 'NL',
        'new zealand'                                   => 'NZ',
        'russia'                                        => 'RU',
        'poland'                                        => 'PL',
        'sweden'                                        => 'SE',
        'ukraine'                                       => 'UA',
        'united states( of america)?|us of america|usa' => 'US',
    ];

    const KEEP_WHOLE = [
        'All species, but I specialize in dragons',
    ];

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

    public function __construct(SymfonyStyle $io, bool $showDiff)
    {
        $this->io = $io;
        $this->io->getFormatter()->setStyle('wrong', new OutputFormatterStyle('red'));

        $this->differ = new Differ($io);
        $this->stringFixer = new StringFixer();
        $this->definedListFixer = new DefinedListFixer();
        $this->freeListFixer = new FreeListFixer();

        $this->showDiff = $showDiff;
    }

    public function fixArtisanData(Artisan $artisan): Artisan
    {
        $originalArtisan = clone $artisan;

        $artisan->setName($this->stringFixer->fix($artisan->getName()));
        $artisan->setFormerMakerIds($this->freeListFixer->fix($artisan->getFormerMakerIds()));
        $artisan->setSince($this->fixSince($artisan->getSince()));
        $artisan->setSpeciesDoes($this->fixSpecies($artisan->getSpeciesDoes()));
        $artisan->setSpeciesDoesnt($this->fixSpecies($artisan->getSpeciesDoesnt()));

        $artisan->setProductionModels($this->definedListFixer->fix($artisan->getProductionModels()));
        $artisan->setFeatures($this->definedListFixer->fix($artisan->getFeatures()));
        $artisan->setStyles($this->definedListFixer->fix($artisan->getStyles()));
        $artisan->setOrderTypes($this->definedListFixer->fix($artisan->getOrderTypes()));
        $artisan->setOtherFeatures($this->freeListFixer->fix($artisan->getOtherFeatures()));
        $artisan->setOtherStyles($this->freeListFixer->fix($artisan->getOtherStyles()));
        $artisan->setOtherOrderTypes($this->freeListFixer->fix($artisan->getOtherOrderTypes()));

        $artisan->setCountry($this->fixCountry($artisan->getCountry()));
        $artisan->setState($this->stringFixer->fix($artisan->getState()));
        $artisan->setCity($this->stringFixer->fix($artisan->getCity()));

        $artisan->setCstUrl($this->fixGenericUrl($artisan->getCstUrl()));
        $artisan->setDeviantArtUrl($this->fixDeviantArtUrl($artisan->getDeviantArtUrl()));
        $artisan->setFacebookUrl($this->fixFacebookUrl($artisan->getFacebookUrl()));
        $artisan->setFaqUrl($this->fixGenericUrl($artisan->getFaqUrl()));
        $artisan->setFurAffinityUrl($this->fixFurAffinityUrl($artisan->getFurAffinityUrl()));
        $artisan->setFursuitReviewUrl($this->fixGenericUrl($artisan->getFursuitReviewUrl()));
        $artisan->setInstagramUrl($this->fixInstagramUrl($artisan->getInstagramUrl()));
        $artisan->setPricesUrl($this->fixGenericUrl($artisan->getPricesUrl()));
        $artisan->setTumblrUrl($this->fixTumblrUrl($artisan->getTumblrUrl()));
        $artisan->setTwitterUrl($this->fixTwitterUrl($artisan->getTwitterUrl()));
        $artisan->setYoutubeUrl($this->fixYoutubeUrl($artisan->getYoutubeUrl()));
        $artisan->setWebsiteUrl($this->fixGenericUrl($artisan->getWebsiteUrl()));
        $artisan->setQueueUrl($this->fixGenericUrl($artisan->getQueueUrl()));
        $artisan->setScritchPhotoUrls($this->fixGenericUrlList($artisan->getScritchPhotoUrls()));
        $artisan->setScritchMiniatureUrls($this->fixGenericUrlList($artisan->getScritchMiniatureUrls()));

        $artisan->setOtherUrls($this->stringFixer->fix($artisan->getOtherUrls()));

        $artisan->setPaymentPlans($this->stringFixer->fix($artisan->getPaymentPlans()));
        $artisan->setIntro($this->fixIntro($artisan->getIntro()));
        $artisan->setNotes($this->fixNotes($artisan->getNotes()));
        $artisan->setLanguages($this->fixLanguages($artisan->getLanguages()));

        $artisan->setContactAllowed($this->fixContactAllowed($artisan->getContactAllowed()));

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

    private function fixCountry(string $input): string
    {
        $result = trim($input);

        foreach (self::COUNTRIES_REPLACEMENTS as $regexp => $replacement) {
            $result = Regexp::replace("#^$regexp$#i", $replacement, $result);
        }

        return $result;
    }

    private function fixFurAffinityUrl(string $input): string
    {
        return Regexp::replace('#^(?:https?://)?(?:www\.)?furaffinity(?:\.net|\.com)?/(?:user/|gallery/)?([^/]+)/?$#i',
            'http://www.furaffinity.net/user/$1', $this->fixGenericUrl($input));
    }

    private function fixTwitterUrl(string $input): string
    {
        return Regexp::replace('#^(?:(?:(?:https?://)?(?:www\.|mobile\.)?twitter(?:\.com)?/)|@)([^/?]+)/?(?:\?(?:lang=[a-z]{2,3}|s=\d+))?$#i',
            'https://twitter.com/$1', $this->fixGenericUrl($input));
    }

    private function fixInstagramUrl(string $input): string
    {
        return Regexp::replace('#^(?:(?:(?:https?://)?(?:www\.)?instagram(?:\.com)?/)|@)([^/?]+)/?(?:\?hl=[a-z]{2,3}(?:-[a-z]{2,3})?)?$#i',
            'https://www.instagram.com/$1/', $this->fixGenericUrl($input));
    }

    private function fixTumblrUrl(string $input): string
    {
        return $this->fixGenericUrl($input); // TODO: Implement fix
    }

    private function fixFacebookUrl(string $input): string
    {
        return Regexp::replace('#^(?:https?://)?(?:www\.|m\.|business\.)?facebook\.com/(?:pg/)?([^/?]+)(?:/posts|/about)?/?(\?(?!id=)[a-z_]+=[a-z_0-9]+)?$#i',
            'https://www.facebook.com/$1/', $this->fixGenericUrl($input));
    }

    private function fixYoutubeUrl(string $input): string
    {
        return Regexp::replace('#^(?:https?://)?(?:www|m)\.youtube\.com/((?:channel|user|c)/[^/?]+)(?:/featured)?(/|\?view_as=subscriber)?$#',
            'https://www.youtube.com/$1', $this->fixGenericUrl($input));
    }

    private function fixDeviantArtUrl(string $input): string
    {
        $result = $this->fixGenericUrl($input);
        $result = Regexp::replace('#^(?:https?://)?(?:www\.)?deviantart(?:\.net|\.com)?/([^/]+)(?:/gallery)?/?$#i',
            'https://www.deviantart.com/$1', $result);
        $result = Regexp::replace('#^(?:https?://)?(?:www\.)?([^.]+)\.deviantart(?:\.net|\.com)?/?$#i',
            'https://$1.deviantart.com/', $result);

        return $result;
    }

    private function fixGenericUrl(string $input): string
    {
        return $this->stringFixer->fix($input);
    }

    private function fixGenericUrlList(string $input)
    {
        return $this->freeListFixer->fix($input);
    }

    private function fixNotes(string $notes): string
    {
        return $this->stringFixer->fix($notes);
    }

    private function fixSince(string $input): string
    {
        return Regexp::replace('#(\d{4})-(\d{2})(?:-\d{2})?#', '$1-$2', trim($input));
    }

    private function fixIntro(string $input): string
    {
        return $this->stringFixer->fix(str_replace("\n", ' ', $input));
    }

    private function fixContactAllowed(string $contactPermit): string
    {
        $contactPermit = $this->stringFixer->fix($contactPermit);
        $contactPermit = str_replace(ContactPermit::getValues(), ContactPermit::getKeys(), $contactPermit);

        return $contactPermit;
    }

    private function fixLanguages(string $languages): string
    {
        $languages = $this->stringFixer->fix($languages);
        $languages = Regexp::split('#[\n,;&]|[, ]and #', $languages);
        $languages = array_filter(array_map('trim', $languages));
        $languages = array_map(function (string $language): string {
            Regexp::match(self::LANGUAGE_REGEXP, $language, $matches);

            $language = $matches['language'];
            $suffix = $matches['prefix'] || ($matches['suffix'] ?? '') ? ' (limited)' : '';

            $language = StrUtils::ucfirst($language);

            return $language.$suffix;
        }, $languages);

        sort($languages);

        return implode("\n", $languages);
    }

    private function fixSpecies(string $species): string
    {
        $species = $this->stringFixer->fix($species);

        return $species;
    }
}
