<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\DataDefinitions\Field;
use App\DataDefinitions\Fields;
use App\DataDefinitions\FieldsList;
use App\Entity\Artisan;
use App\Entity\ArtisanCommissionsStatus;
use App\Entity\ArtisanPrivateData;
use App\Entity\ArtisanUrl;
use App\Entity\ArtisanVolatileData;
use App\Entity\MakerId;
use App\Utils\Artisan\Fields\CommissionAccessor;
use App\Utils\Artisan\Fields\UrlAccessor;
use App\Utils\FieldReadInterface;
use App\Utils\StringList;
use App\Utils\StrUtils;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use JsonSerializable;
use Stringable;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SmartAccessDecorator implements FieldReadInterface, JsonSerializable, Stringable
{
    public function __construct(
        #[Valid]
        private ?Artisan $artisan = null,
    ) {
        if (null === $this->artisan) {
            $this->artisan = new Artisan();
        }
    }

    public function __clone()
    {
        $this->artisan = clone $this->artisan;
    }

    public function __toString(): string
    {
        return $this->artisan->__toString();
    }

    /**
     * @param Artisan[] $artisans
     *
     * @return SmartAccessDecorator[]
     */
    public static function wrapAll(array $artisans): array
    {
        return array_map(fn (Artisan $artisan): SmartAccessDecorator => self::wrap($artisan), $artisans);
    }

    public static function wrap(Artisan $artisan): self
    {
        return new self($artisan);
    }

    public function getArtisan(): Artisan
    {
        return $this->artisan;
    }

    public function set(Field | string $field, mixed $newValue): self
    {
        if (!($field instanceof Field)) {
            $field = Fields::get((string) $field);
        }

        $setter = 'set'.ucfirst($field->modelName() ?: 'noModelName');

        if (method_exists($this->artisan, $setter)) {
            call_user_func([$this->artisan, $setter], $newValue);
        }

        if (!method_exists($this, $setter)) {
            throw new InvalidArgumentException("Setter for {$field->name()} does not exist");
        }

        call_user_func([$this, $setter], $newValue);

        return $this;
    }

    public function get(Field | string $field): mixed
    {
        if (!($field instanceof Field)) {
            $field = Fields::get((string) $field);
        }

        $getter = 'get'.ucfirst($field->modelName() ?: 'noModelName');

        if (method_exists($this->artisan, $getter)) {
            return call_user_func([$this->artisan, $getter]);
        }

        if (!method_exists($this, $getter)) {
            throw new InvalidArgumentException("Getter for {$field->name()} does not exist");
        }

        return call_user_func([$this, $getter]);
    }

    // ===== HELPER GETTERS AND SETTERS =====

    public function getLastMakerId(): string
    {
        return $this->artisan->getMakerId() ?: current($this->getFormerMakerIdsArr());
    }

    public function hasMakerId(string $makerId): bool
    {
        return in_array($makerId, $this->artisan->getMakerIds()
            ->map(fn (MakerId $makerId): string => $makerId->getMakerId())
            ->toArray());
    }

    public function getFormerMakerIds(): string
    {
        return StringList::pack($this->getFormerMakerIdsArr());
    }

    public function setFormerMakerIds(string $formerMakerIdsToSet): self
    {
        $formerMakerIdsToSet = StringList::unpack($formerMakerIdsToSet);
        $allMakerIdsToSet = array_merge($formerMakerIdsToSet, [$this->artisan->getMakerId()]);

        foreach ($this->artisan->getMakerIds() as $makerId) {
            if (!in_array($makerId->getMakerId(), $allMakerIdsToSet)) {
                $this->artisan->removeMakerId($makerId);
            }
        }

        foreach ($formerMakerIdsToSet as $makerId) {
            $this->addMakerId($makerId);
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getFormerMakerIdsArr(): array
    {
        return $this->artisan->getMakerIds()
            ->map(fn (MakerId $makerId): string => $makerId->getMakerId())
            ->filter(fn (string $makerId): bool => $makerId !== $this->getMakerId())
            ->toArray();
    }

    /**
     * @return string[]
     */
    public function getAllMakerIdsArr(): array
    {
        return array_filter(array_merge([$this->artisan->getMakerId()], $this->getFormerMakerIdsArr()));
    }

    /**
     * @return string[]
     */
    public function getFormerlyArr(): array
    {
        return StringList::unpack($this->artisan->getFormerly());
    }

    /**
     * @return string[]
     */
    public function getAllNamesArr(): array
    {
        return array_filter(array_merge([$this->artisan->getName()], $this->getFormerlyArr()));
    }

    public function getCompleteness(): int
    {
        return CompletenessCalc::count($this);
    }

    public function allowsFeedback(): bool
    {
        return ContactPermit::FEEDBACK === $this->artisan->getContactAllowed();
    }

    //
    // ===== VOLATILE DATA GETTERS AND SETTERS =====
    //

    public function getCsLastCheck(): ?DateTimeInterface
    {
        return $this->getVolatileData()->getLastCsUpdate();
    }

    public function setCsLastCheck(?DateTimeInterface $csLastCheck): void
    {
        $this->getVolatileData()->setLastCsUpdate($csLastCheck);
    }

    public function getCsTrackerIssue(): bool
    {
        return $this->getVolatileData()->getCsTrackerIssue();
    }

    public function setCsTrackerIssue(bool $csTrackerIssue): self
    {
        $this->getVolatileData()->setCsTrackerIssue($csTrackerIssue);

        return $this;
    }

    public function getBpLastCheck(): ?DateTimeInterface
    {
        return $this->getVolatileData()->getLastBpUpdate();
    }

    public function setBpLastCheck(?DateTimeInterface $bpLastCheck): void
    {
        $this->getVolatileData()->setLastBpUpdate($bpLastCheck);
    }

    public function getBpTrackerIssue(): bool
    {
        return $this->getVolatileData()->getBpTrackerIssue();
    }

    public function setBpTrackerIssue(bool $bpTrackerIssue): self
    {
        $this->getVolatileData()->setBpTrackerIssue($bpTrackerIssue);

        return $this;
    }

    //
    // ===== COMMISSIONS STATUS HELPERS =====
    //

    public function getOpenFor(): string
    {
        return CommissionAccessor::get($this, true);
    }

    /**
     * @return string[]
     */
    public function getOpenForArray(): array
    {
        return CommissionAccessor::getList($this, true);
    }

    public function setOpenFor(string $openFor): self
    {
        CommissionAccessor::set($this, true, $openFor);

        return $this;
    }

    public function getClosedFor(): string
    {
        return CommissionAccessor::get($this, false);
    }

    /**
     * @return string[]
     */
    public function getClosedForArray(): array
    {
        return CommissionAccessor::getList($this, false);
    }

    public function setClosedFor(string $closedFor): self
    {
        CommissionAccessor::set($this, false, $closedFor);

        return $this;
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

    public function getPassword(): string
    {
        return $this->getPrivateData()->getPassword();
    }

    public function setPassword(string $password): self
    {
        $this->getPrivateData()->setPassword($password);

        return $this;
    }

    //
    // ===== URLS GETTERS AND SETTERS =====
    //

    public function getFursuitReviewUrl(): string
    {
        return $this->getUrl(Fields::URL_FURSUITREVIEW);
    }

    public function setFursuitReviewUrl(string $fursuitReviewUrl): self
    {
        $this->setUrl(Fields::URL_FURSUITREVIEW, $fursuitReviewUrl);

        return $this;
    }

    public function getFurAffinityUrl(): string
    {
        return $this->getUrl(Fields::URL_FUR_AFFINITY);
    }

    public function setFurAffinityUrl(string $furAffinityUrl): self
    {
        $this->setUrl(Fields::URL_FUR_AFFINITY, $furAffinityUrl);

        return $this;
    }

    public function getDeviantArtUrl(): string
    {
        return $this->getUrl(Fields::URL_DEVIANTART);
    }

    public function setDeviantArtUrl(string $deviantArtUrl): self
    {
        $this->setUrl(Fields::URL_DEVIANTART, $deviantArtUrl);

        return $this;
    }

    public function getWebsiteUrl(): string
    {
        return $this->getUrl(Fields::URL_WEBSITE);
    }

    public function setWebsiteUrl(string $websiteUrl): self
    {
        $this->setUrl(Fields::URL_WEBSITE, $websiteUrl);

        return $this;
    }

    public function getFacebookUrl(): string
    {
        return $this->getUrl(Fields::URL_FACEBOOK);
    }

    public function setFacebookUrl(string $facebookUrl): self
    {
        $this->setUrl(Fields::URL_FACEBOOK, $facebookUrl);

        return $this;
    }

    public function getTwitterUrl(): string
    {
        return $this->getUrl(Fields::URL_TWITTER);
    }

    public function setTwitterUrl(string $twitterUrl): self
    {
        $this->setUrl(Fields::URL_TWITTER, $twitterUrl);

        return $this;
    }

    public function getTumblrUrl(): string
    {
        return $this->getUrl(Fields::URL_TUMBLR);
    }

    public function setTumblrUrl(string $tumblrUrl): self
    {
        $this->setUrl(Fields::URL_TUMBLR, $tumblrUrl);

        return $this;
    }

    public function getCommissionsUrl(): string
    {
        return $this->getUrl(Fields::URL_COMMISSIONS);
    }

    public function setCommissionsUrl(string $commissionsUrl): self
    {
        $this->setUrl(Fields::URL_COMMISSIONS, $commissionsUrl);

        return $this;
    }

    public function getQueueUrl(): string
    {
        return $this->getUrl(Fields::URL_QUEUE);
    }

    public function setQueueUrl(string $queueUrl): self
    {
        $this->setUrl(Fields::URL_QUEUE, $queueUrl);

        return $this;
    }

    public function getInstagramUrl(): string
    {
        return $this->getUrl(Fields::URL_INSTAGRAM);
    }

    public function setInstagramUrl(string $instagramUrl): self
    {
        $this->setUrl(Fields::URL_INSTAGRAM, $instagramUrl);

        return $this;
    }

    public function getYoutubeUrl(): string
    {
        return $this->getUrl(Fields::URL_YOUTUBE);
    }

    public function setYoutubeUrl(string $youtubeUrl): self
    {
        $this->setUrl(Fields::URL_YOUTUBE, $youtubeUrl);

        return $this;
    }

    public function getPricesUrl(): string
    {
        return $this->getUrl(Fields::URL_PRICES);
    }

    /**
     * @return string[]
     */
    public function getPricesUrls(): array
    {
        return StringList::unpack($this->getUrl(Fields::URL_PRICES));
    }

    public function setPricesUrl(string $pricesUrl): self
    {
        $this->setUrl(Fields::URL_PRICES, $pricesUrl);

        return $this;
    }

    public function getFaqUrl(): string
    {
        return $this->getUrl(Fields::URL_FAQ);
    }

    public function setFaqUrl(string $faqUrl): self
    {
        $this->setUrl(Fields::URL_FAQ, $faqUrl);

        return $this;
    }

    public function getLinklistUrl(): string
    {
        return $this->getUrl(Fields::URL_LINKLIST);
    }

    public function setLinklistUrl(string $url): self
    {
        $this->setUrl(Fields::URL_LINKLIST, $url);

        return $this;
    }

    public function getFurryAminoUrl(): string
    {
        return $this->getUrl(Fields::URL_FURRY_AMINO);
    }

    public function setFurryAminoUrl(string $url): self
    {
        $this->setUrl(Fields::URL_FURRY_AMINO, $url);

        return $this;
    }

    public function getEtsyUrl(): string
    {
        return $this->getUrl(Fields::URL_ETSY);
    }

    public function setEtsyUrl(string $url): self
    {
        $this->setUrl(Fields::URL_ETSY, $url);

        return $this;
    }

    public function getTheDealersDenUrl(): string
    {
        return $this->getUrl(Fields::URL_THE_DEALERS_DEN);
    }

    public function setTheDealersDenUrl(string $url): self
    {
        $this->setUrl(Fields::URL_THE_DEALERS_DEN, $url);

        return $this;
    }

    public function getOtherShopUrl(): string
    {
        return $this->getUrl(Fields::URL_OTHER_SHOP);
    }

    public function setOtherShopUrl(string $url): self
    {
        $this->setUrl(Fields::URL_OTHER_SHOP, $url);

        return $this;
    }

    public function getOtherUrls(): string
    {
        return $this->getUrl(Fields::URL_OTHER);
    }

    public function setOtherUrls($otherUrls): self
    {
        $this->setUrl(Fields::URL_OTHER, $otherUrls);

        return $this;
    }

    public function getScritchUrl(): string
    {
        return $this->getUrl(Fields::URL_SCRITCH);
    }

    public function setScritchUrl(string $scritchUrl): self
    {
        $this->setUrl(Fields::URL_SCRITCH, $scritchUrl);

        return $this;
    }

    public function getFurtrackUrl(): string
    {
        return $this->getUrl(Fields::URL_FURTRACK);
    }

    public function setFurtrackUrl(string $furtrackUrl): self
    {
        $this->setUrl(Fields::URL_FURTRACK, $furtrackUrl);

        return $this;
    }

    public function getPhotoUrls(): string
    {
        return $this->getUrl(Fields::URL_PHOTOS);
    }

    public function setPhotoUrls(string $photoUrls): self
    {
        $this->setUrl(Fields::URL_PHOTOS, $photoUrls);

        return $this;
    }

    public function getMiniatureUrls(): string
    {
        return $this->getUrl(Fields::URL_MINIATURES);
    }

    public function setMiniatureUrls(string $scritchMiniatureUrls): self
    {
        $this->setUrl(Fields::URL_MINIATURES, $scritchMiniatureUrls);

        return $this;
    }

    /**
     * @return ArtisanUrl[]
     */
    public function getUrlObjs(string $urlFieldName): array
    {
        return UrlAccessor::getObjs($this, $urlFieldName);
    }

    private function getUrl(string $urlFieldName): string
    {
        return UrlAccessor::get($this, $urlFieldName);
    }

    private function setUrl(string $urlFieldName, string $newUrl): void
    {
        UrlAccessor::set($this, $urlFieldName, $newUrl);
    }

    //
    // ===== JSON STUFF =====
    //

    private function getValuesForJson(FieldsList $fields): array
    {
        return array_map(function (Field $field) {
            $value = match ($field->name()) {
                Fields::COMPLETENESS       => $this->getCompleteness(),
                Fields::CS_LAST_CHECK      => StrUtils::asStr($this->getCsLastCheck()),
                Fields::BP_LAST_CHECK      => StrUtils::asStr($this->getBpLastCheck()),
                default                    => $this->get($field),
            };

            return $field->isList() && !is_array($value) ? StringList::unpack($value) : $value;
        }, $fields->asArray());
    }

    public function getPublicData(): array
    {
        return $this->getValuesForJson(Fields::public());
    }

    public function getAllData(): array
    {
        return $this->getValuesForJson(Fields::getAll());
    }

    public function jsonSerialize(): array
    {
        return $this->getPublicData();
    }

    //
    // ===== NON-TRIVIAL VALIDATION =====
    //

    /** @noinspection PhpUnusedParameterInspection */
    #[Callback(groups: ['iu_form'])]
    public function validate(ExecutionContextInterface $context, $payload): void
    {
        if (ContactPermit::NO !== $this->artisan->getContactAllowed() && '' === $this->artisan->getContactInfoObfuscated()) {
            $context
                ->buildViolation('This value should not be blank.')
                ->atPath(Fields::get(Fields::CONTACT_INFO_OBFUSCATED)->modelName())
                ->addViolation();
        }
    }

    /*******************************************************************************************************************

    No, __call(), method_exists(), call_user_func() are not cool enough (Twig etc.). Boilerplate time!

    grep -P 'public function (?!__)' src/Entity/Artisan.php \
        | sed -r 's/public function ((set|add|remove)[a-zA-Z]+)\(([^ ]+ )(\$[a-zA-Z]+)\): self/\0 { $this->artisan->\1(\4); return $this; }/' \
        | sed -r 's/public function (get[a-zA-Z]+)\(\): ([?a-zA-Z| ]+)/\0 { return $this->artisan->\1(); }/'

    NOTE: Few methods were changed.

    *******************************************************************************************************************/

    public function getId(): ?int
    {
        return $this->artisan->getId();
    }

    public function getMakerId(): string
    {
        return $this->artisan->getMakerId();
    }

    public function setMakerId(string $makerId): self
    {
        $this->artisan->setMakerId($makerId);

        if ('' !== $makerId) {
            $this->addMakerId($makerId);
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->artisan->getName();
    }

    public function setName(string $name): self
    {
        $this->artisan->setName($name);

        return $this;
    }

    public function getFormerly(): string
    {
        return $this->artisan->getFormerly();
    }

    public function setFormerly(string $formerly): self
    {
        $this->artisan->setFormerly($formerly);

        return $this;
    }

    public function getIntro(): string
    {
        return $this->artisan->getIntro();
    }

    public function setIntro(string $intro): self
    {
        $this->artisan->setIntro($intro);

        return $this;
    }

    public function getSince(): string
    {
        return $this->artisan->getSince();
    }

    public function setSince(string $since): self
    {
        $this->artisan->setSince($since);

        return $this;
    }

    public function getCountry(): string
    {
        return $this->artisan->getCountry();
    }

    public function setCountry(string $country): self
    {
        $this->artisan->setCountry($country);

        return $this;
    }

    public function getState(): string
    {
        return $this->artisan->getState();
    }

    public function setState(string $state): self
    {
        $this->artisan->setState($state);

        return $this;
    }

    public function getCity(): string
    {
        return $this->artisan->getCity();
    }

    public function setCity(string $city): self
    {
        $this->artisan->setCity($city);

        return $this;
    }

    public function getProductionModelsComment(): string
    {
        return $this->artisan->getProductionModelsComment();
    }

    public function setProductionModelsComment(string $productionModelsComment): self
    {
        $this->artisan->setProductionModelsComment($productionModelsComment);

        return $this;
    }

    public function getProductionModels(): string
    {
        return $this->artisan->getProductionModels();
    }

    public function setProductionModels(string $productionModels): self
    {
        $this->artisan->setProductionModels($productionModels);

        return $this;
    }

    public function getStylesComment(): string
    {
        return $this->artisan->getStylesComment();
    }

    public function setStylesComment(string $stylesComment): self
    {
        $this->artisan->setStylesComment($stylesComment);

        return $this;
    }

    public function getStyles(): string
    {
        return $this->artisan->getStyles();
    }

    public function setStyles(string $styles): self
    {
        $this->artisan->setStyles($styles);

        return $this;
    }

    public function getOtherStyles(): string
    {
        return $this->artisan->getOtherStyles();
    }

    public function setOtherStyles(string $otherStyles): self
    {
        $this->artisan->setOtherStyles($otherStyles);

        return $this;
    }

    public function getOrderTypesComment(): string
    {
        return $this->artisan->getOrderTypesComment();
    }

    public function setOrderTypesComment(string $orderTypesComment): self
    {
        $this->artisan->setOrderTypesComment($orderTypesComment);

        return $this;
    }

    public function getOrderTypes(): string
    {
        return $this->artisan->getOrderTypes();
    }

    public function setOrderTypes(string $orderTypes): self
    {
        $this->artisan->setOrderTypes($orderTypes);

        return $this;
    }

    public function getOtherOrderTypes(): string
    {
        return $this->artisan->getOtherOrderTypes();
    }

    public function setOtherOrderTypes(string $otherOrderTypes): self
    {
        $this->artisan->setOtherOrderTypes($otherOrderTypes);

        return $this;
    }

    public function getFeaturesComment(): string
    {
        return $this->artisan->getFeaturesComment();
    }

    public function setFeaturesComment(string $featuresComment): self
    {
        $this->artisan->setFeaturesComment($featuresComment);

        return $this;
    }

    public function getFeatures(): string
    {
        return $this->artisan->getFeatures();
    }

    public function setFeatures(string $features): self
    {
        $this->artisan->setFeatures($features);

        return $this;
    }

    public function getOtherFeatures(): string
    {
        return $this->artisan->getOtherFeatures();
    }

    public function setOtherFeatures(string $otherFeatures): self
    {
        $this->artisan->setOtherFeatures($otherFeatures);

        return $this;
    }

    public function getPaymentPlans(): string
    {
        return $this->artisan->getPaymentPlans();
    }

    public function setPaymentPlans(string $paymentPlans): self
    {
        $this->artisan->setPaymentPlans($paymentPlans);

        return $this;
    }

    public function getPaymentMethods(): string
    {
        return $this->artisan->getPaymentMethods();
    }

    public function setPaymentMethods(string $paymentMethods): self
    {
        $this->artisan->setPaymentMethods($paymentMethods);

        return $this;
    }

    public function getCurrenciesAccepted(): string
    {
        return $this->artisan->getCurrenciesAccepted();
    }

    public function setCurrenciesAccepted(string $currenciesAccepted): self
    {
        $this->artisan->setCurrenciesAccepted($currenciesAccepted);

        return $this;
    }

    public function getSpeciesComment(): string
    {
        return $this->artisan->getSpeciesComment();
    }

    public function setSpeciesComment(string $speciesComment): self
    {
        $this->artisan->setSpeciesComment($speciesComment);

        return $this;
    }

    public function getSpeciesDoes(): string
    {
        return $this->artisan->getSpeciesDoes();
    }

    public function setSpeciesDoes(string $speciesDoes): self
    {
        $this->artisan->setSpeciesDoes($speciesDoes);

        return $this;
    }

    public function getSpeciesDoesnt(): string
    {
        return $this->artisan->getSpeciesDoesnt();
    }

    public function setSpeciesDoesnt(string $speciesDoesnt): self
    {
        $this->artisan->setSpeciesDoesnt($speciesDoesnt);

        return $this;
    }

    public function getLanguages(): string
    {
        return $this->artisan->getLanguages();
    }

    public function setLanguages(string $languages): self
    {
        $this->artisan->setLanguages($languages);

        return $this;
    }

    public function getNotes(): string
    {
        return $this->artisan->getNotes();
    }

    public function setNotes(string $notes): self
    {
        $this->artisan->setNotes($notes);

        return $this;
    }

    public function getInactiveReason(): string
    {
        return $this->artisan->getInactiveReason();
    }

    public function setInactiveReason(string $inactiveReason): self
    {
        $this->artisan->setInactiveReason($inactiveReason);

        return $this;
    }

    public function getContactAllowed(): string
    {
        return $this->artisan->getContactAllowed();
    }

    public function setContactAllowed(string $contactAllowed): self
    {
        $this->artisan->setContactAllowed($contactAllowed);

        return $this;
    }

    public function getContactMethod(): string
    {
        return $this->artisan->getContactMethod();
    }

    public function setContactMethod(string $contactMethod): self
    {
        $this->artisan->setContactMethod($contactMethod);

        return $this;
    }

    public function getContactInfoObfuscated(): string
    {
        return $this->artisan->getContactInfoObfuscated();
    }

    public function setContactInfoObfuscated(string $contactInfoObfuscated): self
    {
        $this->artisan->setContactInfoObfuscated($contactInfoObfuscated);

        return $this;
    }

    public function getVolatileData(): ArtisanVolatileData
    {
        if (null === ($res = $this->artisan->getVolatileData())) {
            $this->artisan->setVolatileData($res = new ArtisanVolatileData());
        }

        return $res;
    }

    public function setVolatileData(?ArtisanVolatileData $volatileData): self
    {
        $this->artisan->setVolatileData($volatileData);

        return $this;
    }

    public function getPrivateData(): ArtisanPrivateData
    {
        if (null === ($res = $this->artisan->getPrivateData())) {
            $this->artisan->setPrivateData($res = new ArtisanPrivateData());
        }

        return $res;
    }

    public function setPrivateData(?ArtisanPrivateData $privateData): self
    {
        $this->artisan->setPrivateData($privateData);

        return $this;
    }

    public function getUrls(): Collection|array
    {
        return $this->artisan->getUrls();
    }

    public function addUrl(ArtisanUrl $artisanUrl): self
    {
        $this->artisan->addUrl($artisanUrl);

        return $this;
    }

    public function removeUrl(ArtisanUrl $artisanUrl): self
    {
        $this->artisan->removeUrl($artisanUrl);

        return $this;
    }

    public function getCommissions(): Collection|array
    {
        return $this->artisan->getCommissions();
    }

    public function addCommission(ArtisanCommissionsStatus $commission): self
    {
        $this->artisan->addCommission($commission);

        return $this;
    }

    public function removeCommission(ArtisanCommissionsStatus $commission): self
    {
        $this->artisan->removeCommission($commission);

        return $this;
    }

    public function getMakerIds(): Collection|array
    {
        return $this->artisan->getMakerIds();
    }

    public function addMakerId(MakerId|string $makerId): self
    {
        if (!($makerId instanceof MakerId)) {
            if ($this->hasMakerId($makerId)) {
                return $this;
            }

            $makerId = new MakerId($makerId);
        }

        $this->artisan->addMakerId($makerId);

        return $this;
    }

    public function removeMakerId(MakerId $makerId): self
    {
        $this->artisan->removeMakerId($makerId);

        return $this;
    }
}
