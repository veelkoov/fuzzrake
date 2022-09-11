<?php

declare(strict_types=1);

namespace App\Tracking\Web\WebpageSnapshot;

use DateTimeImmutable;

class Snapshot
{
    /**
     * @var Snapshot[]
     */
    private array $children = [];

    /**
     * @param string[][] $headers
     * @param string[]   $errors
     */
    public function __construct(
        public readonly string $contents,
        public readonly string $url,
        public readonly DateTimeImmutable $retrievedAt,
        public readonly string $ownerName,
        public readonly int $httpCode,
        public readonly array $headers,
        public readonly array $errors,
    ) {
    }

    public function addChild(Snapshot $children): void
    {
        $this->children[] = $children;
    }

    /**
     * @param Snapshot[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /**
     * @return Snapshot[]
     */
    public function getChildren(): array
    {
        return $this->children;
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
        return array_merge([$this->contents], ...array_map(fn (Snapshot $snapshot) => $snapshot->getAllContents(), $this->getChildren()));
    }

    public static function restore(string $contents, Metadata $metadata): Snapshot
    {
        return new self(
            $contents,
            $metadata->url,
            $metadata->retrievedAt,
            $metadata->ownerName,
            $metadata->httpCode,
            $metadata->headers,
            $metadata->errors,
        );
    }
}
