<?php

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
        $artisan->setFeatures($this->fixList($artisan->getFeatures()));
        $artisan->setStyles($this->fixList($artisan->getStyles()));
        $artisan->setCountry($this->fixCountry($artisan->getCountry()));

        $artisan->setFurAffinityUrl($this->fixFurAffinityUrl($artisan->getFurAffinityUrl()));
        $artisan->setDeviantArtUrl($this->fixDeviantArtUrl($artisan->getDeviantArtUrl()));
        $artisan->setTwitterUrl($this->fixTwitterUrl($artisan->getTwitterUrl()));
        $artisan->setInstagramUrl($this->fixInstagramUrl($artisan->getInstagramUrl()));
        $artisan->setTumblrUrl($this->fixTumblrUrl($artisan->getTumblrUrl()));
        $artisan->setFacebookUrl($this->fixFacebookUrl($artisan->getFacebookUrl()));
        $artisan->setYoutubeUrl($this->fixYoutubeUrl($artisan->getYoutubeUrl()));
    }

    private function showDiff(string $input, string $result, $validRegexp = '.*'): void
    {
        $out = false;

        if ($result !== $input) {
            $this->io->text("--- ' $input '\n +++ ' $result '");
            $out = true;
        }

        if (trim($input) !== '' && $result !== '' && !preg_match("#^($validRegexp)$#", $result)) {
            $this->io->text("!!! ' $result '");
            $out = true;
        }

        if ($out) {
            $this->io->text('');
        }
    }

    private function fixList(string $input): string
    {
        $list = preg_split('#[;,\n]#', $input);
        $list = array_map('trim', $list);
        $list = array_filter($list);
        sort($list);
        $result = implode(', ', $list);
        $result = str_replace(['Follow me eyes', 'Adjustable ears / wiggle ears'], ['Follow-me eyes', 'Adjustable/wiggle ears'], $result);

        $this->showDiff($input, $result);

        return $result;
    }

    private function fixCountry(string $input): string
    {
        $replacements = [
            'united states|USA' => 'US',
            'argentina' => 'AR',
            'belgium' => 'BE',
            'canada' => 'CA',
            'denmark' => 'DK',
            'uk|england' => 'GB',
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
        $result = preg_replace('#^(?:(?:(?:https?://)?(?:www\.)?instagram(?:\.com)?/)|@)([^/?]+)/?(?:\?hl=[a-z]{2,3}(?:-[a-z]{2,3}))?$#i',
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
        $result = preg_replace('#^(?:https?://)?(?:www\.|m\.)?facebook\.com/([^/?]+)/?(\?ref=[a-z_]+)?$#i',
            'https://www.facebook.com/$1/', trim($input));

        $this->showDiff($input, $result, 'https://www.facebook.com/[^/]+/');

        return $result;
    }

    private function fixYoutubeUrl(string $input): string
    {
        $result = preg_replace('#^(?:https?://)?(?:www|m)\.youtube\.com/((?:channel|user)/[^/?]+)(/|\?view_as=subscriber)?$#',
            'https://www.youtube.com/$1', trim($input));

        $this->showDiff($input, $result, 'https://www\.youtube\.com/(channel|user)/[^/?]+');

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
}
