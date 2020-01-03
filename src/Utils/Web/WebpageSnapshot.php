<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\Json;
use DateTime;
use DateTimeInterface;
use JsonException;
use JsonSerializable;

class WebpageSnapshot implements JsonSerializable
{
    const JSON_SERIALIZATION_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                                     | JSON_UNESCAPED_LINE_TERMINATORS | JSON_PRETTY_PRINT;

    private string $url;
    private string $contents;
    private DateTime $retrievedAt;
    private string $ownerName;

    /**
     * @var WebpageSnapshot[]
     */
    private array $children = [];

    public function __construct(string $url, string $contents, DateTime $retrievedAt, string $ownerName)
    {
        $this->url = $url;
        $this->contents = $contents;
        $this->retrievedAt = $retrievedAt;
        $this->ownerName = $ownerName;
    }

    /**
     * @throws JsonException
     */
    public static function fromJson(string $json): WebpageSnapshot
    {
        return self::fromArray(Json::decode($json));
    }

    /**
     * @throws JsonException
     */
    public function toJson(): string
    {
        return Json::encode($this, self::JSON_SERIALIZATION_OPTIONS);
    }

    public function addChildren(WebpageSnapshot $children): void
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
            'url'         => $this->url,
            'ownerName'   => $this->ownerName,
            'retrievedAt' => $this->retrievedAt->format(DateTimeInterface::ISO8601),
            'contents'    => $this->contents,
            'children'    => $this->children,
        ];
    }

    private static function fromArray(array $input): WebpageSnapshot
    {
        $result = new self($input['url'], $input['contents'], DateTime::createFromFormat(DateTimeInterface::ISO8601,
            $input['retrievedAt']), $input['ownerName']);
        $result->setChildren(array_map([WebpageSnapshot::class, 'fromArray'], $input['children']));

        return $result;
    }
}
