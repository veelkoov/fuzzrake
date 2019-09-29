<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;
use App\Utils\Artisan\ContactPermit;
use App\Utils\Artisan\Features;
use App\Utils\Artisan\Fields;
use App\Utils\Artisan\OrderTypes;
use App\Utils\Regexp\Utils as Regexp;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataFixer
{
    const REPLACEMENTS = [
        '#’#'                            => "'",
        '#^Rather not say$#i'            => '',
        '#^n/a$#i'                       => '',
        '#^n/a yet$#i'                   => '',
        '#[ \t]{2,}#'                    => ' ',
        '#^ANNOUNCEMENTS \+ FEEDBACK$#'  => 'FEEDBACK',
        '#^ANNOUNCEMENTS \*ONLY\*$#'     => 'ANNOUNCEMENTS',
        '#^NO \(I may join Telegram\)$#' => 'NO',
    ];

    const LIST_REPLACEMENTS = [
        'n/a'                                                         => '',

        'Three-fourth \(Head, handpaws, tail, legs/pants, feetpaws\)' => OrderTypes::THREE_FOURTH,
        'Partial \(Head, handpaws, tail, feetpaws\)'                  => OrderTypes::PARTIAL,
        'Mini partial \(Head, handpaws, tail\)'                       => OrderTypes::MINI_PARTIAL,
        'Three-fourth \(Head+handpaws+tail+legs/pants+feetpaws\)'     => OrderTypes::THREE_FOURTH,
        'Partial \(Head+handpaws+tail+feetpaws\)'                     => OrderTypes::PARTIAL,
        'Mini partial \(Head+handpaws+tail\)'                         => OrderTypes::MINI_PARTIAL,
        'Follow me eyes'                                              => Features::FOLLOW_ME_EYES,
        'Adjustable ears / wiggle ears'                               => Features::ADJUSTABLE_WIGGLE_EARS,

        'Excellent vision &amp; breathability'                        => 'Excellent vision & breathability',
        'Bases, jawsets, silicone noses/tongues'                      => "Bases\nJawsets\nSilicone noses\nSilicone tongues",
        'Silicone and resin parts'                                    => "Silicone parts\nResin parts",
        'accessories and cleaning'                                    => 'Accessories and cleaning', // TODO
        'backpacks'                                                   => 'Backpacks',
        'claws'                                                       => 'Claws',
        'Armsleeves|Arm Sleeves'                                      => 'Arm sleeves',
        'Head Bases'                                                  => 'Head bases',
        'Plushes'                                                     => 'Plushies',
        'Plushie, backpacks, bandanas, collars, general accessories'  => "Plushies\nBackpacks\nBandanas\nCollars\nGeneral accessories",
        'Eyes, noses, claws'                                          => "Eyes\nNoses\nClaws",
        'Resin and silicone parts'                                    => "Resin parts\nSilicone parts",
        'Sleeves \(legs and arms\)'                                   => "Arm sleeves\nLeg sleeves",
        'Fursuit Props'                                               => 'Fursuit props',
        'Fursuit Props and Accessories, Fursuit supplies'             => "Fursuit props\nFursuit accessories\nFursuit supplies",
        'Fleece Props, Other accessories'                             => "Fleece props\nOther accessories",
        'Sock paws'                                                   => 'Sockpaws',
        'Removable magnetic parts, secret pockets'                    => "Removable magnetic parts\nHidden pockets",
        'Plush Suits'                                                 => 'Plush suits',
        'Femme Suits'                                                 => 'Femme suits',
        'Just Ask'                                                    => 'Just ask',
        'props and can do plushies'                                   => "Props\nCan do plushies",
        'Removable Eyes'                                              => 'Removable eyes',
        'Removable/interchangeable eyes'                              => "Removable eyes\nInterchangeable eyes",
        'Pickable Nose'                                               => 'Pickable nose',
        '(.+)changable(.+)'                                           => '$1changeable$2',
        'Fursuit Sprays?'                                             => 'Fursuit sprays',
        'Arm sleeves, plush props, fursuit spray'                     => "Arm sleeves\nPlush props\nFursuit sprays",
        'Body padding/plush suits'                                    => "Body padding\nPlush suits",
        'Dry brushing'                                                => 'Drybrushing',
        'Bendable wings and tails'                                    => "Bendable wings\nBendable tails",

        'QQQQQ'                                                       => 'QQQQQ',
    ];

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
        'ukraine'                                       => 'UA',
        'united states( of america)?|us of america|usa' => 'US',
    ];

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var DataDiffer
     */
    private $differ;

    /**
     * @var bool
     */
    private $showDiff;

    public function __construct(SymfonyStyle $io, bool $showDiff)
    {
        $this->io = $io;
        $this->io->getFormatter()->setStyle('wrong', new OutputFormatterStyle('red'));

        $this->differ = new DataDiffer($io);

        $this->showDiff = $showDiff;
    }

    public function fixArtisanData(Artisan $artisan): Artisan
    {
        $originalArtisan = clone $artisan;

        $artisan->setName($this->fixString($artisan->getName()));
        $artisan->setSince($this->fixSince($artisan->getSince()));
        $artisan->setSpeciesDoes($this->fixString($artisan->getSpeciesDoes()));
        $artisan->setSpeciesDoesnt($this->fixString($artisan->getSpeciesDoesnt()));

        $artisan->setProductionModels($this->fixList($artisan->getProductionModels(), true, '#[;\n]#'));
        $artisan->setFeatures($this->fixList($artisan->getFeatures(), true, '#[;\n]#'));
        $artisan->setStyles($this->fixList($artisan->getStyles(), true, '#[;\n]#'));
        $artisan->setOrderTypes($this->fixList($artisan->getOrderTypes(), true, '#[;\n]#'));
        $artisan->setOtherFeatures($this->fixList($artisan->getOtherFeatures(), false, '#\n#'));
        $artisan->setOtherStyles($this->fixList($artisan->getOtherStyles(), false, '#\n#'));
        $artisan->setOtherOrderTypes($this->fixList($artisan->getOtherOrderTypes(), false, '#\n#'));

        $artisan->setCountry($this->fixCountry($artisan->getCountry()));
        $artisan->setState($this->fixString($artisan->getState()));
        $artisan->setCity($this->fixString($artisan->getCity()));

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

        $artisan->setOtherUrls($this->fixString($artisan->getOtherUrls()));

        $artisan->setPaymentPlans($this->fixString($artisan->getPaymentPlans()));
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

    private function fixList(string $input, bool $sort, string $separatorRegexp): string
    {
        $input = implode("\n", array_filter(array_map('trim', Regexp::split($separatorRegexp, $input))));

        foreach (self::LIST_REPLACEMENTS as $pattern => $replacement) {
            $input = Regexp::replace("#(?<=^|\n)$pattern(?=\n|$)#i", $replacement, $input);
        }

        $input = explode("\n", $input);

        if ($sort) {
            sort($input);
        }

        return implode("\n", array_unique($input));
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
        return $this->fixString($input);
    }

    private function fixNotes(string $notes): string
    {
        return $this->fixString($notes);
    }

    private function fixSince(string $input): string
    {
        return Regexp::replace('#(\d{4})-(\d{2})(?:-\d{2})?#', '$1-$2', trim($input));
    }

    private function fixString(string $subject): string
    {
        foreach (self::REPLACEMENTS as $pattern => $replacement) {
            $subject = Regexp::replace($pattern, $replacement, $subject);
        }

        return trim($subject);
    }

    private function fixIntro(string $input): string
    {
        return $this->fixString(str_replace("\n", ' ', $input));
    }

    private function fixContactAllowed(string $contactPermit): string
    {
        $contactPermit = $this->fixString($contactPermit);
        $contactPermit = str_replace(ContactPermit::getValues(), ContactPermit::getKeys(), $contactPermit);

        return $contactPermit;
    }

    private function fixLanguages(string $languages): string
    {
        $languages = Regexp::split('#[\n,;&]|and#', $languages);
        $languages = array_filter(array_map('trim', $languages));
        $languages = array_map(function (string $language): string {
            Regexp::match('#(?<prefix>a small bit of |bit of |a little |some |moderate |elementary |slight )?(?<language>.+)#i', $language, $matches);

            $prefix = $matches['prefix'] ? 'Limited ' : '';

            return $prefix.mb_convert_case($matches['language'], MB_CASE_TITLE);
        }, $languages);

        return implode("\n", $languages);
    }
}
