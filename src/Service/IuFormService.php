<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artisan;

class IuFormService
{
    /**
     * @var string
     */
    private $iuFormUrl;

    public function __construct(string $iuFormUrl)
    {
        $this->iuFormUrl = $iuFormUrl;
    }

    public function getUpdateUrl(Artisan $artisan): string
    {
        $input = [ // TODO: move to Fields class
            646315912  => $artisan->getName(),
            6087327    => $artisan->getFormerly(),
            764970912  => $this->transformSince($artisan->getSince()),
            1452524703 => $artisan->getCountry(),
            355015034  => $artisan->getState(),
            944749751  => $artisan->getCity(),
            743737005  => $artisan->getPaymentPlans(),
            2034494235 => $artisan->getPricesUrl(),
            838362497  => $this->transformArray($artisan->getProductionModels()),
            129031545  => $this->transformArray($artisan->getStyles()),
            1324232796 => $artisan->getOtherStyles(),
            1319815626 => $this->transformArray($artisan->getOrderTypes()),
            67316802   => $artisan->getOtherOrderTypes(),
            1197078153 => $this->transformArray($artisan->getFeatures()),
            175794467  => $artisan->getOtherFeatures(),
            416913125  => $artisan->getSpeciesDoes(),
            1335617718 => $artisan->getSpeciesDoesnt(),
            1291118884 => $artisan->getFursuitReviewUrl(),
            1753739667 => $artisan->getWebsiteUrl(),
            110608078  => $artisan->getFaqUrl(),
            1781081038 => $artisan->getFurAffinityUrl(),
            591054015  => $artisan->getDeviantArtUrl(),
            151172280  => $artisan->getTwitterUrl(),
            1965677490 => $artisan->getFacebookUrl(),
            1209445762 => $artisan->getTumblrUrl(),
            696741203  => $artisan->getInstagramUrl(),
            618562986  => $artisan->getYoutubeUrl(),
            1737459766 => $artisan->getQueueUrl(),
            1355429885 => $artisan->getCstUrl(),
            350422540  => $artisan->getScritchesUrl(),
            2080821980 => $artisan->getScritchesPhotosUrls(),
            1507707399 => $artisan->getOtherUrls(),
            528156817  => $artisan->getLanguages(),
            927668258  => $artisan->getMakerId(),
            1671817601 => $artisan->getNotes(),
            725071599  => $artisan->getIntro(),
            1066294270 => $this->transformContactAllowed($artisan->getContactAllowed()),
            1142456974 => $artisan->getContactInfoObfuscated(),
            1898509469 => 'Yes, I\'m not on the list yet, or I used the update link',
        ]; // NOTE: Update tested fields as well!

        $urlParams = [];

        foreach ($input as $id => $value) {
            $urlParams[] = $this->toDataItem($id, $value);
        }

        return $this->iuFormUrl.'?usp=pp_url&'.implode('&', array_filter($urlParams));
    }

    private function transformContactAllowed(string $contactAllowed): string
    {
        switch ($contactAllowed) {
            case 'FEEDBACK':
                return 'ANNOUNCEMENTS + FEEDBACK';
            case 'ANNOUNCEMENTS':
                return 'ANNOUNCEMENTS *ONLY*';
            default:
                return 'NO (I may join Telegram)';
        }
    }

    private function transformSince(string $since): string
    {
        return $since.'-01';
    }

    private function toDataItem(int $id, $data): string
    {
        if (is_string($data)) {
            return '' === $data ? '' : "entry.$id=".urlencode($data);
        } else {
            return implode('&', array_map(function (string $dataItem) use ($id): string {
                return "entry.$id=".urlencode($dataItem);
            }, $data));
        }
    }

    /**
     * @param string $array
     *
     * @return string[]
     */
    private function transformArray(string $array): array
    {
        return explode("\n", $array);
    }
}
