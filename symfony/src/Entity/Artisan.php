<?php

declare(strict_types=1);

namespace App\Entity;

use App\Data\Definitions\ContactPermit;
use App\Repository\ArtisanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Stringable;

#[ORM\Entity(repositoryClass: ArtisanRepository::class)]
#[ORM\Table(name: 'artisans')]
class Artisan implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /*
     * We are using SQLite, so VARCHAR is TEXT, and there are no limits. Let's not make life more difficult,
     * and use TEXT everywhere. https://www.sqlite.org/datatype3.html
     */
    #[ORM\Column(type: Types::TEXT)]
    private string $makerId = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $name = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $formerly = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $intro = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $since = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $country = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $state = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $city = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $productionModelsComment = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $productionModels = ''; // TODO: Replaced with values use. Remove.

    #[ORM\Column(type: Types::TEXT)]
    private string $stylesComment = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $styles = ''; // TODO: Replaced with values use. Remove.

    #[ORM\Column(type: Types::TEXT)]
    private string $otherStyles = ''; // TODO: Replaced with values use. Remove.

    #[ORM\Column(type: Types::TEXT)]
    private string $orderTypesComment = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $orderTypes = ''; // TODO: Replaced with values use. Remove.

    #[ORM\Column(type: Types::TEXT)]
    private string $otherOrderTypes = ''; // TODO: Replaced with values use. Remove.

    #[ORM\Column(type: Types::TEXT)]
    private string $featuresComment = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $features = ''; // TODO: Replaced with values use. Remove.

    #[ORM\Column(type: Types::TEXT)]
    private string $otherFeatures = ''; // TODO: Replaced with values use. Remove.

    #[ORM\Column(type: Types::TEXT)]
    private string $paymentPlans = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $paymentMethods = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $currenciesAccepted = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $speciesComment = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $speciesDoes = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $speciesDoesnt = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $languages = ''; // TODO: Replaced with values use. Remove.

    #[ORM\Column(type: Types::TEXT)]
    private string $notes = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $inactiveReason = '';

    #[ORM\Column(type: Types::TEXT, nullable: true, enumType: ContactPermit::class)]
    private ?ContactPermit $contactAllowed = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $contactMethod = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $contactInfoObfuscated = '';

    #[ORM\OneToOne(mappedBy: 'artisan', targetEntity: ArtisanVolatileData::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?ArtisanVolatileData $volatileData = null;

    #[ORM\OneToOne(mappedBy: 'creator', targetEntity: CreatorPrivateData::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?CreatorPrivateData $privateData = null;

    /**
     * @var Collection<int, ArtisanUrl>
     */
    #[ORM\OneToMany(mappedBy: 'artisan', targetEntity: ArtisanUrl::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $urls;

    /**
     * @var Collection<int, CreatorOfferStatus>
     */
    #[ORM\OneToMany(mappedBy: 'artisan', targetEntity: CreatorOfferStatus::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $commissions;

    /**
     * @var Collection<int, MakerId>
     */
    #[ORM\OneToMany(mappedBy: 'artisan', targetEntity: MakerId::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $makerIds;

    /**
     * @var Collection<int, ArtisanValue>
     */
    #[ORM\OneToMany(mappedBy: 'artisan', targetEntity: ArtisanValue::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $values;

    public function __construct()
    {
        $this->urls = new ArrayCollection();
        $this->commissions = new ArrayCollection();
        $this->makerIds = new ArrayCollection();
        $this->values = new ArrayCollection();
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

        $valuesToClone = $this->values;
        $this->values = new ArrayCollection();

        foreach ($valuesToClone as $value) {
            $this->addValue(clone $value);
        }
    }

    #[Override]
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

    public function clearLegacyFields(): void // TODO: Temporary. Remove. https://github.com/veelkoov/fuzzrake/issues/249
    {
        $this->features = '';
        $this->languages = '';
        $this->orderTypes = '';
        $this->otherFeatures = '';
        $this->otherOrderTypes = '';
        $this->otherStyles = '';
        $this->productionModels = '';
        $this->styles = '';
    }

    public function getLegacyProductionModels(): string // TODO: Temporary. Remove. https://github.com/veelkoov/fuzzrake/issues/249
    {
        return $this->productionModels;
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

    public function getLegacyStyles(): string // TODO: Temporary. Remove. https://github.com/veelkoov/fuzzrake/issues/249
    {
        return $this->styles;
    }

    public function getLegacyOtherStyles(): string // TODO: Temporary. Remove. https://github.com/veelkoov/fuzzrake/issues/249
    {
        return $this->otherStyles;
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

    public function getLegacyOrderTypes(): string // TODO: Temporary. Remove. https://github.com/veelkoov/fuzzrake/issues/249
    {
        return $this->orderTypes;
    }

    public function getLegacyOtherOrderTypes(): string // TODO: Temporary. Remove. https://github.com/veelkoov/fuzzrake/issues/249
    {
        return $this->otherOrderTypes;
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

    public function getLegacyFeatures(): string // TODO: Temporary. Remove. https://github.com/veelkoov/fuzzrake/issues/249
    {
        return $this->features;
    }

    public function getLegacyOtherFeatures(): string // TODO: Temporary. Remove. https://github.com/veelkoov/fuzzrake/issues/249
    {
        return $this->otherFeatures;
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

    public function getLegacyLanguages(): string // TODO: Temporary. Remove. https://github.com/veelkoov/fuzzrake/issues/249
    {
        return $this->languages;
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

    public function getContactAllowed(): ?ContactPermit
    {
        return $this->contactAllowed;
    }

    public function setContactAllowed(?ContactPermit $contactAllowed): self
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
        $volatileData?->setArtisan($this);

        $this->volatileData = $volatileData;

        return $this;
    }

    public function getPrivateData(): ?CreatorPrivateData
    {
        return $this->privateData;
    }

    public function setPrivateData(?CreatorPrivateData $privateData): self
    {
        $privateData?->setCreator($this);

        $this->privateData = $privateData;

        return $this;
    }

    /**
     * @return Collection<int, ArtisanUrl>
     */
    public function getUrls(): Collection
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
        $this->urls->removeElement($artisanUrl);

        return $this;
    }

    /**
     * @return Collection<int, CreatorOfferStatus>
     */
    public function getCommissions(): Collection
    {
        return $this->commissions;
    }

    public function addCommission(CreatorOfferStatus $commission): self
    {
        if (!$this->commissions->contains($commission)) {
            $this->commissions[] = $commission;
            $commission->setArtisan($this);
        }

        return $this;
    }

    public function removeCommission(CreatorOfferStatus $commission): self
    {
        $this->commissions->removeElement($commission);

        return $this;
    }

    /**
     * @return Collection<int, MakerId>
     */
    public function getMakerIds(): Collection
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
        $this->makerIds->removeElement($makerId);

        return $this;
    }

    /**
     * @return Collection<int, ArtisanValue>
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    public function addValue(ArtisanValue $value): self
    {
        if (!$this->values->contains($value)) {
            $this->values[] = $value;
            $value->setArtisan($this);
        }

        return $this;
    }

    public function removeValue(ArtisanValue $value): self
    {
        $this->values->removeElement($value);

        return $this;
    }
}
