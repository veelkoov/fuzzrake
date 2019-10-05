<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtisanUrlRepository")
 * @ORM\Table(name="artisans_urls")
 */
class ArtisanUrl
{
    public const TYPE_FURSUIT_REVIEW = 'FURSUIT_REVIEW';
    public const TYPE_WEBSITE = 'WEBSITE';
    public const TYPE_PRICES = 'PRICES';
    public const TYPE_FAQ = 'FAQ';
    public const TYPE_FUR_AFFINITY = 'FUR_AFFINITY';
    public const TYPE_DEVIANTART = 'DEVIANTART';
    public const TYPE_TWITTER = 'TWITTER';
    public const TYPE_FACEBOOK = 'FACEBOOK';
    public const TYPE_TUMBLR = 'TUMBLR';
    public const TYPE_INSTAGRAM = 'INSTAGRAM';
    public const TYPE_YOUTUBE = 'YOUTUBE';
    public const TYPE_QUEUE = 'QUEUE';
    public const TYPE_SCRITCH = 'SCRITCH';
    public const TYPE_SCRITCH_PHOTO = 'SCRITCH_PHOTO';
    public const TYPE_OTHER = 'OTHER';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Artisan", inversedBy="artisanUrls")
     * @ORM\JoinColumn(name="artisan_id", nullable=false)
     */
    private $artisan;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=1023)
     */
    private $url;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArtisan(): ?Artisan
    {
        return $this->artisan;
    }

    public function setArtisan(?Artisan $artisan): self
    {
        $this->artisan = $artisan;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
}
