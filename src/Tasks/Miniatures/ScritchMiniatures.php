<?php

declare(strict_types=1);

namespace App\Tasks\Miniatures;

use App\Utils\Json;
use App\Utils\Web\HttpClient\GentleHttpClient;
use LogicException;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ScritchMiniatures extends AbstractMiniatures
{
    private CookieJar $cookieJar;
    private ?string $csrfToken = null;

    public function __construct(
        GentleHttpClient $httpClient,
    ) {
        parent::__construct($httpClient);

        $this->cookieJar = new CookieJar();
    }

    public function getMiniatureUrl(string $photoUrl): string
    {
        $csrfToken = $this->getCsrfToken();
        $pictureId = $this->getPictureId($photoUrl);
        $jsonPayload = $this->getGraphQlJsonPayload($pictureId);

        $response = $this->httpClient->post('https://scritch.es/graphql', $jsonPayload, $this->cookieJar, [
            'Content-Type'  => 'application/json',
            'X-CSRF-Token'  => $csrfToken,
            'authorization' => "Scritcher $csrfToken",
        ]);

        $this->updateCookies($response);

        $thumbnailUrl = Json::decode($response->getContent(true))['data']['medium']['thumbnail'] ?? '';

        if ('' === $thumbnailUrl) {
            throw new LogicException('No thumbnail URL found in response');
        }

        return $thumbnailUrl;
    }

    protected function getRegexp(): string
    {
        return '^https://scritch\.es/pictures/(?<picture_id>[-a-f0-9]{36})$';
    }

    private function getGraphQlJsonPayload(string $pictureId): string
    {
        return '{"operationName": "Medium", "variables": {"id": "'.$pictureId.'"}, "query": "query Medium($id: ID!, $tagging: Boolean) { medium(id: $id, tagging: $tagging) { thumbnail } }"}';
    }

    /**
     * @throws ExceptionInterface
     */
    private function updateCookies(ResponseInterface $response): void
    {
        $this->cookieJar->updateFromSetCookie($response->getHeaders(true)['set-cookie'] ?? []);
    }

    /**
     * @throws ExceptionInterface
     */
    private function getCsrfToken(): string
    {
        if (null === $this->csrfToken) {
            $response = $this->httpClient->get('https://scritch.es/', $this->cookieJar);
            $this->updateCookies($response);
            $this->csrfToken = $this->cookieJar->get('csrf-token')->getValue();
        }

        return $this->csrfToken;
    }
}
