<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtisanRepository")
 * @ORM\Table(name="artisans")
 */
class Artisan implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fursuitReviewUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $furAffinityUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $deviantArtUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $websiteUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facebookUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $twitterUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tumblrUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $commisionsQuotesCheckUrl;

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getFursuitReviewUrl(): ?string
    {
        return $this->fursuitReviewUrl;
    }

    public function setFursuitReviewUrl(?string $fursuitReviewUrl): self
    {
        $this->fursuitReviewUrl = $fursuitReviewUrl;

        return $this;
    }

    public function getFurAffinityUrl(): ?string
    {
        return $this->furAffinityUrl;
    }

    public function setFurAffinityUrl(?string $furAffinityUrl): self
    {
        $this->furAffinityUrl = $furAffinityUrl;

        return $this;
    }

    public function getDeviantArtUrl(): ?string
    {
        return $this->deviantArtUrl;
    }

    public function setDeviantArtUrl(?string $deviantArtUrl): self
    {
        $this->deviantArtUrl = $deviantArtUrl;

        return $this;
    }

    public function getWebsiteUrl(): ?string
    {
        return $this->websiteUrl;
    }

    public function setWebsiteUrl(?string $websiteUrl): self
    {
        $this->websiteUrl = $websiteUrl;

        return $this;
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl): self
    {
        $this->facebookUrl = $facebookUrl;

        return $this;
    }

    public function getTwitterUrl(): ?string
    {
        return $this->twitterUrl;
    }

    public function setTwitterUrl(?string $twitterUrl): self
    {
        $this->twitterUrl = $twitterUrl;

        return $this;
    }

    public function getTumblrUrl(): ?string
    {
        return $this->tumblrUrl;
    }

    public function setTumblrUrl(?string $tumblrUrl): self
    {
        $this->tumblrUrl = $tumblrUrl;

        return $this;
    }

    public function getCommisionsQuotesCheckUrl(): ?string
    {
        return $this->commisionsQuotesCheckUrl;
    }

    public function setCommisionsQuotesCheckUrl(?string $commisionsQuotesCheckUrl): self
    {
        $this->commisionsQuotesCheckUrl = $commisionsQuotesCheckUrl;

        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $f = ['name', 'country', 'furAffinityUrl', 'deviantArtUrl', 'websiteUrl', 'facebookUrl', 'twitterUrl', 'tumblrUrl'];

        return array_map(function ($item) {
            return $this->$item;
        }, array_combine($f, $f));
    }
}
