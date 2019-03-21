<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataFixer
{
    const REPLACEMENTS = [
        '’' => "'",
        'Rather not say' => '',
        'N/a' => '',
    ];

    const LIST_REPLACEMENTS = [
        'Three-fourth \(Head, handpaws, tail, legs/pants, feetpaws\)' => 'Three-fourth (head + handpaws + tail + legs/pants + feetpaws)',
        'Partial \(Head, handpaws, tail, feetpaws\)' => 'Partial (head + handpaws + tail + feetpaws)',
        'Mini partial \(Head, handpaws, tail\)' => 'Mini partial (head + handpaws + tail)',
        'Three-fourth \(Head+handpaws+tail+legs/pants+feetpaws\)' => 'Three-fourth (head + handpaws + tail + legs/pants + feetpaws)',
        'Partial \(Head+handpaws+tail+feetpaws\)' => 'Partial (head + handpaws + tail + feetpaws)',
        'Mini partial \(Head+handpaws+tail\)' => 'Mini partial (head + handpaws + tail)',

        'Follow me eyes' => 'Follow-me eyes',
        'Adjustable ears / wiggle ears' => 'Adjustable/wiggle ears',
        'Bases, jawsets, silicone noses/tongues' => "Bases\nJawsets\nSilicone noses\nSilicone tongues",
        'Silicone and resin parts' => "Silicone parts\nResin parts",
        'accessories and cleaning' => 'Accessories and cleaning', // TODO
        'backpacks' => 'Backpacks',
        'claws' => 'Claws',
        'Armsleeves' => 'Arm sleeves',
        'Head Bases' => 'Head bases',
        'Plushes' => 'Plushies',
        'Plushie, backpacks, bandanas, collars, general accessories' => "Plushies\nBackpacks\nBandanas\nCollars\nGeneral accessories",
        'Eyes, noses, claws' => "Eyes\nNoses\nClaws",
        'Resin and silicone parts' => "Resin parts\nSilicone parts",
        'Sleeves \(legs and arms\)' => "Arm sleeves\nLeg sleeves",
        'Fursuit Props' => 'Fursuit props',
        'Fursuit Props and Accessories, Fursuit supplies' => "Fursuit props\nFursuit accessories\nFursuit supplies",
        'Fleece Props, Other accessories' => "Fleece props\nOther accessories",
        'Sock paws' => 'Sockpaws',
        'Removable magnetic parts, secret pockets' => "Removable magnetic parts\nHidden pockets",
        'QQQQQ' => 'QQQQQ',
    ];

    const COUNTRIES_REPLACEMENTS = [
        'argentina' => 'AR',
        'australia' => 'AU',
        'belgium' => 'BE',
        'canada' => 'CA',
        'czech republic' => 'CZ',
        'denmark' => 'DK',
        'germany' => 'DE',
        'finland' => 'FI',
        'uk|england|united kingdom' => 'GB',
        'ireland' => 'IE',
        'italia|italy' => 'IT',
        '(the )?netherlands' => 'NL',
        'russia' => 'RU',
        'ukraine' => 'UA',
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

    /**
     * @param SymfonyStyle $io
     * @param bool         $showDiff
     */
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

        $artisan->setProductionModel($this->fixList($artisan->getProductionModel(), true));
        $artisan->setFeatures($this->fixList($artisan->getFeatures(), true));
        $artisan->setStyles($this->fixList($artisan->getStyles(), true));
        $artisan->setTypes($this->fixList($artisan->getTypes(), true));
        $artisan->setOtherFeatures($this->fixList($artisan->getOtherFeatures(), false));
        $artisan->setOtherStyles($this->fixList($artisan->getOtherStyles(), false));
        $artisan->setOtherTypes($this->fixList($artisan->getOtherTypes(), false));

        $artisan->setCountry($this->fixCountry($artisan->getCountry()));
        $artisan->setState($this->fixString($artisan->getState()));
        $artisan->setCity($this->fixString($artisan->getCity()));

        $artisan->setCommisionsQuotesCheckUrl($this->fixGenericUrl($artisan->getCommisionsQuotesCheckUrl()));
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

        $artisan->setIntro($this->fixIntro($artisan->getIntro()));
        $artisan->setNotes($this->fixNotes($artisan->getNotes()));
        $artisan->setLanguages($this->fixString($artisan->getLanguages()));

        if ($this->showDiff) {
            $this->differ->showDiff($originalArtisan, $artisan);
        }

        return $artisan;
    }

    public function validateArtisanData(Artisan $artisan): void
    {
        foreach (ArtisanMetadata::MODEL_FIELDS_VALIDATION_REGEXPS as $prettyFieldName => $validationRegexp) {
            $fieldValue = $artisan->get(ArtisanMetadata::PRETTY_TO_MODEL_FIELD_NAMES_MAP[$prettyFieldName]);

            if (!preg_match($validationRegexp, $fieldValue)) {
                $fieldValue = Utils::safeStr($fieldValue);
                $this->io->writeln("wr:{$artisan->getMakerId()}:$prettyFieldName:|:<wrong>$fieldValue</>|$fieldValue|");
            }
        }
    }

    private function fixList(string $input, bool $sort): string
    {
        $list = array_filter(array_map(function ($item) {
            $item = trim($item);

            foreach (self::LIST_REPLACEMENTS as $pattern => $replacement) {
                $item = preg_replace("#(?:^|\n)$pattern(?:\n|$)#i", $replacement, $item);
            }

            return $item;
        }, preg_split('#[;\n]#', $input)));

        $list = ($list);

        if ($sort) {
            sort($list);
        }

        return implode("\n", array_unique($list));
    }

    private function fixCountry(string $input): string
    {
        $result = trim($input);

        foreach (self::COUNTRIES_REPLACEMENTS as $regexp => $replacement) {
            $result = preg_replace("#^$regexp$#i", $replacement, $result);
        }

        return $result;
    }

    private function fixFurAffinityUrl(string $input): string
    {
        return preg_replace('#^(?:https?://)?(?:www\.)?furaffinity(?:\.net|\.com)?/(?:user/|gallery/)?([^/]+)/?$#i',
            'http://www.furaffinity.net/user/$1', $this->fixGenericUrl($input));
    }

    private function fixTwitterUrl(string $input): string
    {
        return preg_replace('#^(?:(?:(?:https?://)?(?:www\.|mobile\.)?twitter(?:\.com)?/)|@)([^/?]+)/?(?:\?(?:lang=[a-z]{2,3}|s=\d+))?$#i',
            'https://twitter.com/$1', $this->fixGenericUrl($input));
    }

    private function fixInstagramUrl(string $input): string
    {
        return preg_replace('#^(?:(?:(?:https?://)?(?:www\.)?instagram(?:\.com)?/)|@)([^/?]+)/?(?:\?hl=[a-z]{2,3}(?:-[a-z]{2,3})?)?$#i',
            'https://www.instagram.com/$1/', $this->fixGenericUrl($input));
    }

    private function fixTumblrUrl(string $input): string
    {
        return $this->fixGenericUrl($input); // TODO: Implement fix
    }

    private function fixFacebookUrl(string $input): string
    {
        return preg_replace('#^(?:https?://)?(?:www\.|m\.|business\.)?facebook\.com/(?:pg/)?([^/?]+)(?:/posts)?/?(\?ref=[a-z_]+)?$#i',
            'https://www.facebook.com/$1/', $this->fixGenericUrl($input));
    }

    private function fixYoutubeUrl(string $input): string
    {
        return preg_replace('#^(?:https?://)?(?:www|m)\.youtube\.com/((?:channel|user|c)/[^/?]+)(?:/featured)?(/|\?view_as=subscriber)?$#',
            'https://www.youtube.com/$1', $this->fixGenericUrl($input));
    }

    private function fixDeviantArtUrl(string $input): string
    {
        $result = $this->fixGenericUrl($input);
        $result = preg_replace('#^(?:https?://)?(?:www\.)?deviantart(?:\.net|\.com)?/([^/]+)(?:/gallery)?/?$#i',
            'https://www.deviantart.com/$1', $result);
        $result = preg_replace('#^(?:https?://)?(?:www\.)?([^.]+)\.deviantart(?:\.net|\.com)?/?$#i',
            'https://$1.deviantart.com/', $result);

        return $result;
    }

    private function fixGenericUrl(string $input): string
    {
        return trim($this->fixString($input));
    }

    private function fixNotes(string $input): string
    {
        $result = preg_replace('#([,;])([,; ]*[,;])#s', '$1', trim($input));
        $result = str_replace('@', '(e)', $result);
        $result = preg_replace('#(e-?)?mail#i', 'eeeee', $result);

        return $result;
    }

    private function fixSince(string $input): string
    {
        return preg_replace('#(\d{4})-(\d{2})(?:-\d{2})?#', '$1-$2', trim($input));
    }

    private function fixString(string $input): string
    {
        $result = str_replace(array_keys(self::REPLACEMENTS), array_values(self::REPLACEMENTS), $input);
        $result = preg_replace('#[ \t]{2,}#', ' ', $result);

        return trim($result);
    }

    private function fixIntro(string $input): string
    {
        return $this->fixString(str_replace("\n", ' ', $input));
    }
}
