<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Artisan;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataFixer
{
    const REPLACEMENTS = [
        'Follow me eyes' => 'Follow-me eyes',
        'Adjustable ears / wiggle ears' => 'Adjustable/wiggle ears',
        'Three-fourth (Head, handpaws, tail, legs/pants, feetpaws)' => 'Three-fourth (head + handpaws + tail + legs/pants + feetpaws)',
        'Partial (Head, handpaws, tail, feetpaws)' => 'Partial (head + handpaws + tail + feetpaws)',
        'Mini partial (Head, handpaws, tail)' => 'Mini partial (head + handpaws + tail)',
        'Three-fourth (Head+handpaws+tail+legs/pants+feetpaws)' => 'Three-fourth (head + handpaws + tail + legs/pants + feetpaws)',
        'Partial (Head+handpaws+tail+feetpaws)' => 'Partial (head + handpaws + tail + feetpaws)',
        'Mini partial (Head+handpaws+tail)' => 'Mini partial (head + handpaws + tail)',
        '’' => "'",
    ];

    const COUNTRIES_REPLACAMENTS = [
        'argentina' => 'AR',
        'australia' => 'AU',
        'belgium' => 'BE',
        'canada' => 'CA',
        'czech republic' => 'CZ',
        'denmark' => 'DK',
        'germany' => 'DE',
        'uk|england|united kingdom' => 'GB',
        'ireland' => 'IE',
        'italia|italy' => 'IT',
        '(the )?netherlands' => 'NL',
        'russia' => 'RU',
        'ukraine' => 'UA',
        'united states|USA' => 'US',
    ];

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @param SymfonyStyle $io
     */
    public function __construct(SymfonyStyle $io)
    {
        $this->io = $io;
        $this->io->getFormatter()->setStyle('wrong', new OutputFormatterStyle('red'));
    }

    public function fixArtisanData(Artisan $artisan): Artisan
    {
        $artisan->setName($this->fixString($artisan->getName()));
        $artisan->setSince($this->fixSince($artisan->getSince()));

        $artisan->setProductionModel($this->fixList($artisan->getProductionModel()));
        $artisan->setFeatures($this->fixList($artisan->getFeatures()));
        $artisan->setStyles($this->fixList($artisan->getStyles()));
        $artisan->setTypes($this->fixList($artisan->getTypes()));
        $artisan->setOtherFeatures($this->fixList($artisan->getOtherFeatures()));
        $artisan->setOtherStyles($this->fixList($artisan->getOtherStyles()));
        $artisan->setOtherTypes($this->fixList($artisan->getOtherTypes()));

        $artisan->setCountry($this->fixCountry($artisan->getCountry()));
        $artisan->setState($this->fixString($artisan->getState()));
        $artisan->setCity($this->fixString($artisan->getCity()));

        $artisan->setFurAffinityUrl($this->fixFurAffinityUrl($artisan->getFurAffinityUrl()));
        $artisan->setDeviantArtUrl($this->fixDeviantArtUrl($artisan->getDeviantArtUrl()));
        $artisan->setTwitterUrl($this->fixTwitterUrl($artisan->getTwitterUrl()));
        $artisan->setInstagramUrl($this->fixInstagramUrl($artisan->getInstagramUrl()));
        $artisan->setTumblrUrl($this->fixTumblrUrl($artisan->getTumblrUrl()));
        $artisan->setFacebookUrl($this->fixFacebookUrl($artisan->getFacebookUrl()));
        $artisan->setYoutubeUrl($this->fixYoutubeUrl($artisan->getYoutubeUrl()));

        $artisan->setIntro($this->fixString($artisan->getIntro()));
        $artisan->setNotes($this->fixNotes($artisan->getNotes()));

        return $artisan;
    }

    public function validateArtisanData(Artisan $artisan): void
    {
        foreach (ArtisanMetadata::MODEL_FIELDS_VALIDATION_REGEXPS as $prettyFieldName => $validationRegexp) {
            $fieldValue = $artisan->get(ArtisanMetadata::PRETTY_TO_MODEL_FIELD_NAMES_MAP[$prettyFieldName]);

            if (!preg_match($validationRegexp, $fieldValue)) {
                $this->io->writeln("{$artisan->getMakerId()}:{$prettyFieldName}:|:<wrong>{$fieldValue}</>|ABCDEFGHIJ|");
            }
        }
    }

    private function fixList(string $input): string
    {
        $cslist = str_replace(array_keys(self::REPLACEMENTS), array_values(self::REPLACEMENTS), $input);
        $list = preg_split('#[;\n]#', $cslist);
        $list = array_map('trim', $list);
        $list = array_filter($list);
        sort($list);
        $result = implode("\n", $list);

        return $result;
    }

    private function fixCountry(string $input): string
    {
        $result = trim($input);

        foreach (self::COUNTRIES_REPLACAMENTS as $regexp => $replacement) {
            $result = preg_replace("#^$regexp$#i", $replacement, $result);
        }

        return $result;
    }

    private function fixFurAffinityUrl(string $input): string
    {
        return preg_replace('#^(?:https?://)?(?:www\.)?furaffinity(?:\.net|\.com)?/(?:user/|gallery/)?([^/]+)/?$#i',
            'http://www.furaffinity.net/user/$1', trim($input));
    }

    private function fixTwitterUrl(string $input): string
    {
        return preg_replace('#^(?:(?:(?:https?://)?(?:www\.|mobile\.)?twitter(?:\.com)?/)|@)([^/?]+)/?(?:\?lang=[a-z]{2,3})?$#i',
            'https://twitter.com/$1', trim($input));
    }

    private function fixInstagramUrl(string $input): string
    {
        return preg_replace('#^(?:(?:(?:https?://)?(?:www\.)?instagram(?:\.com)?/)|@)([^/?]+)/?(?:\?hl=[a-z]{2,3}(?:-[a-z]{2,3})?)?$#i',
            'https://www.instagram.com/$1/', trim($input));
    }

    private function fixTumblrUrl(string $input): string
    {
        return trim($input); // TODO: Implement fix
    }

    private function fixFacebookUrl(string $input): string
    {
        return preg_replace('#^(?:https?://)?(?:www\.|m\.|business\.)?facebook\.com/([^/?]+)/?(\?ref=[a-z_]+)?$#i',
            'https://www.facebook.com/$1/', trim($input));
    }

    private function fixYoutubeUrl(string $input): string
    {
        return preg_replace('#^(?:https?://)?(?:www|m)\.youtube\.com/((?:channel|user|c)/[^/?]+)(?:/featured)?(/|\?view_as=subscriber)?$#',
            'https://www.youtube.com/$1', trim($input));
    }

    private function fixDeviantArtUrl(string $input): string
    {
        $result = trim($input);
        $result = preg_replace('#^(?:https?://)?(?:www\.)?deviantart(?:\.net|\.com)?/([^/]+)(?:/gallery)?/?$#i',
            'https://www.deviantart.com/$1', $result);
        $result = preg_replace('#^(?:https?://)?(?:www\.)?([^.]+)\.deviantart(?:\.net|\.com)?/?$#i',
            'https://$1.deviantart.com/', $result);

        return $result;
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
}
