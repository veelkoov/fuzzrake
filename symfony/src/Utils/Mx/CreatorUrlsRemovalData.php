<?php

namespace App\Utils\Mx;

use App\Data\Definitions\Fields\Field;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use Psl\Iter;
use Psl\Vec;

final class CreatorUrlsRemovalData
{
    private const array IGNORED_URL_TYPES = [
        Field::URL_COMMISSIONS,
        Field::URL_FURSUITREVIEW,
        Field::URL_OTHER,
    ];

    public readonly GroupedUrls $removedUrls;
    public readonly GroupedUrls $remainingUrls;

    public bool $hide;
    public bool $sendEmail = true;

    /**
     * @param string[] $urlIds
     */
    public function __construct(Creator $creator, array $urlIds)
    {
        $urls = GroupedUrls::from($creator);

        $this->removedUrls = $urls->onlyWithIds($urlIds);
        $this->remainingUrls = $urls->minus($this->removedUrls);

        // If there are no remaining valid URLs, hide the creator. Known issue: some URLs are not helpful.
        $this->hide = [] === Vec\filter(
            $this->remainingUrls->urls,
            fn (GroupedUrl $url): bool => !Iter\contains(self::IGNORED_URL_TYPES, $url->type),
        );
    }
}
