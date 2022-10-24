<?php

declare(strict_types=1);

namespace App\Tasks\Miniatures;

use App\Tasks\Miniatures\Queries\FurtrackQuery;
use App\Tasks\Miniatures\Queries\ScritchQuery;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StringList;
use JsonException;
use LogicException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class MiniaturesUpdater
{
    public function __construct(
        private readonly ScritchQuery $scritch,
        private readonly FurtrackQuery $furtrack,
    ) {
    }

    public function update(Artisan $artisan): UpdateResult|string
    {
        $pictureUrls = StringList::unpack($artisan->getPhotoUrls());

        if (empty($pictureUrls)) {
            if ('' === $artisan->getMiniatureUrls()) {
                return UpdateResult::NO_CHANGE;
            }

            $artisan->setMiniatureUrls('');

            return UpdateResult::CLEARED;
        }

        if (count($pictureUrls) === count(StringList::unpack($artisan->getMiniatureUrls()))) {
            return UpdateResult::NO_CHANGE;
        }

        $unsupported = $this->filterUnsupportedUrls($pictureUrls);
        if (0 !== count($unsupported)) {
            return 'Unsupported URLs: "'.implode('", "', $unsupported).'"';
        }

        try {
            $miniatureUrls = $this->retrieveMiniatureUrls($pictureUrls);
        } catch (ExceptionInterface|JsonException|LogicException $e) {
            return 'Details: '.$e->getMessage();
        }

        $artisan->setMiniatureUrls(StringList::pack($miniatureUrls));

        return UpdateResult::RETRIEVED;
    }

    /**
     * @param string[] $pictureUrls
     *
     * @return string[]
     *
     * @throws JsonException|LogicException|ExceptionInterface
     */
    private function retrieveMiniatureUrls(array $pictureUrls): array
    {
        $result = [];

        foreach ($pictureUrls as $url) {
            if ($this->furtrack->supportsUrl($url)) {
                $result[] = $this->furtrack->getMiniatureUrl($url);
            } else {
                $result[] = $this->scritch->getMiniatureUrl($url);
            }
        }

        return $result;
    }

    /**
     * @param string[] $pictureUrls
     *
     * @return string[]
     */
    private function filterUnsupportedUrls(array $pictureUrls): array
    {
        $result = [];

        foreach ($pictureUrls as $url) {
            if (!$this->scritch->supportsUrl($url) && !$this->furtrack->supportsUrl($url)) {
                $result[] = $url;
            }
        }

        return $result;
    }
}
