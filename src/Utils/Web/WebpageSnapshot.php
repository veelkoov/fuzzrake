<?php

declare(strict_types=1);

namespace App\Utils\Web;

use DateTime;

class WebpageSnapshot
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
    private $datetimeRetrieved;

    /**
     * @param string   $url
     * @param string   $contents
     * @param DateTime $datetimeRetrieved
     */
    public function __construct(string $url, string $contents, DateTime $datetimeRetrieved)
    {
        $this->url = $url;
        $this->contents = $contents;
        $this->datetimeRetrieved = $datetimeRetrieved;
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
    public function getDatetimeRetrieved(): DateTime
    {
        return $this->datetimeRetrieved;
    }
}
