<?php

declare(strict_types=1);

namespace App\Utils\Web;

use DateTime;
use DateTimeInterface;
use JsonSerializable;

class WebpageSnapshot implements JsonSerializable
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $contents;

    /**
     * @var DateTime
     */
    private $retrievedAt;

    /**
     * @var WebpageSnapshot[]
     */
    private $children = [];

    /**
     * @param string   $url
     * @param string   $contents
     * @param DateTime $retrievedAt
     */
    public function __construct(string $url, string $contents, DateTime $retrievedAt)
    {
        $this->url = $url;
        $this->contents = $contents;
        $this->retrievedAt = $retrievedAt;
    }

    public static function fromArray(array $input): WebpageSnapshot
    {
        $result = new self($input['url'], $input['contents'], DateTime::createFromFormat(DateTimeInterface::ISO8601, $input['retrievedAt']));
        $result->setChildren(array_map([WebpageSnapshot::class, 'fromArray'], $input['children']));

        return $result;
    }

    public function addChildren(WebpageSnapshot $children): void
    {
        $this->children[] = $children;
    }

    /**
     * @param WebpageSnapshot[] $children
     */
    public function setChildren(array $children)
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

    public function getContents(): string
    {
        return $this->contents;
    }

    public function getRetrievedAt(): DateTime
    {
        return $this->retrievedAt;
    }

    /**
     * @return string[]
     */
    public function getAllContents(): array
    {
        return array_merge([$this->contents], ...array_map(function (WebpageSnapshot $snapshot) { return $snapshot->getAllContents(); }, $this->getChildren()));
    }

    public function jsonSerialize(): array
    {
        return [
            'url' => $this->url,
            'retrievedAt' => $this->retrievedAt->format(DateTimeInterface::ISO8601),
            'contents' => $this->contents,
            'children' => $this->children,
        ];
    }
}
