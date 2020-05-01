<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utils\Artisan\ContactPermit;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use App\Utils\CompletenessCalc;
use App\Utils\FieldReadInterface;
use App\Utils\Json;
use App\Utils\StringList;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use JsonException;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtisanRepository")
 * @ORM\Table(name="artisans")
 */
class Artisan implements JsonSerializable, FieldReadInterface
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
     * @ORM\Column(type="string", length=64)
     */
    private string $formerMakerIds = '';

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
     * @ORM\OneToOne(targetEntity="App\Entity\ArtisanCommissionsStatus", mappedBy="artisan", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private ?ArtisanCommissionsStatus $commissionsStatus = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\ArtisanPrivateData", mappedBy="artisan", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private ?ArtisanPrivateData $privateData = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ArtisanUrl", mappedBy="artisan", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $urls;

    public function __construct()
    {
        $this->urls = new ArrayCollection();
    }

    public function __clone()
    {
        if ($this->privateData) {
            $this->privateData = clone $this->privateData;
        }

        if ($this->commissionsStatus) {
            $this->commissionsStatus = clone $this->commissionsStatus;
        }

        $urls = $this->urls;
        $this->urls = new ArrayCollection();

        foreach ($urls as $url) {
            $this->addUrl(clone $url);
        }
    }

    // ===== LEGITIMATE GETTERS AND SETTERS =====

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

    public function getFormerMakerIds(): string
    {
        return $this->formerMakerIds;
    }

    public function setFormerMakerIds(string $formerMakerIds): self
    {
        $this->formerMakerIds = $formerMakerIds;

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

    public function setSpeciesDoesnt($speciesDoesnt): self
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

    public function getCommissionsStatus(): ArtisanCommissionsStatus
    {
        return $this->commissionsStatus ?? $this->commissionsStatus = (new ArtisanCommissionsStatus())->setArtisan($this);
    }

    public function setCommissionsStatus(ArtisanCommissionsStatus $commissionsStatus): self
    {
        $this->commissionsStatus = $commissionsStatus;

        if ($this !== $commissionsStatus->getArtisan()) {
            $commissionsStatus->setArtisan($this);
        }

        return $this;
    }

    public function getPrivateData(): ArtisanPrivateData
    {
        return $this->privateData ?? $this->privateData = (new ArtisanPrivateData())->setArtisan($this);
    }

    public function setPrivateData(ArtisanPrivateData $privateData): self
    {
        $this->privateData = $privateData;

        if ($this !== $privateData->getArtisan()) {
            $privateData->setArtisan($this);
        }

        return $this;
    }

    /**
     * @return Collection|ArtisanUrl[]
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
        if ($this->urls->contains($artisanUrl)) {
            $this->urls->removeElement($artisanUrl);
            if ($artisanUrl->getArtisan() === $this) {
                $artisanUrl->setArtisan(null);
            }
        }

        return $this;
    }

    // ===== HELPER GETTERS AND SETTERS =====

    public function set(Field $field, $newValue): self
    {
        if ($field->is(Fields::CONTACT_INPUT_VIRTUAL)) {
            $this->setContactInfoOriginal($newValue);

            return $this;
        }

        $setter = 'set'.ucfirst($field->modelName() ?: 'noModelName');

        if (!method_exists($this, $setter)) {
            throw new InvalidArgumentException("Setter for {$field->name()} does not exist");
        }

        call_user_func([$this, $setter], $newValue);

        return $this;
    }

    public function get(Field $field)
    {
        if ($field->is(Fields::CONTACT_INPUT_VIRTUAL)) {
            return $this->getContactInfoOriginal();
        }

        $getter = 'get'.ucfirst($field->modelName() ?: 'noModelName');

        if (!method_exists($this, $getter)) {
            throw new InvalidArgumentException("Getter for {$field->name()} does not exist");
        }

        return call_user_func([$this, $getter]);
    }

    public function getLastMakerId(): string
    {
        return $this->getMakerId() ?: current($this->getFormerMakerIdsArr());
    }

    /**
     * @return string[]
     */
    public function getFormerMakerIdsArr(): array
    {
        return StringList::unpack($this->formerMakerIds);
    }

    /**
     * @return string[]
     */
    public function getAllMakerIdsArr(): array
    {
        return array_filter(array_merge([$this->getMakerId()], $this->getFormerMakerIdsArr()));
    }

    /**
     * @return string[]
     */
    public function getFormerlyArr(): array
    {
        return StringList::unpack($this->formerly);
    }

    /**
     * @return string[]
     */
    public function getAllNamesArr(): array
    {
        return array_filter(array_merge([$this->getName()], $this->getFormerlyArr()));
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
            ->anyNotEmpty(CompletenessCalc::MINOR, $this->getPricesUrl())
            ->anyNotEmpty(CompletenessCalc::TRIVIAL, $this->getFaqUrl()) // it's optional, but nice to have
            ->anyNotEmpty(CompletenessCalc::CRUCIAL, $this->getWebsiteUrl(), $this->getDeviantArtUrl(),
                $this->getFurAffinityUrl(), $this->getTwitterUrl(), $this->getFacebookUrl(),
                $this->getTumblrUrl(), $this->getInstagramUrl(), $this->getYoutubeUrl())
            // Commissions/quotes check URL not checked - we'll check if the CST had a match instead
            ->anyNotEmpty(CompletenessCalc::TRIVIAL, $this->getQueueUrl()) // it's optional, but nice to have
            // Other URLs not checked - we're not requiring unknown
            ->anyNotEmpty(CompletenessCalc::MINOR, $this->languages)
            // Notes are not supposed to be displayed, thus not counted
            ->anyNotNull(CompletenessCalc::IMPORTANT, $this->getCommissionsStatus()->getStatus())
            // CST last check does not depend on artisan input
            ->result();
    }

    public function allowsFeedback(): bool
    {
        return ContactPermit::FEEDBACK === $this->contactAllowed;
    }

    //
    // ===== PRIVATE DATA GETTERS AND SETTERS =====
    //

    public function getContactAddressPlain(): string
    {
        return $this->getPrivateData()->getContactAddress();
    }

    public function setContactAddressPlain(string $contactAddressPlain): self
    {
        $this->getPrivateData()->setContactAddress($contactAddressPlain);

        return $this;
    }

    public function getContactInfoOriginal(): string
    {
        return $this->getPrivateData()->getOriginalContactInfo();
    }

    public function setContactInfoOriginal(string $contactInfoOriginal): self
    {
        $this->getPrivateData()->setOriginalContactInfo($contactInfoOriginal);

        return $this;
    }

    public function getPasscode(): string
    {
        return $this->getPrivateData()->getPasscode();
    }

    public function setPasscode(string $passcode): self
    {
        $this->getPrivateData()->setPasscode($passcode);

        return $this;
    }

    //
    // ===== URLS GETTERS AND SETTERS =====
    //

    public function getFursuitReviewUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_FURSUITREVIEW);
    }

    public function setFursuitReviewUrl(string $fursuitReviewUrl): self
    {
        $this->setSingleUrl(Fields::URL_FURSUITREVIEW, $fursuitReviewUrl);

        return $this;
    }

    public function getFurAffinityUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_FUR_AFFINITY);
    }

    public function setFurAffinityUrl(string $furAffinityUrl): self
    {
        $this->setSingleUrl(Fields::URL_FUR_AFFINITY, $furAffinityUrl);

        return $this;
    }

    public function getDeviantArtUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_DEVIANTART);
    }

    public function setDeviantArtUrl(string $deviantArtUrl): self
    {
        $this->setSingleUrl(Fields::URL_DEVIANTART, $deviantArtUrl);

        return $this;
    }

    public function getWebsiteUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_WEBSITE);
    }

    public function setWebsiteUrl(string $websiteUrl): self
    {
        $this->setSingleUrl(Fields::URL_WEBSITE, $websiteUrl);

        return $this;
    }

    public function getFacebookUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_FACEBOOK);
    }

    public function setFacebookUrl(string $facebookUrl): self
    {
        $this->setSingleUrl(Fields::URL_FACEBOOK, $facebookUrl);

        return $this;
    }

    public function getTwitterUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_TWITTER);
    }

    public function setTwitterUrl(string $twitterUrl): self
    {
        $this->setSingleUrl(Fields::URL_TWITTER, $twitterUrl);

        return $this;
    }

    public function getTumblrUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_TUMBLR);
    }

    public function setTumblrUrl(string $tumblrUrl): self
    {
        $this->setSingleUrl(Fields::URL_TUMBLR, $tumblrUrl);

        return $this;
    }

    public function getCstUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_CST);
    }

    public function setCstUrl(string $cstUrl): self
    {
        $this->setSingleUrl(Fields::URL_CST, $cstUrl);

        return $this;
    }

    public function getQueueUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_QUEUE);
    }

    public function setQueueUrl(string $queueUrl): self
    {
        $this->setSingleUrl(Fields::URL_QUEUE, $queueUrl);

        return $this;
    }

    public function getInstagramUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_INSTAGRAM);
    }

    public function setInstagramUrl(string $instagramUrl): self
    {
        $this->setSingleUrl(Fields::URL_INSTAGRAM, $instagramUrl);

        return $this;
    }

    public function getYoutubeUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_YOUTUBE);
    }

    public function setYoutubeUrl(string $youtubeUrl): self
    {
        $this->setSingleUrl(Fields::URL_YOUTUBE, $youtubeUrl);

        return $this;
    }

    public function getPricesUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_PRICES);
    }

    public function setPricesUrl(string $pricesUrl): self
    {
        $this->setSingleUrl(Fields::URL_PRICES, $pricesUrl);

        return $this;
    }

    public function getFaqUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_FAQ);
    }

    public function setFaqUrl(string $faqUrl): self
    {
        $this->setSingleUrl(Fields::URL_FAQ, $faqUrl);

        return $this;
    }

    public function getLinktreeUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_LINKTREE);
    }

    public function setLinktreeUrl(string $url): self
    {
        $this->setSingleUrl(Fields::URL_LINKTREE, $url);

        return $this;
    }

    public function getFurryAminoUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_FURRY_AMINO);
    }

    public function setFurryAminoUrl(string $url): self
    {
        $this->setSingleUrl(Fields::URL_FURRY_AMINO, $url);

        return $this;
    }

    public function getEtsyUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_ETSY);
    }

    public function setEtsyUrl(string $url): self
    {
        $this->setSingleUrl(Fields::URL_ETSY, $url);

        return $this;
    }

    public function getTheDealersDenUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_THE_DEALERS_DEN);
    }

    public function setTheDealersDenUrl(string $url): self
    {
        $this->setSingleUrl(Fields::URL_THE_DEALERS_DEN, $url);

        return $this;
    }

    public function getOtherShopUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_OTHER_SHOP);
    }

    public function setOtherShopUrl(string $url): self
    {
        $this->setSingleUrl(Fields::URL_OTHER_SHOP, $url);

        return $this;
    }

    public function getOtherUrls(): string
    {
        return $this->getSingleUrl(Fields::URL_OTHER);
    }

    public function setOtherUrls($otherUrls): self
    {
        $this->setSingleUrl(Fields::URL_OTHER, $otherUrls);

        return $this;
    }

    public function getScritchUrl(): string
    {
        return $this->getSingleUrl(Fields::URL_SCRITCH);
    }

    public function setScritchUrl(string $scritchUrl): self
    {
        $this->setSingleUrl(Fields::URL_SCRITCH, $scritchUrl);

        return $this;
    }

    public function getScritchPhotoUrls(): string
    {
        return $this->getSingleUrl(Fields::URL_SCRITCH_PHOTO);
    }

    public function setScritchPhotoUrls(string $scritchPhotoUrls): self
    {
        $this->setSingleUrl(Fields::URL_SCRITCH_PHOTO, $scritchPhotoUrls);

        return $this;
    }

    public function getScritchMiniatureUrls(): string
    {
        return $this->getSingleUrl(Fields::URL_SCRITCH_MINIATURE);
    }

    public function setScritchMiniatureUrls(string $scritchMiniatureUrls): self
    {
        $this->setSingleUrl(Fields::URL_SCRITCH_MINIATURE, $scritchMiniatureUrls);

        return $this;
    }

    public function getSingleUrlObject(string $urlFieldName): ?ArtisanUrl
    {
        foreach ($this->getUrls() as $url) {
            if ($url->getType() === $urlFieldName) {
                return $url;
            }
        }

        return null;
    }

    private function getSingleUrl(string $urlFieldName): string
    {
        if (($url = $this->getSingleUrlObject($urlFieldName))) {
            return $url->getUrl();
        } else {
            return '';
        }
    }

    private function setSingleUrl(string $urlFieldName, string $newUrl): self
    {
        foreach ($this->getUrls() as $url) {
            if ($url->getType() === $urlFieldName) {
                if ('' === $newUrl) {
                    $this->removeUrl($url);
                } else {
                    $url->setUrl($newUrl);
                }

                return $this;
            }
        }

        if ('' !== $newUrl) {
            $this->addUrl((new ArtisanUrl())->setType($urlFieldName)->setUrl($newUrl));
        }

        return $this;
    }

    //
    // ===== JSON STUFF =====
    //

    private function getValuesForJson(): array
    {
        return array_map(function (Field $field) {
            switch ($field->name()) {
                case Fields::CST_LAST_CHECK:
                    $lc = $this->getCommissionsStatus()->getLastChecked();
                    $value = null === $lc ? 'unknown' : $lc->format('Y-m-d H:i:s');
                    break;

                case Fields::COMMISSIONS_STATUS:
                    $value = $this->getCommissionsStatus()->getStatus();
                    break;

                case Fields::COMPLETENESS:
                    $value = $this->completeness();
                    break;

                default:
                    $value = $this->get($field);
            }

            return $field->isList() ? StringList::unpack($value) : $value;
        }, Fields::inJson());
    }

    public function jsonSerialize(): array
    {
        return $this->getValuesForJson();
    }

    /**
     * @throws JsonException
     */
    public function getJsonArray(): string
    {
        return Json::encode(array_values($this->getValuesForJson()));
    }
}
