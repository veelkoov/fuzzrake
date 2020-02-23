<?php

declare(strict_types=1);

namespace App\Utils\Web\Snapshot;

use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

class WebpageSnapshot
{
    /**
     * @var WebpageSnapshot[]
     */
    private array $children = [];

    /**
     * @var string[]
     */
    private array $headers = [];
    private string $url;
    private string $contents;
    private DateTime $retrievedAt;
    private string $ownerName;
    private int $httpCode = 0;

    public function __construct(string $url, string $contents, DateTime $retrievedAt, string $ownerName, int $httpCode,
        array $headers)
    {
        $this->url = $url;
        $this->contents = $contents;
        $this->retrievedAt = $retrievedAt;
        $this->ownerName = $ownerName;
        $this->httpCode = $httpCode;
        $this->headers = $headers;
    }

    public function addChild(WebpageSnapshot $children): void
    {
        $this->children[] = $children;
    }

    /**
     * @param WebpageSnapshot[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * @return WebpageSnapshot[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getOwnerName(): string
    {
        return $this->ownerName;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getRetrievedAt(): DateTime
    {
        return $this->retrievedAt;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function isOK(): bool
    {
        return 200 === $this->httpCode;
    }

    /**
     * @return string[]
     */
    public function getAllContents(): array
    {
        return array_merge([$this->contents], ...array_map(function (WebpageSnapshot $snapshot) { return $snapshot->getAllContents(); }, $this->getChildren()));
    }

    public function getMetadata(): array
    {
        return [
            'url'         => $this->url,
            'ownerName'   => $this->ownerName,
            'retrievedAt' => $this->retrievedAt->format(DateTimeInterface::ISO8601),
            'childCount'  => count($this->children),
            'headers'     => $this->headers,
            'httpCode'    => $this->httpCode,
        ];
    }

    /**
     * @throws DateTimeException
     */
    public static function fromArray(array $input): WebpageSnapshot
    {
        if (!is_string($input['contents'])) {
            throw new InvalidArgumentException('Contents is not a string');
        }

        return new self($input['url'], $input['contents'], DateTimeUtils::getUtcAt($input['retrievedAt']),
            $input['ownerName'], $input['httpCode'] ?? 0, $input['headers'] ?? []);
    }
}
