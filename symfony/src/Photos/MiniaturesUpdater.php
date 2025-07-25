<?php

declare(strict_types=1);

namespace App\Photos;

use App\Photos\MiniatureUrlResolver\FurtrackMiniatureUrlResolver;
use App\Photos\MiniatureUrlResolver\ScritchMiniatureUrlResolver;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Web\Url\Url;
use Psr\Log\LoggerInterface;

class MiniaturesUpdater
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly FurtrackMiniatureUrlResolver $furtrackResolver,
        private readonly ScritchMiniatureUrlResolver $scritchResolver,
    ) {
    }

    public function updateCreatorMiniaturesFor(Creator $creator, bool $force): void
    {
        if (!$force && count($creator->getMiniatureUrls()) === count($creator->getPhotoUrls())) {
            return; // No action required, skip silently
        }

        if (0 === count($creator->getPhotoUrls())) {
            $this->logger->info("Removing miniatures of {$creator->getLastCreatorId()}.");

            $creator->setMiniatureUrls([]);

            return;
        }

        if (!$this->supportsAll($creator->getPhotoUrlObjects())) {
            $this->logger->info("At least one unsupported URL for {$creator->getLastCreatorId()}. Discarding message.");

            return;
        }

        $this->logger->info("Updating miniatures for {$creator->getLastCreatorId()}...");

        try {
            $creator->setMiniatureUrls($this->resolveMiniatureUrlsFor($creator));

            $this->logger->info("Successfully updated miniatures for {$creator->getLastCreatorId()}.");
        } catch (MiniaturesUpdateException $exception) {
            $this->logger->error("Failed updating miniatures for {$creator->getLastCreatorId()}.", ['exception' => $exception]);
        }
    }

    /**
     * @return list<string>
     *
     * @throws MiniaturesUpdateException
     */
    private function resolveMiniatureUrlsFor(Creator $creator): array
    {
        $newMiniatureUrls = [];

        foreach ($creator->getPhotoUrlObjects() as $photoUrl) {
            $newMiniatureUrls[] = $this->getMiniatureUrl($photoUrl);
        }

        return $newMiniatureUrls;
    }

    /**
     * @throws MiniaturesUpdateException
     */
    public function getMiniatureUrl(Url $photoUrl): string
    {
        if ($this->furtrackResolver->supports($photoUrl->getUrl())) {
            return $this->furtrackResolver->getMiniatureUrl($photoUrl);
        }

        if ($this->scritchResolver->supports($photoUrl->getUrl())) {
            return $this->scritchResolver->getMiniatureUrl($photoUrl);
        }

        throw new MiniaturesUpdateException("Unsupported URL: {$photoUrl->getUrl()}.");
    }

    /**
     * @param array<Url> $photoUrls
     */
    public function supportsAll(array $photoUrls): bool
    {
        return array_all($photoUrls,
            fn (Url $url) => $this->furtrackResolver->supports($url->getUrl())
            || $this->scritchResolver->supports($url->getUrl()),
        );
    }
}
