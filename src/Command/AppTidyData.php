<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AppTidyData extends Command
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
    ];

    protected static $defaultName = 'app:tidy-data';

    /**
     * @var ArtisanRepository
     */
    private $artisanRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SymfonyStyle
     */
    private $io;

    public function __construct(ArtisanRepository $artisanRepository, ObjectManager $objectManager)
    {
        $this->artisanRepository = $artisanRepository;
        $this->objectManager = $objectManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('dry-run', 'd', null, 'Dry run (don\'t update the DB)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        foreach ($this->artisanRepository->findAll() as $artisan) {
            $this->fixArtisanData($artisan);
        }

        if (!$input->getOption('dry-run')) {
            $this->objectManager->flush();
            $this->io->success('Finished and saved');
        } else {
            $this->io->success('Finished without saving');
        }
    }

    private function fixArtisanData(Artisan $artisan): void
    {
        $artisan->setSince($this->fixSince($artisan->getSince()));

        $artisan->setFeatures($this->fixList($artisan->getFeatures()));
        $artisan->setStyles($this->fixList($artisan->getStyles()));
        $artisan->setTypes($this->fixList($artisan->getTypes()));
        $artisan->setCountry($this->fixCountry($artisan->getCountry()));
        $artisan->setState($this->trim($artisan->getState()));
        $artisan->setCity($this->trim($artisan->getCity()));

        $artisan->setFurAffinityUrl($this->fixFurAffinityUrl($artisan->getFurAffinityUrl()));
        $artisan->setDeviantArtUrl($this->fixDeviantArtUrl($artisan->getDeviantArtUrl()));
        $artisan->setTwitterUrl($this->fixTwitterUrl($artisan->getTwitterUrl()));
        $artisan->setInstagramUrl($this->fixInstagramUrl($artisan->getInstagramUrl()));
        $artisan->setTumblrUrl($this->fixTumblrUrl($artisan->getTumblrUrl()));
        $artisan->setFacebookUrl($this->fixFacebookUrl($artisan->getFacebookUrl()));
        $artisan->setYoutubeUrl($this->fixYoutubeUrl($artisan->getYoutubeUrl()));

        $artisan->setNotes($this->fixNotes($artisan->getNotes()));
    }

    private function showDiff(string $input, string $result, $validRegexp = '.*'): void
    {
        $out = false;

        if ($result !== $input) {
            $this->io->text("--- ' $input '\n +++ ' $result '");
            $out = true;
        }

        if ('' !== trim($input) && '' !== $result && !preg_match("#^($validRegexp)$#", $result)) {
            $this->io->text("!!! ' $result '");
            $out = true;
        }

        if ($out) {
            $this->io->text('');
        }
    }

    private function fixList(string $input): string
    {
        $cslist = str_replace(array_keys(self::REPLACEMENTS), array_values(self::REPLACEMENTS), $input);
        $list = preg_split('#[;,\n]#', $cslist);
        $list = array_map('trim', $list);
        $list = array_filter($list);
        sort($list);
        $result = implode(', ', $list);

        $this->showDiff($input, $result);

        return $result;
    }

    private function fixCountry(string $input): string
    {
        $replacements = [
            'ukraine' => 'UA',
            'united states|USA' => 'US',
            'argentina' => 'AR',
            'belgium' => 'BE',
            'canada' => 'CA',
            'czech republic' => 'CZ',
            'denmark' => 'DK',
            'uk|england|united kingdom' => 'GB',
            'germany' => 'DE',
            'ireland' => 'IE',
            'italia|italy' => 'IT',
            '(the )?netherlands' => 'NL',
            'russia' => 'RU',
        ];

        $result = trim($input);

        foreach ($replacements as $regexp => $replacement) {
            $result = preg_replace("#^$regexp$#i", $replacement, $result);
        }

        $this->showDiff($input, $result, '[A-Z]{2}');

        return $result;
    }

    private function fixFurAffinityUrl(string $input): string
    {
        $result = preg_replace('#^(?:https?://)?(?:www\.)?furaffinity(?:\.net|\.com)?/(?:user/)?([^/]+)/?$#i',
            'http://www.furaffinity.net/user/$1', trim($input));

        $this->showDiff($input, $result, 'http://www\.furaffinity\.net/user/[^/]+');

        return $result;
    }

    private function fixTwitterUrl(string $input): string
    {
        $result = preg_replace('#^(?:(?:(?:https?://)?(?:www\.|mobile\.)?twitter(?:\.com)?/)|@)([^/?]+)/?(?:\?lang=[a-z]{2,3})?$#i',
            'https://twitter.com/$1', trim($input));

        $this->showDiff($input, $result, 'https://twitter\.com/[^/]+');

        return $result;
    }

    private function fixInstagramUrl(string $input): string
    {
        $result = preg_replace('#^(?:(?:(?:https?://)?(?:www\.)?instagram(?:\.com)?/)|@)([^/?]+)/?(?:\?hl=[a-z]{2,3}(?:-[a-z]{2,3})?)?$#i',
            'https://www.instagram.com/$1/', trim($input));

        $this->showDiff($input, $result, 'https://www\.instagram\.com/[^/]+/');

        return $result;
    }

    private function fixTumblrUrl(string $input): string
    {
        $result = trim($input);

        $this->showDiff($input, $result, 'https?://[^.]+\.tumblr\.com/');

        return $result;
    }

    private function fixFacebookUrl(string $input): string
    {
        $result = preg_replace('#^(?:https?://)?(?:www\.|m\.|business\.)?facebook\.com/([^/?]+)/?(\?ref=[a-z_]+)?$#i',
            'https://www.facebook.com/$1/', trim($input));

        $this->showDiff($input, $result, 'https://www.facebook.com/[^/]+/');

        return $result;
    }

    private function fixYoutubeUrl(string $input): string
    {
        $result = preg_replace('#^(?:https?://)?(?:www|m)\.youtube\.com/((?:channel|user|c)/[^/?]+)(?:/featured)?(/|\?view_as=subscriber)?$#',
            'https://www.youtube.com/$1', trim($input));

        $this->showDiff($input, $result, 'https://www\.youtube\.com/(channel|user|c)/[^/?]+');

        return $result;
    }

    private function fixDeviantArtUrl(string $input): string
    {
        $result = trim($input);
        $result = preg_replace('#^(?:https?://)?(?:www\.)?deviantart(?:\.net|\.com)?/([^/]+)(?:/gallery)?/?$#i',
            'https://www.deviantart.com/$1', $result);
        $result = preg_replace('#^(?:https?://)?(?:www\.)?([^.]+)\.deviantart(?:\.net|\.com)?/?$#i',
            'https://$1.deviantart.com/', $result);

        $this->showDiff($input, $result, 'https://www\.deviantart\.com/[^/]+|https://[^.]+\.deviantart\.com/');

        return $result;
    }

    private function fixNotes(string $input): string
    {
        $result = preg_replace('#([,;])([,; ]*[,;])#s', '$1', trim($input));
        $result = str_replace('@', '(e)', $result);
        $result = preg_replace('#(e-?)?mail#i', 'eeeee', $result);

        $this->showDiff($input, $result, '(.|\n)*');

        return $result;
    }

    private function fixSince(string $input)
    {
        $result = preg_replace('#(\d{4})-(\d{2})(?:-\d{2})?#', '$1-$2', trim($input));

        $this->showDiff($input, $result, '\d{4}-\d{2}');

        return $result;
    }

    private function trim(string $input)
    {
        $result = trim($input);

        $this->showDiff($input, $result);

        return $result;
    }
}
