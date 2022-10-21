<?php

declare(strict_types=1);

namespace App\Tasks\Miniatures\Queries;

use App\Tracking\Web\HttpClient\GentleHttpClient;
use App\Utils\ArrayReader;
use App\Utils\Json;
use LogicException;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ScritchQuery extends AbstractQuery
{
    private readonly CookieJar $cookieJar;
    private ?string $csrfToken = null;

    public function __construct(
        GentleHttpClient $httpClient,
    ) {
        parent::__construct($httpClient);

        $this->cookieJar = new CookieJar();
    }

    public function getMiniatureUrl(string $photoUrl): string
    {
        $csrfToken = $this->getOrRetrieveCsrfToken();
        $pictureId = $this->getPictureId($photoUrl);
        $jsonPayload = $this->getGraphQlJsonPayload($pictureId);

        $response = $this->httpClient->post('https://scritch.es/graphql', $jsonPayload, $this->cookieJar, [
            'Content-Type'  => 'application/json',
            'X-CSRF-Token'  => $csrfToken,
            'authorization' => "Scritcher $csrfToken",
        ]);

        $this->updateCookies($response);

        $postData = Json::decode($response->getContent(true));
        $accessor = new ArrayReader($postData);

        return $accessor->getNonEmptyString('[data][medium][thumbnail]');
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
    private function getOrRetrieveCsrfToken(): string
    {
        return $this->csrfToken ??= $this->getCsrfToken();
    }

    /**
     * @throws ExceptionInterface
     */
    private function getCsrfToken(): string
    {
        $response = $this->httpClient->get('https://scritch.es/', $this->cookieJar);

        $this->updateCookies($response);

        $csrfTokenCookie = $this->cookieJar->get('csrf-token');

        if (null === $csrfTokenCookie) {
            throw new LogicException('Missing csrf-token cookie');
        }

        return $csrfTokenCookie->getValue();
    }
}
