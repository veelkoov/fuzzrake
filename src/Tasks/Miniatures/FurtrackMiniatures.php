<?php

declare(strict_types=1);

namespace App\Tasks\Miniatures;

use App\Utils\Json;
use App\Utils\Web\HttpClient\GentleHttpClient;
use LogicException;

class FurtrackMiniatures extends AbstractMiniatures
{
    public function __construct(
        GentleHttpClient $httpClient,
    ) {
        parent::__construct($httpClient);
    }

    public function getMiniatureUrl(string $photoUrl): string
    {
        $pictureId = $this->getPictureId($photoUrl);

        $response = $this->httpClient->get("https://ultra.furtrack.com/view/post/$pictureId");

        $postData = Json::decode($response->getContent(true));

        $postStub = $postData['post']['postStub'] ?? '';
        $metaFiletype = $postData['post']['metaFiletype'] ?? '';

        if ('' === $postStub || '' === $metaFiletype) {
            throw new LogicException('No post stub or meta filetype found in response');
        }

        return "https://orca.furtrack.com/gallery/sample/$postStub.$metaFiletype";
    }

    protected function getRegexp(): string
    {
        return '^https://www.furtrack.com/p/(?<picture_id>\d+)$';
    }
}
