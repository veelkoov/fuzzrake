<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Data\Definitions\Fields\Fields;
use App\Entity\ArtisanUrl;
use App\Utils\StringList;

final class SmartUrlAccessor
{
    /**
     * @var array<string, list<ArtisanUrl>>
     */
    private static ?array $initUrls = null;

    /**
     * @var array<string, list<ArtisanUrl>>
     */
    private array $urls;

    public function __construct(
        private readonly SmartAccessDecorator $artisan,
    ) {
        self::$initUrls = array_combine(Fields::urls()->names(), array_map(fn ($_) => [], Fields::urls()->names()));

        $this->urls = self::$initUrls;

        foreach ($this->artisan->getUrls() as $url) {
            $this->urls[$url->getType()][] = $url;
        }
    }

    /**
     * @return list<ArtisanUrl>
     */
    public function getObjects(string $name): array
    {
        return $this->urls[$name];
    }

    /**
     * @return list<string>
     */
    public function getList(string $type): array
    {
        return array_map(fn (ArtisanUrl $url) => $url->getUrl(), $this->getObjects($type));
    }

    public function getPacked(string $type): string
    {
        return StringList::pack($this->getList($type));
    }

    public function setPacked(string $type, string $newUrl): void
    {
        $newObjects = [];
        $existingObjects = $this->getObjects($type);
        $existingUrls = $this->getList($type);
        $wantedUrls = StringList::unpack($newUrl);

        foreach ($existingObjects as $existingObject) {
            if (in_array($existingObject->getUrl(), $wantedUrls, true)) {
                $newObjects[] = $existingObject;
            } else {
                $this->artisan->getArtisan()->removeUrl($existingObject);
            }
        }

        foreach ($wantedUrls as $wantedUrl) {
            if (!in_array($wantedUrl, $existingUrls, true)) {
                $newObject = (new ArtisanUrl())->setType($type)->setUrl($wantedUrl);
                $newObjects[] = $newObject;
                $this->artisan->getArtisan()->addUrl($newObject);
            }
        }

        $this->urls[$type] = $newObjects;
    }
}
