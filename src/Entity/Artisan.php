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
     * @ORM\Column(type="string", length=256)
     */
    private $since;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $country;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=1023)
     */
    private $styles;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $otherStyles;

    /**
     * @ORM\Column(type="string", length=1023)
     */
    private $types;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $otherTypes;

    /**
     * @ORM\Column(type="string", length=1023)
     */
    private $features;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $otherFeatures;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fursuitReviewUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $websiteUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $furAffinityUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $deviantArtUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $twitterUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $facebookUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tumblrUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $instagramUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $youtubeUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $commisionsQuotesCheckUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $queueUrl;

    /**
     * @ORM\Column(type="text")
     */
    private $notes;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $areCommissionsOpen;

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
        $f = ['name', 'types', 'country', 'furAffinityUrl', 'deviantArtUrl', 'websiteUrl', 'facebookUrl', 'twitterUrl',
            'tumblrUrl', 'commisionsQuotesCheckUrl', 'queueUrl', 'features', 'notes'];

        return array_map(function ($item) {
            return $this->$item;
        }, array_combine($f, $f));
    }

    public function getTypes(): ?string
    {
        return $this->types;
    }

    public function setTypes(string $types): self
    {
        $this->types = $types;

        return $this;
    }

    public function getQueueUrl(): ?string
    {
        return $this->queueUrl;
    }

    public function setQueueUrl(string $queueUrl): self
    {
        $this->queueUrl = $queueUrl;

        return $this;
    }

    public function getFeatures(): ?string
    {
        return $this->features;
    }

    public function setFeatures(string $features): self
    {
        $this->features = $features;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getInstagramUrl(): ?string
    {
        return $this->instagramUrl;
    }

    public function setInstagramUrl(string $instagramUrl): self
    {
        $this->instagramUrl = $instagramUrl;

        return $this;
    }

    public function getAreCommissionsOpen(): ?bool
    {
        return $this->areCommissionsOpen;
    }

    public function setAreCommissionsOpen(?bool $areCommissionsOpen): self
    {
        $this->areCommissionsOpen = $areCommissionsOpen;

        return $this;
    }

    public function getSince(): ?string
    {
        return $this->since;
    }

    public function setSince(string $since): self
    {
        $this->since = $since;

        return $this;
    }

    public function getYoutubeUrl(): ?string
    {
        return $this->youtubeUrl;
    }

    public function setYoutubeUrl(string $youtubeUrl): self
    {
        $this->youtubeUrl = $youtubeUrl;

        return $this;
    }

    public function getStyles(): ?string
    {
        return $this->styles;
    }

    public function setStyles(string $styles): self
    {
        $this->styles = $styles;

        return $this;
    }

    public function getOtherStyles(): ?string
    {
        return $this->otherStyles;
    }

    public function setOtherStyles(string $otherStyles): self
    {
        $this->otherStyles = $otherStyles;

        return $this;
    }

    public function getOtherTypes(): ?string
    {
        return $this->otherTypes;
    }

    public function setOtherTypes(string $otherTypes): self
    {
        $this->otherTypes = $otherTypes;

        return $this;
    }

    public function getOtherFeatures(): ?string
    {
        return $this->otherFeatures;
    }

    public function setOtherFeatures(string $otherFeatures): self
    {
        $this->otherFeatures = $otherFeatures;

        return $this;
    }
}
