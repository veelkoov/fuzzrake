<?php

declare(strict_types=1);

namespace App\Utils\Web;

use App\Entity\Artisan;

class Url
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var Artisan
     */
    private $artisan;

    /**
     * @var bool
     */
    private $isDependency;

    public function __construct(string $url, Artisan $artisan, bool $isDependency = false)
    {
        $this->url = $url;
        $this->artisan = $artisan;
        $this->isDependency = $isDependency;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getArtisan(): Artisan
    {
        return $this->artisan;
    }

    public function isDependency(): bool
    {
        return $this->isDependency;
    }
}
