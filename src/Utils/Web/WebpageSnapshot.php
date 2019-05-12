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

    public static function fromFile(string $snapshotPath): WebpageSnapshot
    {
        $input = json_decode(file_get_contents($snapshotPath), true, 512, JSON_THROW_ON_ERROR);

        return new self($input['url'], $input['contents'], DateTime::createFromFormat(DateTimeInterface::ISO8601, $input['retrievedAt']));
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getContents(): string
    {
        return $this->contents;
    }

    /**
     * @return DateTime
     */
    public function getRetrievedAt(): DateTime
    {
        return $this->retrievedAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'url' => $this->url,
            'retrievedAt' => $this->retrievedAt->format(DateTimeInterface::ISO8601),
            'contents' => $this->contents,
        ];
    }
}
