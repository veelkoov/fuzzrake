<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Utils\Json;
use App\Utils\JsonException;
use DateTime;
use DateTimeInterface;
use JsonSerializable;

class WebpageSnapshot implements JsonSerializable
{
    const JSON_SERIALIZATION_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                                     | JSON_UNESCAPED_LINE_TERMINATORS | JSON_PRETTY_PRINT;

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
     * @var string
     */
    private $ownerName;

    /**
     * @var WebpageSnapshot[]
     */
    private $children = [];

    /**
     * @param string   $url
     * @param string   $contents
     * @param DateTime $retrievedAt
     * @param string   $ownerName
     */
    public function __construct(string $url, string $contents, DateTime $retrievedAt, string $ownerName)
    {
        $this->url = $url;
        $this->contents = $contents;
        $this->retrievedAt = $retrievedAt;
        $this->ownerName = $ownerName;
    }

    /**
     * @param string $json
     *
     * @return WebpageSnapshot
     *
     * @throws JsonException
     */
    public static function fromJson(string $json)
    {
        return self::fromArray(Json::decode($json));
    }

    /**
     * @return string
     *
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
