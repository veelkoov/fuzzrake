<?php

namespace App\Entity;

use App\Utils\ArtisanFields;
use App\Utils\CompletenessCalc;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtisanRepository")
 * @ORM\Table(name="artisans")
 */
class Artisan implements JsonSerializable
{
    const OPEN = true;
    const CLOSED = false;
    const UNKNOWN = null;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=31)
     */
    private $makerId = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $formerMakerIds = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $formerly = '';

    /**
     * @ORM\Column(type="string", length=511)
     */
    private $intro;

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
     * @ORM\Column(type="string", length=255)
     */
    private $productionModels;

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
    private $orderTypes;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $otherOrderTypes;

    /**
     * @ORM\Column(type="string", length=1023)
     */
    private $features;

    /**
     * @ORM\Column(type="string", length=1023)
     */
    private $otherFeatures;

    /**
     * @ORM\Column(type="string", length=1023)
     */
    private $paymentPlans;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $speciesDoes;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $speciesDoesnt;

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
    private $pricesUrl;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $faqUrl;

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
    private $queueUrl;

    /**
     * @ORM\Column(type="string", length=1023)
     */
    private $otherUrls;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $languages;

    /**
     * @ORM\Column(type="text")
     */
    private $notes;

    /**
     * @ORM\Column(name="cst_url", type="string", length=255)
     */
    private $cstUrl;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $areCommissionsOpen; // TODO: remove

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $commissionsQuotesLastCheck; // TODO: remove

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $contactAllowed;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $contactMethod;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $contactAddress;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ArtisanCommissionsStatus", mappedBy="artisan", cascade={"persist", "remove"})
     */
    private $commissionsStatus;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ArtisanPrivateData", mappedBy="artisan", cascade={"persist", "remove"})
     */
    private $privateData;

    public function getId()
    {
        return $this->id;
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

    public function getFormerlyArr(): array
    {
        return explode("\n", $this->formerly);
    }

    public function getAllNamesArr(): array
    {
        return array_filter(array_merge([$this->getName()], $this->getFormerlyArr()));
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

    public function getCstUrl(): ?string
    {
        return $this->cstUrl;
    }

    public function setCstUrl(?string $cstUrl): self
    {
        $this->cstUrl = $cstUrl;

        return $this;
    }

    public function getOrderTypes(): ?string
    {
        return $this->orderTypes;
    }

    public function setOrderTypes(string $orderTypes): self
    {
        $this->orderTypes = $orderTypes;

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

    public function getOtherOrderTypes(): ?string
    {
        return $this->otherOrderTypes;
    }

    public function setOtherOrderTypes(string $otherOrderTypes): self
    {
        $this->otherOrderTypes = $otherOrderTypes;

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

    public function getCommissionsQuotesLastCheck(): ?DateTimeInterface
    {
        return $this->commissionsQuotesLastCheck;
    }

    public function setCommissionsQuotesLastCheck(?DateTimeInterface $commissionsQuotesLastCheck): self
    {
        $this->commissionsQuotesLastCheck = $commissionsQuotesLastCheck;

        return $this;
    }

    public function getIntro(): ?string
    {
        return $this->intro;
    }

    public function setIntro(string $intro): self
    {
        $this->intro = $intro;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMakerId()
    {
        return $this->makerId;
    }

    /**
     * @param mixed $makerId
     */
    public function setMakerId($makerId): void
    {
        $this->makerId = $makerId;
    }

    /**
     * @return mixed
     */
    public function getProductionModels()
    {
        return $this->productionModels;
    }

    /**
     * @param mixed $productionModels
     */
    public function setProductionModels($productionModels): void
    {
        $this->productionModels = $productionModels;
    }

    /**
     * @return mixed
     */
    public function getPaymentPlans()
    {
        return $this->paymentPlans;
    }

    /**
     * @param mixed $paymentPlans
     */
    public function setPaymentPlans($paymentPlans): void
    {
        $this->paymentPlans = $paymentPlans;
    }

    /**
     * @return mixed
     */
    public function getSpeciesDoes()
    {
        return $this->speciesDoes;
    }

    /**
     * @param mixed $speciesDoes
     */
    public function setSpeciesDoes($speciesDoes): void
    {
        $this->speciesDoes = $speciesDoes;
    }

    /**
     * @return mixed
     */
    public function getSpeciesDoesnt()
    {
        return $this->speciesDoesnt;
    }

    /**
     * @param mixed $speciesDoesnt
     */
    public function setSpeciesDoesnt($speciesDoesnt): void
    {
        $this->speciesDoesnt = $speciesDoesnt;
    }

    /**
     * @return mixed
     */
    public function getPricesUrl()
    {
        return $this->pricesUrl;
    }

    /**
     * @param mixed $pricesUrl
     */
    public function setPricesUrl($pricesUrl): void
    {
        $this->pricesUrl = $pricesUrl;
    }

    /**
     * @return mixed
     */
    public function getFaqUrl()
    {
        return $this->faqUrl;
    }

    /**
     * @param mixed $faqUrl
     */
    public function setFaqUrl($faqUrl): void
    {
        $this->faqUrl = $faqUrl;
    }

    /**
     * @return mixed
     */
    public function getOtherUrls()
    {
        return $this->otherUrls;
    }

    /**
     * @param mixed $otherUrls
     */
    public function setOtherUrls($otherUrls): void
    {
        $this->otherUrls = $otherUrls;
    }

    /**
     * @return mixed
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @return string
     */
    public function getFormerMakerIds(): string
    {
        return $this->formerMakerIds;
    }

    /**
     * @return array
     */
    public function getFormerMakerIdsArr(): array
    {
        return explode("\n", $this->formerMakerIds);
    }

    /**
     * @return array
     */
    public function getAllMakerIdsArr(): array
    {
        return array_filter(array_merge([$this->getMakerId()], $this->getFormerMakerIdsArr()));
    }

    /**
     * @param string $formerMakerIds
     */
    public function setFormerMakerIds(string $formerMakerIds): void
    {
        $this->formerMakerIds = $formerMakerIds;
    }

    /**
     * @param mixed $languages
     */
    public function setLanguages($languages): void
    {
        $this->languages = $languages;
    }

    public function completeness(): ?int
    {
        return (new CompletenessCalc())
            ->anyNotEmpty(CompletenessCalc::CRUCIAL, $this->makerId) // "force" to update - mandatory field
            // Name not counted - makes no sense
            // Formerly not counted - small minority has changed their names
            ->anyNotEmpty(CompletenessCalc::TRIVIAL, $this->intro)
            ->anyNotEmpty(CompletenessCalc::AVERAGE, $this->since)
            ->anyNotEmpty(CompletenessCalc::CRUCIAL, $this->country)
            ->anyNotEmpty(in_array($this->country, ['US', 'CA'])
                ? CompletenessCalc::MINOR : CompletenessCalc::INSIGNIFICANT, $this->state)
            ->anyNotEmpty(CompletenessCalc::IMPORTANT, $this->city)
            ->anyNotEmpty(CompletenessCalc::IMPORTANT, $this->productionModels)
            ->anyNotEmpty(CompletenessCalc::CRUCIAL, $this->styles, $this->otherStyles)
            ->anyNotEmpty(CompletenessCalc::CRUCIAL, $this->orderTypes, $this->otherOrderTypes)
            ->anyNotEmpty(CompletenessCalc::CRUCIAL, $this->features, $this->otherFeatures)
            ->anyNotEmpty(CompletenessCalc::AVERAGE, $this->paymentPlans)
            ->anyNotEmpty(CompletenessCalc::MINOR, $this->speciesDoes, $this->speciesDoesnt)
            // FursuitReview not checked, because we can force makers to force their customers to write reviews
            // ... shame...
            ->anyNotEmpty(CompletenessCalc::MINOR, $this->pricesUrl)
            ->anyNotEmpty(CompletenessCalc::TRIVIAL, $this->faqUrl) // it's optional, but nice to have
            ->anyNotEmpty(CompletenessCalc::CRUCIAL, $this->websiteUrl, $this->deviantArtUrl, $this->furAffinityUrl,
                $this->twitterUrl, $this->facebookUrl, $this->tumblrUrl, $this->instagramUrl, $this->youtubeUrl)
            // Commissions/quotes check URL not checked - we'll check if the CST had a match instead
            ->anyNotEmpty(CompletenessCalc::TRIVIAL, $this->queueUrl) // it's optional, but nice to have
            // Other URLs not checked - we're not requiring unknown
            ->anyNotEmpty(CompletenessCalc::MINOR, $this->languages)
            // Notes are not supposed to be displayed, thus not counted
            ->anyNotNull(CompletenessCalc::IMPORTANT, $this->areCommissionsOpen)
            // CST last check does not depend on artisan input
            ->result();
    }

    public function set(string $fieldName, $newValue): self
    {
        if (!property_exists(self::class, $fieldName)) {
            throw new InvalidArgumentException("Field $fieldName does not exist");
        }

        $setter = 'set'.ucfirst($fieldName);

        call_user_func([$this, $setter], $newValue);

        return $this;
    }

    public function get(string $fieldName)
    {
        if (!property_exists(self::class, $fieldName)) {
            throw new InvalidArgumentException("Field $fieldName does not exist");
        }

        $getter = 'get'.ucfirst($fieldName);

        return call_user_func([$this, $getter]);
    }

    public function jsonSerialize(): array
    {
        $data = get_object_vars($this);
        unset($data['id']);

        foreach (ArtisanFields::lists() as $field) {
            $data[$field->modelName()] = array_filter(explode("\n", $data[$field->modelName()]));
        }

        $data['completeness'] = $this->completeness();
        $data['commissionsQuotesLastCheck'] = null === $this->getCommissionsQuotesLastCheck()
            ? 'unknown' : date('Y-m-d H:i:s', $this->getCommissionsQuotesLastCheck()->getTimestamp());

        return array_values($data);
    }

    public function getCommissionsStatus(): ?ArtisanCommissionsStatus
    {
        return $this->commissionsStatus;
    }

    public function setCommissionsStatus(ArtisanCommissionsStatus $commissionsStatus): self
    {
        $this->commissionsStatus = $commissionsStatus;

        if ($this !== $commissionsStatus->getArtisan()) {
            $commissionsStatus->setArtisan($this);
        }

        return $this;
    }

    public function getContactMethod(): ?string
    {
        return $this->contactMethod;
    }

    public function setContactMethod(?string $contactMethod): self
    {
        $this->contactMethod = $contactMethod;

        return $this;
    }

    public function getContactAddress(): ?string
    {
        return $this->contactAddress;
    }

    public function setContactAddress(?string $contactAddress): self
    {
        $this->contactAddress = $contactAddress;

        return $this;
    }

    public function getPrivateData(): ?ArtisanPrivateData
    {
        return $this->privateData;
    }

    public function setPrivateData(ArtisanPrivateData $privateData): self
    {
        $this->privateData = $privateData;

        if ($this !== $privateData->getArtisan()) {
            $privateData->setArtisan($this);
        }

        return $this;
    }

    public function getContactAllowed(): ?string
    {
        return $this->contactAllowed;
    }

    public function setContactAllowed(string $contactAllowed): self
    {
        $this->contactAllowed = $contactAllowed;

        return $this;
    }
}
