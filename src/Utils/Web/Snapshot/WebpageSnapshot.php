<?php

declare(strict_types=1);

namespace App\Utils\Web\Snapshot;

use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use DateTimeImmutable;
use InvalidArgumentException;

class WebpageSnapshot
{
    /**
     * @var WebpageSnapshot[]
     */
    private array $children = [];

    /**
     * @param string[][] $headers
     * @param string[]   $errors
     */
    public function __construct(
        private readonly string $url,
        private readonly string $contents,
        private readonly DateTimeImmutable $retrievedAt,
        private readonly string $ownerName,
        private readonly int $httpCode,
        private readonly array $headers,
        private readonly array $errors,
    ) {
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

    public function getRetrievedAt(): DateTimeImmutable
    {
        return $this->retrievedAt;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     * @return string[][]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
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
        return array_merge([$this->contents], ...array_map(fn (WebpageSnapshot $snapshot) => $snapshot->getAllContents(), $this->getChildren()));
    }

    public function getMetadata(): array
    {
        return [
            'url'         => $this->url,
            'ownerName'   => $this->ownerName,
            'retrievedAt' => $this->retrievedAt->format(DATE_ATOM),
            'childCount'  => count($this->children),
            'headers'     => $this->headers,
            'errors'      => $this->errors,
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

        return new self(
            $input['url'],
            $input['contents'],
            UtcClock::at($input['retrievedAt']),
            $input['ownerName'],
            $input['httpCode'] ?? 0,
            $input['headers'] ?? [],
            $input['errors'] ?? [],
        );
    }
}
