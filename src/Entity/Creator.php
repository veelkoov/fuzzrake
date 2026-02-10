<?php

declare(strict_types=1);

namespace App\Entity;

use App\Data\Definitions\ContactPermit;
use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use LogicException;
use Override;
use Stringable;

#[ORM\Entity(repositoryClass: CreatorRepository::class)]
#[ORM\Table(name: 'creators')]
#[ORM\HasLifecycleCallbacks]
class Creator implements Stringable
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
    private string $creatorId = '';

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
    private string $stylesComment = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $orderTypesComment = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $featuresComment = '';

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
    private string $notes = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $inactiveReason = '';

    #[ORM\Column(type: Types::TEXT, nullable: true, enumType: ContactPermit::class)]
    private ?ContactPermit $contactAllowed = null;

    #[ORM\OneToOne(targetEntity: CreatorVolatileData::class, mappedBy: 'creator', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?CreatorVolatileData $volatileData = null;

    #[ORM\OneToOne(targetEntity: CreatorPrivateData::class, mappedBy: 'creator', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?CreatorPrivateData $privateData = null;

    #[ORM\OneToOne(targetEntity: User::class, mappedBy: 'creator', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?User $user = null;

    /**
     * @var Collection<int, CreatorUrl>
     */
    #[ORM\OneToMany(targetEntity: CreatorUrl::class, mappedBy: 'creator', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $urls;

    /**
     * @var Collection<int, CreatorOfferStatus>
     */
    #[ORM\OneToMany(targetEntity: CreatorOfferStatus::class, mappedBy: 'creator', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $offerStatuses;

    /**
     * @var Collection<int, CreatorId>
     */
    #[ORM\OneToMany(targetEntity: CreatorId::class, mappedBy: 'creator', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $creatorIds;

    /**
     * @var Collection<int, CreatorValue>
     */
    #[ORM\OneToMany(targetEntity: CreatorValue::class, mappedBy: 'creator', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $values;

    /**
     * @var Collection<int, CreatorSpecie>
     */
    #[ORM\OneToMany(targetEntity: CreatorSpecie::class, mappedBy: 'creator', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $species;

    public function __construct()
    {
        $this->urls = new ArrayCollection();
        $this->offerStatuses = new ArrayCollection();
        $this->creatorIds = new ArrayCollection();
        $this->values = new ArrayCollection();
        $this->species = new ArrayCollection();
    }

    public function __clone()
    {
        if (null !== $this->user) {
            $this->setUser(clone $this->user);
        }

        if (null !== $this->privateData) {
            $this->setPrivateData(clone $this->privateData);
        }

        if (null !== $this->volatileData) {
            $this->setVolatileData(clone $this->volatileData);
        }

        $urlsToClone = $this->urls;
        $this->urls = new ArrayCollection();

        foreach ($urlsToClone as $url) {
            $this->addUrl(clone $url);
        }

        $creatorIdsToClone = $this->creatorIds;
        $this->creatorIds = new ArrayCollection();

        foreach ($creatorIdsToClone as $creatorId) {
            $this->addCreatorId(clone $creatorId);
        }

        $offerStatusesToClone = $this->offerStatuses;
        $this->offerStatuses = new ArrayCollection();

        foreach ($offerStatusesToClone as $offerStatus) {
            $this->addOfferStatus(clone $offerStatus);
        }

        $valuesToClone = $this->values;
        $this->values = new ArrayCollection();

        foreach ($valuesToClone as $value) {
            $this->addValue(clone $value);
        }

        $speciesToClone = $this->species;
        $this->species = new ArrayCollection();

        foreach ($speciesToClone as $specie) {
            $this->addSpecie(clone $specie);
        }
    }

    #[Override]
    public function __toString(): string
    {
        return self::class.":$this->id:$this->creatorId";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatorId(): string
    {
        return $this->creatorId;
    }

    public function setCreatorId(string $creatorId): self
    {
        $this->creatorId = $creatorId;

        if ('' !== $creatorId) {
            $this->addCreatorId($creatorId);
        }

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

    public function getStylesComment(): string
    {
        return $this->stylesComment;
    }

    public function setStylesComment(string $stylesComment): self
    {
        $this->stylesComment = $stylesComment;

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

    public function getFeaturesComment(): string
    {
        return $this->featuresComment;
    }

    public function setFeaturesComment(string $featuresComment): self
    {
        $this->featuresComment = $featuresComment;

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

    public function getVolatileData(): ?CreatorVolatileData
    {
        return $this->volatileData;
    }

    public function setVolatileData(?CreatorVolatileData $volatileData): self
    {
        $volatileData?->setCreator($this);

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $user?->setCreator($this);

        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, CreatorUrl>
     */
    public function getUrls(): Collection
    {
        return $this->urls;
    }

    public function addUrl(CreatorUrl $creatorUrl): self
    {
        if (!$this->urls->contains($creatorUrl)) {
            $this->urls[] = $creatorUrl;
            $creatorUrl->setCreator($this);
        }

        return $this;
    }

    public function removeUrl(CreatorUrl $creatorUrl): self
    {
        $this->urls->removeElement($creatorUrl);

        return $this;
    }

    /**
     * @return Collection<int, CreatorOfferStatus>
     */
    public function getOfferStatuses(): Collection
    {
        return $this->offerStatuses;
    }

    public function addOfferStatus(CreatorOfferStatus $offerStatus): self
    {
        if (!$this->offerStatuses->contains($offerStatus)) {
            $this->offerStatuses[] = $offerStatus;
            $offerStatus->setCreator($this);
        }

        return $this;
    }

    public function removeOfferStatus(CreatorOfferStatus $offerStatus): self
    {
        $this->offerStatuses->removeElement($offerStatus);

        return $this;
    }

    /**
     * @return Collection<int, CreatorId>
     */
    public function getCreatorIds(): Collection
    {
        return $this->creatorIds;
    }

    public function addCreatorId(CreatorId|string $creatorId): self
    {
        if (!$creatorId instanceof CreatorId) {
            if ($this->hasCreatorId($creatorId)) {
                return $this;
            }

            $creatorId = new CreatorId($creatorId);
        }

        if (!$this->creatorIds->contains($creatorId)) {
            $this->creatorIds[] = $creatorId;
            $creatorId->setCreator($this);
        }

        return $this;
    }

    public function removeCreatorId(CreatorId $creatorId): self
    {
        $this->creatorIds->removeElement($creatorId);

        return $this;
    }

    /**
     * @return Collection<int, CreatorValue>
     */
    public function getValues(): Collection
    {
        return $this->values;
    }

    public function addValue(CreatorValue $value): self
    {
        if (!$this->values->contains($value)) {
            $this->values[] = $value;
            $value->setCreator($this);
        }

        return $this;
    }

    public function removeValue(CreatorValue $value): self
    {
        $this->values->removeElement($value);

        return $this;
    }

    /**
     * @return Collection<int, CreatorSpecie>
     */
    public function getSpecies(): Collection
    {
        return $this->species;
    }

    public function addSpecie(CreatorSpecie $specie): self
    {
        if (!$this->species->contains($specie)) {
            $this->species[] = $specie;
            $specie->setCreator($this);
        }

        return $this;
    }

    public function removeSpecie(CreatorSpecie $specie): self
    {
        $this->species->removeElement($specie);

        return $this;
    }

    /** @noinspection PhpUnusedParameterInspection */
    #[ORM\PreFlush]
    public function preFlush(PreFlushEventArgs $event): void
    {
        SmartAccessDecorator::wrap($this)->assureNsfwSafety();
    }

    //
    // ===== CREATOR ID HELPERS =====
    //

    public function getLastCreatorId(): string
    {
        return '' !== $this->creatorId
            ? $this->creatorId
            : array_first($this->getFormerCreatorIds()) ?? throw new LogicException('Creator does not have any creator ID');
    }

    /**
     * This does not guarantee any order, including current creator ID does not have to be the first one.
     *
     * @return list<string>
     */
    public function getAllCreatorIds(): array
    {
        return array_values($this->creatorIds
            ->map(static fn (CreatorId $entity): string => $entity->getCreatorId())->toArray());
    }

    /**
     * Assume that values come from setting the creator ID, which is validated independently.
     *
     * @return list<string>
     */
    public function getFormerCreatorIds(): array
    {
        return arr_filterl($this->getAllCreatorIds(), fn (string $creatorId) => $creatorId !== $this->creatorId);
    }

    public function hasCreatorId(string $creatorId): bool
    {
        return arr_contains($this->getAllCreatorIds(), $creatorId);
    }

    /**
     * @param list<string> $formerCreatorIdsToSet
     */
    public function setFormerCreatorIds(array $formerCreatorIdsToSet): self
    {
        $creatorIdsToKeep = [...$formerCreatorIdsToSet, $this->creatorId];

        foreach ($this->getCreatorIds() as $creatorId) {
            if (!arr_contains($creatorIdsToKeep, $creatorId->getCreatorId())) {
                $this->removeCreatorId($creatorId);
            }
        }

        foreach ($formerCreatorIdsToSet as $creatorId) {
            $this->addCreatorId($creatorId);
        }

        return $this;
    }
}
