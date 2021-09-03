<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtisanRepository")
 * @ORM\Table(name="artisans")
 */
class Artisan implements Stringable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private string $makerId = '';

    /**
     * @ORM\Column(type="string", length=128)
     */
    private string $name = '';

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $formerly = '';

    /**
     * @ORM\Column(type="string", length=512)
     */
    private string $intro = '';

    /**
     * @ORM\Column(type="string", length=16)
     */
    private string $since = '';

    /**
     * @ORM\Column(type="string", length=16)
     */
    private string $country = '';

    /**
     * @ORM\Column(type="string", length=32)
     */
    private string $state = '';

    /**
     * @ORM\Column(type="string", length=32)
     */
    private string $city = '';

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $productionModelsComment = '';

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $productionModels = '';

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $stylesComment = '';

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private string $styles = '';

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private string $otherStyles = '';

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $orderTypesComment = '';

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private string $orderTypes = '';

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private string $otherOrderTypes = '';

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $featuresComment = '';

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private string $features = '';

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private string $otherFeatures = '';

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $paymentPlans = '';

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $paymentMethods = '';

    /**
     * @ORM\Column(type="string", length=64)
     */
    private string $currenciesAccepted = '';

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $speciesComment = '';

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $speciesDoes = '';

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $speciesDoesnt = '';

    /**
     * @ORM\Column(type="string", length=256)
     */
    private string $languages = '';

    /**
     * @ORM\Column(type="text")
     */
    private string $notes = '';

    /**
     * @ORM\Column(type="string", length=512)
     */
    private string $inactiveReason = '';

    /**
     * @ORM\Column(type="string", length=16)
     */
    private string $contactAllowed = '';

    /**
     * @ORM\Column(type="string", length=32)
     */
    private string $contactMethod = '';

    /**
     * @ORM\Column(type="string", length=128)
     */
    private string $contactInfoObfuscated = '';

    /**
     * @ORM\OneToOne(targetEntity=ArtisanVolatileData::class, mappedBy="artisan", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private ?ArtisanVolatileData $volatileData = null;

    /**
     * @ORM\OneToOne(targetEntity=ArtisanPrivateData::class, mappedBy="artisan", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private ?ArtisanPrivateData $privateData = null;

    /**
     * @ORM\OneToMany(targetEntity=ArtisanUrl::class, mappedBy="artisan", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var Collection|ArtisanUrl[]
     */
    private Collection|array $urls;

    /**
     * @ORM\OneToMany(targetEntity=ArtisanCommissionsStatus::class, mappedBy="artisan", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var Collection|ArtisanCommissionsStatus[]
     */
    private Collection|array $commissions;

    /**
     * @ORM\OneToMany(targetEntity=MakerId::class, mappedBy="artisan", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var Collection|MakerId[]
     */
    private Collection|array $makerIds;

    public function __construct()
    {
        $this->urls = new ArrayCollection();
        $this->commissions = new ArrayCollection();
        $this->makerIds = new ArrayCollection();
    }

    public function __clone()
    {
        if ($this->privateData) {
            $this->setPrivateData(clone $this->privateData);
        }

        if ($this->volatileData) {
            $this->setVolatileData(clone $this->volatileData);
        }

        $urlsToClone = $this->urls;
        $this->urls = new ArrayCollection();

        foreach ($urlsToClone as $url) {
            $this->addUrl(clone $url);
        }

        $makerIdsToClone = $this->makerIds;
        $this->makerIds = new ArrayCollection();

        foreach ($makerIdsToClone as $makerId) {
            $this->addMakerId(clone $makerId);
        }

        $commissionsToClone = $this->commissions;
        $this->commissions = new ArrayCollection();

        foreach ($commissionsToClone as $commission) {
            $this->addCommission(clone $commission);
        }
    }

    public function __toString(): string
    {
        return self::class.":$this->id:$this->makerId";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMakerId(): string
    {
        return $this->makerId;
    }

    public function setMakerId(string $makerId): self
    {
        $this->makerId = $makerId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFormerly(): string
    {
        return $this->formerly;
    }

    public function setFormerly(string $formerly): self
    {
        $this->formerly = $formerly;

        return $this;
    }

    public function getIntro(): string
    {
        return $this->intro;
    }

    public function setIntro(string $intro): self
    {
        $this->intro = $intro;

        return $this;
    }

    public function getSince(): string
    {
        return $this->since;
    }

    public function setSince(string $since): self
    {
        $this->since = $since;

        return $this;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getProductionModelsComment(): string
    {
        return $this->productionModelsComment;
    }

    public function setProductionModelsComment(string $productionModelsComment): self
    {
        $this->productionModelsComment = $productionModelsComment;

        return $this;
    }

    public function getProductionModels(): string
    {
        return $this->productionModels;
    }

    public function setProductionModels(string $productionModels): self
    {
        $this->productionModels = $productionModels;

        return $this;
    }

    public function getStylesComment(): string
    {
        return $this->stylesComment;
    }

    public function setStylesComment(string $stylesComment): self
    {
        $this->stylesComment = $stylesComment;

        return $this;
    }

    public function getStyles(): string
    {
        return $this->styles;
    }

    public function setStyles(string $styles): self
    {
        $this->styles = $styles;

        return $this;
    }

    public function getOtherStyles(): string
    {
        return $this->otherStyles;
    }

    public function setOtherStyles(string $otherStyles): self
    {
        $this->otherStyles = $otherStyles;

        return $this;
    }

    public function getOrderTypesComment(): string
    {
        return $this->orderTypesComment;
    }

    public function setOrderTypesComment(string $orderTypesComment): self
    {
        $this->orderTypesComment = $orderTypesComment;

        return $this;
    }

    public function getOrderTypes(): string
    {
        return $this->orderTypes;
    }

    public function setOrderTypes(string $orderTypes): self
    {
        $this->orderTypes = $orderTypes;

        return $this;
    }

    public function getOtherOrderTypes(): string
    {
        return $this->otherOrderTypes;
    }

    public function setOtherOrderTypes(string $otherOrderTypes): self
    {
        $this->otherOrderTypes = $otherOrderTypes;

        return $this;
    }

    public function getFeaturesComment(): string
    {
        return $this->featuresComment;
    }

    public function setFeaturesComment(string $featuresComment): self
    {
        $this->featuresComment = $featuresComment;

        return $this;
    }

    public function getFeatures(): string
    {
        return $this->features;
    }

    public function setFeatures(string $features): self
    {
        $this->features = $features;

        return $this;
    }

    public function getOtherFeatures(): string
    {
        return $this->otherFeatures;
    }

    public function setOtherFeatures(string $otherFeatures): self
    {
        $this->otherFeatures = $otherFeatures;

        return $this;
    }

    public function getPaymentPlans(): string
    {
        return $this->paymentPlans;
    }

    public function setPaymentPlans(string $paymentPlans): self
    {
        $this->paymentPlans = $paymentPlans;

        return $this;
    }

    public function getPaymentMethods(): string
    {
        return $this->paymentMethods;
    }

    public function setPaymentMethods(string $paymentMethods): self
    {
        $this->paymentMethods = $paymentMethods;

        return $this;
    }

    public function getCurrenciesAccepted(): string
    {
        return $this->currenciesAccepted;
    }

    public function setCurrenciesAccepted(string $currenciesAccepted): self
    {
        $this->currenciesAccepted = $currenciesAccepted;

        return $this;
    }

    public function getSpeciesComment(): string
    {
        return $this->speciesComment;
    }

    public function setSpeciesComment(string $speciesComment): self
    {
        $this->speciesComment = $speciesComment;

        return $this;
    }

    public function getSpeciesDoes(): string
    {
        return $this->speciesDoes;
    }

    public function setSpeciesDoes(string $speciesDoes): self
    {
        $this->speciesDoes = $speciesDoes;

        return $this;
    }

    public function getSpeciesDoesnt(): string
    {
        return $this->speciesDoesnt;
    }

    public function setSpeciesDoesnt(string $speciesDoesnt): self
    {
        $this->speciesDoesnt = $speciesDoesnt;

        return $this;
    }

    public function getLanguages(): string
    {
        return $this->languages;
    }

    public function setLanguages(string $languages): self
    {
        $this->languages = $languages;

        return $this;
    }

    public function getNotes(): string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function getInactiveReason(): string
    {
        return $this->inactiveReason;
    }

    public function setInactiveReason(string $inactiveReason): self
    {
        $this->inactiveReason = $inactiveReason;

        return $this;
    }

    public function getContactAllowed(): string
    {
        return $this->contactAllowed;
    }

    public function setContactAllowed(string $contactAllowed): self
    {
        $this->contactAllowed = $contactAllowed;

        return $this;
    }

    public function getContactMethod(): string
    {
        return $this->contactMethod;
    }

    public function setContactMethod(string $contactMethod): self
    {
        $this->contactMethod = $contactMethod;

        return $this;
    }

    public function getContactInfoObfuscated(): string
    {
        return $this->contactInfoObfuscated;
    }

    public function setContactInfoObfuscated(string $contactInfoObfuscated): self
    {
        $this->contactInfoObfuscated = $contactInfoObfuscated;

        return $this;
    }

    public function getVolatileData(): ?ArtisanVolatileData
    {
        return $this->volatileData;
    }

    public function setVolatileData(?ArtisanVolatileData $volatileData): self
    {
        // unset the owning side of the relation if necessary
        if (null === $volatileData && null !== $this->volatileData) {
            $this->volatileData->setArtisan(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $volatileData && $volatileData->getArtisan() !== $this) {
            $volatileData->setArtisan($this);
        }

        $this->volatileData = $volatileData;

        return $this;
    }

    public function getPrivateData(): ?ArtisanPrivateData
    {
        return $this->privateData;
    }

    public function setPrivateData(?ArtisanPrivateData $privateData): self
    {
        // unset the owning side of the relation if necessary
        if (null === $privateData && null !== $this->privateData) {
            $this->privateData->setArtisan(null);
        }

        // set the owning side of the relation if necessary
        if (null !== $privateData && $privateData->getArtisan() !== $this) {
            $privateData->setArtisan($this);
        }

        $this->privateData = $privateData;

        return $this;
    }

    /**
     * @return Collection|ArtisanUrl[]
     */
    public function getUrls(): Collection|array
    {
        return $this->urls;
    }

    public function addUrl(ArtisanUrl $artisanUrl): self
    {
        if (!$this->urls->contains($artisanUrl)) {
            $this->urls[] = $artisanUrl;
            $artisanUrl->setArtisan($this);
        }

        return $this;
    }

    public function removeUrl(ArtisanUrl $artisanUrl): self
    {
        if ($this->urls->removeElement($artisanUrl)) {
            // set the owning side to null (unless already changed)
            if ($artisanUrl->getArtisan() === $this) {
                $artisanUrl->setArtisan(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ArtisanCommissionsStatus[]
     */
    public function getCommissions(): Collection|array
    {
        return $this->commissions;
    }

    public function addCommission(ArtisanCommissionsStatus $commission): self
    {
        if (!$this->commissions->contains($commission)) {
            $this->commissions[] = $commission;
            $commission->setArtisan($this);
        }

        return $this;
    }

    public function removeCommission(ArtisanCommissionsStatus $commission): self
    {
        if ($this->commissions->removeElement($commission)) {
            // set the owning side to null (unless already changed)
            if ($commission->getArtisan() === $this) {
                $commission->setArtisan(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MakerId[]
     */
    public function getMakerIds(): Collection|array
    {
        return $this->makerIds;
    }

    public function addMakerId(MakerId $makerId): self
    {
        if (!$this->makerIds->contains($makerId)) {
            $this->makerIds[] = $makerId;
            $makerId->setArtisan($this);
        }

        return $this;
    }

    public function removeMakerId(MakerId $makerId): self
    {
        if ($this->makerIds->removeElement($makerId)) {
            // set the owning side to null (unless already changed)
            if ($makerId->getArtisan() === $this) {
                $makerId->setArtisan(null);
            }
        }

        return $this;
    }
}
