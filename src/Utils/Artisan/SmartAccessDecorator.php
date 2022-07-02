<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\DataDefinitions\Ages;
use App\DataDefinitions\ContactPermit;
use App\DataDefinitions\Fields\Field;
use App\DataDefinitions\Fields\Fields;
use App\DataDefinitions\Fields\FieldsList;
use App\DataDefinitions\Fields\Validation;
use App\Entity\Artisan as ArtisanE;
use App\Entity\ArtisanCommissionsStatus;
use App\Entity\ArtisanPrivateData;
use App\Entity\ArtisanUrl;
use App\Entity\ArtisanValue;
use App\Entity\ArtisanVolatileData;
use App\Entity\MakerId;
use App\Utils\Artisan\Fields\CommissionAccessor;
use App\Utils\Artisan\Fields\UrlAccessor;
use App\Utils\Contact;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\Utils\FieldReadInterface;
use App\Utils\Parse;
use App\Utils\StringList;
use App\Utils\StrUtils;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use JsonSerializable;
use Stringable;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SmartAccessDecorator implements FieldReadInterface, JsonSerializable, Stringable
{
    public function __construct(
        private ?ArtisanE $artisan = null,
    ) {
        if (null === $this->artisan) {
            $this->artisan = new ArtisanE();
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
     * @param ArtisanE[] $artisans
     *
     * @return SmartAccessDecorator[]
     */
    public static function wrapAll(array $artisans): array
    {
        return array_map(fn (ArtisanE $artisan): SmartAccessDecorator => self::wrap($artisan), $artisans);
    }

    public static function wrap(ArtisanE $artisan): self
    {
        return new self($artisan);
    }

    public function getArtisan(): ArtisanE
    {
        return $this->artisan;
    }

    public function set(Field $field, mixed $newValue): self
    {
        $setter = 'set'.ucfirst($field->modelName() ?: 'noModelName');

        if (method_exists($this->artisan, $setter)) {
            call_user_func([$this->artisan, $setter], $newValue);
        }

        if (!method_exists($this, $setter)) {
            throw new InvalidArgumentException("Setter for $field->name does not exist");
        }

        call_user_func([$this, $setter], $newValue);

        return $this;
    }

    public function get(Field $field): mixed
    {
        $getter = 'get'.ucfirst($field->modelName() ?: 'noModelName');

        if (method_exists($this->artisan, $getter)) {
            return call_user_func([$this->artisan, $getter]);
        }

        if (!method_exists($this, $getter)) {
            throw new InvalidArgumentException("Getter for $field->name does not exist");
        }

        return call_user_func([$this, $getter]);
    }

    //
    // ===== MAKER ID HELPERS =====
    //

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

    //
    // ===== VARIOUS HELPERS, DATA-TABLE HELPERS =====
    //

    public function getIsMinor(): ?bool
    {
        return $this->getBoolValue(Field::IS_MINOR);
    }

    public function setIsMinor(?bool $isMinor): self
    {
        return $this->setBoolValue(Field::IS_MINOR, $isMinor);
    }

    #[NotNull(message: 'You must answer this question.', groups: [Validation::GRP_DATA])]
    public function getAges(): ?Ages
    {
        return Ages::get($this->getStringValue(Field::AGES));
    }

    public function setAges(?Ages $ages): self
    {
        return $this->setStringValue(Field::AGES, $ages?->value);
    }

    #[NotNull(message: 'You must answer this question.', groups: [Validation::GRP_DATA])]
    public function getNsfwWebsite(): ?bool
    {
        return $this->getBoolValue(Field::NSFW_WEBSITE);
    }

    public function setNsfwWebsite(?bool $nsfwWebsite): self
    {
        return $this->setBoolValue(Field::NSFW_WEBSITE, $nsfwWebsite);
    }

    #[NotNull(message: 'You must answer this question.', groups: [Validation::GRP_DATA])]
    public function getNsfwSocial(): ?bool
    {
        return $this->getBoolValue(Field::NSFW_SOCIAL);
    }

    public function setNsfwSocial(?bool $nsfwSocial): self
    {
        return $this->setBoolValue(Field::NSFW_SOCIAL, $nsfwSocial);
    }

    public function getDoesNsfw(): ?bool
    {
        return $this->getBoolValue(Field::DOES_NSFW);
    }

    public function setDoesNsfw(?bool $doesNsfw): self
    {
        return $this->setBoolValue(Field::DOES_NSFW, $doesNsfw);
    }

    public function getWorksWithMinors(): ?bool
    {
        return $this->getBoolValue(Field::WORKS_WITH_MINORS);
    }

    public function setWorksWithMinors(?bool $worksWithMinors): self
    {
        return $this->setBoolValue(Field::WORKS_WITH_MINORS, $worksWithMinors);
    }

    public function isAllowedToDoNsfw(): ?bool
    {
        if (null === ($ages = $this->getAges())) {
            return null;
        }

        return Ages::ADULTS === $ages;
    }

    public function isAllowedToWorkWithMinors(): ?bool
    {
        $nsfwWebsite = $this->getNsfwWebsite();
        $nsfwSocial = $this->getNsfwSocial();

        if (null === $nsfwWebsite || null === $nsfwSocial) {
            return null;
        }

        if (true === $nsfwWebsite || true === $nsfwSocial) {
            return false;
        }

        $doesNsfw = $this->getDoesNsfw();

        if (null !== $doesNsfw) {
            return !$doesNsfw;
        }

        return match ($this->isAllowedToDoNsfw()) {
            false => true,
            true, null => null,
        };
    }

    public function getSafeDoesNsfw(): ?bool
    {
        if (null === ($allowed = $this->isAllowedToDoNsfw())) {
            return null;
        }

        if (false === $allowed) {
            return false;
        }

        return $this->getDoesNsfw();
    }

    public function getSafeWorksWithMinors(): ?bool
    {
        if (null === ($allowed = $this->isAllowedToWorkWithMinors())) {
            return null;
        }

        if (false === $allowed) {
            return false;
        }

        return $this->getWorksWithMinors();
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

    public function updateContact(string $newOriginalContactValue): void
    {
        [$method, $address] = Contact::parse($newOriginalContactValue);

        $obfuscated = match ($method) {
            Contact::INVALID => 'PLEASE CORRECT',
            ''               => '',
            default          => $method.': '.Contact::obscure($address),
        };

        $this->setContactMethod($method)
            ->setContactInfoObfuscated($obfuscated)
            ->getPrivateData()
            ->setOriginalContactInfo($newOriginalContactValue)
            ->setContactAddress($address);
    }

    /**
     * Even though we serve only "safe" version of the NSFW-related fields,
     * these internal ("unsafe") values needs to be fixed even in the database,
     * as its snapshots are public.
     */
    public function assureNsfwSafety(): void
    {
        if (false !== $this->getNsfwWebsite() || false !== $this->getNsfwSocial() || false !== $this->getDoesNsfw()) {
            if (true === $this->getWorksWithMinors()) {
                $this->setWorksWithMinors(false); // No, you don't
            }
        }

        if (Ages::ADULTS !== $this->getAges()) {
            if (true === $this->getDoesNsfw()) {
                $this->setDoesNsfw(false); // No, you don't
            }
        }
    }

    public function getDateAdded(): ?DateTimeImmutable
    {
        return $this->getDateTimeValue(Field::DATE_ADDED);
    }

    public function setDateAdded(?DateTimeImmutable $dateAdded): self
    {
        $this->setDateTimeValue(Field::DATE_ADDED, $dateAdded);

        return $this;
    }

    public function getDateUpdated(): ?DateTimeImmutable
    {
        return $this->getDateTimeValue(Field::DATE_UPDATED);
    }

    public function setDateUpdated(?DateTimeImmutable $dateUpdated): self
    {
        $this->setDateTimeValue(Field::DATE_UPDATED, $dateUpdated);

        return $this;
    }

    //
    // ===== VOLATILE DATA GETTERS AND SETTERS =====
    //

    public function getCsLastCheck(): ?DateTimeImmutable
    {
        return $this->getVolatileData()->getLastCsUpdate();
    }

    public function setCsLastCheck(?DateTimeImmutable $csLastCheck): void
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

    #[Length(max: 1024)]
    public function getFursuitReviewUrl(): string
    {
        return $this->getUrl(Field::URL_FURSUITREVIEW);
    }

    public function setFursuitReviewUrl(string $fursuitReviewUrl): self
    {
        return $this->setUrl(Field::URL_FURSUITREVIEW, $fursuitReviewUrl);
    }

    #[Length(max: 1024)]
    public function getFurAffinityUrl(): string
    {
        return $this->getUrl(Field::URL_FUR_AFFINITY);
    }

    public function setFurAffinityUrl(string $furAffinityUrl): self
    {
        return $this->setUrl(Field::URL_FUR_AFFINITY, $furAffinityUrl);
    }

    #[Length(max: 1024)]
    public function getDeviantArtUrl(): string
    {
        return $this->getUrl(Field::URL_DEVIANTART);
    }

    public function setDeviantArtUrl(string $deviantArtUrl): self
    {
        return $this->setUrl(Field::URL_DEVIANTART, $deviantArtUrl);
    }

    #[Length(max: 1024)]
    public function getWebsiteUrl(): string
    {
        return $this->getUrl(Field::URL_WEBSITE);
    }

    public function setWebsiteUrl(string $websiteUrl): self
    {
        return $this->setUrl(Field::URL_WEBSITE, $websiteUrl);
    }

    #[Length(max: 1024)]
    public function getFacebookUrl(): string
    {
        return $this->getUrl(Field::URL_FACEBOOK);
    }

    public function setFacebookUrl(string $facebookUrl): self
    {
        return $this->setUrl(Field::URL_FACEBOOK, $facebookUrl);
    }

    #[Length(max: 1024)]
    public function getTwitterUrl(): string
    {
        return $this->getUrl(Field::URL_TWITTER);
    }

    public function setTwitterUrl(string $twitterUrl): self
    {
        return $this->setUrl(Field::URL_TWITTER, $twitterUrl);
    }

    #[Length(max: 1024)]
    public function getTumblrUrl(): string
    {
        return $this->getUrl(Field::URL_TUMBLR);
    }

    public function setTumblrUrl(string $tumblrUrl): self
    {
        return $this->setUrl(Field::URL_TUMBLR, $tumblrUrl);
    }

    #[Length(max: 1024)]
    public function getCommissionsUrls(): string
    {
        return $this->getUrl(Field::URL_COMMISSIONS);
    }

    public function setCommissionsUrls(string $commissionsUrls): self
    {
        return $this->setUrl(Field::URL_COMMISSIONS, $commissionsUrls);
    }

    #[Length(max: 1024)]
    public function getQueueUrl(): string
    {
        return $this->getUrl(Field::URL_QUEUE);
    }

    public function setQueueUrl(string $queueUrl): self
    {
        return $this->setUrl(Field::URL_QUEUE, $queueUrl);
    }

    #[Length(max: 1024)]
    public function getInstagramUrl(): string
    {
        return $this->getUrl(Field::URL_INSTAGRAM);
    }

    public function setInstagramUrl(string $instagramUrl): self
    {
        return $this->setUrl(Field::URL_INSTAGRAM, $instagramUrl);
    }

    #[Length(max: 1024)]
    public function getYoutubeUrl(): string
    {
        return $this->getUrl(Field::URL_YOUTUBE);
    }

    public function setYoutubeUrl(string $youtubeUrl): self
    {
        return $this->setUrl(Field::URL_YOUTUBE, $youtubeUrl);
    }

    #[Length(max: 1024)]
    public function getPricesUrls(): string
    {
        return $this->getUrl(Field::URL_PRICES);
    }

    /**
     * @return string[]
     */
    public function getPricesUrlsArray(): array
    {
        return StringList::unpack($this->getUrl(Field::URL_PRICES));
    }

    public function setPricesUrls(string $pricesUrls): self
    {
        return $this->setUrl(Field::URL_PRICES, $pricesUrls);
    }

    #[Length(max: 1024)]
    public function getFaqUrl(): string
    {
        return $this->getUrl(Field::URL_FAQ);
    }

    public function setFaqUrl(string $faqUrl): self
    {
        return $this->setUrl(Field::URL_FAQ, $faqUrl);
    }

    #[Length(max: 1024)]
    public function getLinklistUrl(): string
    {
        return $this->getUrl(Field::URL_LINKLIST);
    }

    public function setLinklistUrl(string $url): self
    {
        return $this->setUrl(Field::URL_LINKLIST, $url);
    }

    #[Length(max: 1024)]
    public function getFurryAminoUrl(): string
    {
        return $this->getUrl(Field::URL_FURRY_AMINO);
    }

    public function setFurryAminoUrl(string $url): self
    {
        return $this->setUrl(Field::URL_FURRY_AMINO, $url);
    }

    #[Length(max: 1024)]
    public function getEtsyUrl(): string
    {
        return $this->getUrl(Field::URL_ETSY);
    }

    public function setEtsyUrl(string $url): self
    {
        return $this->setUrl(Field::URL_ETSY, $url);
    }

    #[Length(max: 1024)]
    public function getTheDealersDenUrl(): string
    {
        return $this->getUrl(Field::URL_THE_DEALERS_DEN);
    }

    public function setTheDealersDenUrl(string $url): self
    {
        return $this->setUrl(Field::URL_THE_DEALERS_DEN, $url);
    }

    #[Length(max: 1024)]
    public function getOtherShopUrl(): string
    {
        return $this->getUrl(Field::URL_OTHER_SHOP);
    }

    public function setOtherShopUrl(string $url): self
    {
        return $this->setUrl(Field::URL_OTHER_SHOP, $url);
    }

    #[Length(max: 1024)]
    public function getOtherUrls(): string
    {
        return $this->getUrl(Field::URL_OTHER);
    }

    public function setOtherUrls($otherUrls): self
    {
        return $this->setUrl(Field::URL_OTHER, $otherUrls);
    }

    #[Length(max: 1024)]
    public function getScritchUrl(): string
    {
        return $this->getUrl(Field::URL_SCRITCH);
    }

    public function setScritchUrl(string $scritchUrl): self
    {
        return $this->setUrl(Field::URL_SCRITCH, $scritchUrl);
    }

    #[Length(max: 1024)]
    public function getFurtrackUrl(): string
    {
        return $this->getUrl(Field::URL_FURTRACK);
    }

    public function setFurtrackUrl(string $furtrackUrl): self
    {
        return $this->setUrl(Field::URL_FURTRACK, $furtrackUrl);
    }

    #[Length(max: 1024)]
    public function getPhotoUrls(): string
    {
        return $this->getUrl(Field::URL_PHOTOS);
    }

    public function setPhotoUrls(string $photoUrls): self
    {
        return $this->setUrl(Field::URL_PHOTOS, $photoUrls);
    }

    #[Length(max: 1024)]
    public function getMiniatureUrls(): string
    {
        return $this->getUrl(Field::URL_MINIATURES);
    }

    public function setMiniatureUrls(string $scritchMiniatureUrls): self
    {
        return $this->setUrl(Field::URL_MINIATURES, $scritchMiniatureUrls);
    }

    /**
     * @return ArtisanUrl[]
     */
    public function getUrlObjs(Field $urlField): array
    {
        return UrlAccessor::getObjs($this, $urlField->name);
    }

    private function getUrl(Field $urlField): string
    {
        return UrlAccessor::get($this, $urlField->name);
    }

    private function setUrl(Field $urlField, string $newUrl): self
    {
        UrlAccessor::set($this, $urlField->name, $newUrl);

        return $this;
    }

    //
    // ===== JSON STUFF =====
    //

    private function getValuesForJson(FieldsList $fields): array
    {
        return array_map(function (Field $field) {
            $value = match ($field) {
                Field::COMPLETENESS       => $this->getCompleteness(),
                Field::CS_LAST_CHECK      => StrUtils::asStr($this->getCsLastCheck()),
                Field::DATE_ADDED         => StrUtils::asStr($this->getDateAdded()),
                Field::DATE_UPDATED       => StrUtils::asStr($this->getDateUpdated()),
                default                   => $this->get($field),
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
        return $this->getValuesForJson(Fields::all());
    }

    public function jsonSerialize(): array
    {
        return $this->getPublicData();
    }

    //
    // ===== NON-TRIVIAL VALIDATION =====
    //

    /** @noinspection PhpUnusedParameterInspection */
    #[Callback(groups: [Validation::GRP_DATA])]
    public function validateData(ExecutionContextInterface $context, $payload): void
    {
        if (null === $this->getDoesNsfw() && $this->isAllowedToDoNsfw()) {
            $context
                ->buildViolation('You must answer this question.')
                ->atPath(Field::DOES_NSFW->modelName())
                ->addViolation();
        }

        if (null === $this->getWorksWithMinors() && $this->isAllowedToWorkWithMinors()) {
            $context
                ->buildViolation('You must answer this question.')
                ->atPath(Field::WORKS_WITH_MINORS->modelName())
                ->addViolation();
        }
    }

    /** @noinspection PhpUnusedParameterInspection */
    #[Callback(groups: [Validation::GRP_CONTACT_AND_PASSWORD])]
    public function validateContactAndPassword(ExecutionContextInterface $context, $payload): void
    {
        if (ContactPermit::NO !== $this->artisan->getContactAllowed() && '' === $this->artisan->getContactInfoObfuscated()) {
            $context
                ->buildViolation('This value should not be blank.')
                ->atPath(Field::CONTACT_INFO_OBFUSCATED->modelName())
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

    #[Regex(pattern: '/^[A-Z0-9]*$/', message: 'Use only uppercase letters and/or digits (A-Z, 0-9).')]
    #[Regex(pattern: '/^(.{7})?$/', message: 'Use exactly 7 characters.')]
    #[NotBlank(groups: [Validation::GRP_DATA])]
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

    #[Length(max: 128)]
    #[NotBlank]
    public function getName(): string
    {
        return $this->artisan->getName();
    }

    public function setName(string $name): self
    {
        $this->artisan->setName($name);

        return $this;
    }

    #[Length(max: 256)]
    public function getFormerly(): string
    {
        return $this->artisan->getFormerly();
    }

    public function setFormerly(string $formerly): self
    {
        $this->artisan->setFormerly($formerly);

        return $this;
    }

    #[Length(max: 512)]
    public function getIntro(): string
    {
        return $this->artisan->getIntro();
    }

    public function setIntro(string $intro): self
    {
        $this->artisan->setIntro($intro);

        return $this;
    }

    #[Length(max: 16)]
    public function getSince(): string
    {
        return $this->artisan->getSince();
    }

    public function setSince(string $since): self
    {
        $this->artisan->setSince($since);

        return $this;
    }

    #[Length(max: 16)]
    #[NotBlank(groups: [Validation::GRP_DATA])]
    public function getCountry(): string
    {
        return $this->artisan->getCountry();
    }

    public function setCountry(string $country): self
    {
        $this->artisan->setCountry($country);

        return $this;
    }

    #[Length(max: 32)]
    public function getState(): string
    {
        return $this->artisan->getState();
    }

    public function setState(string $state): self
    {
        $this->artisan->setState($state);

        return $this;
    }

    #[Length(max: 32)]
    public function getCity(): string
    {
        return $this->artisan->getCity();
    }

    public function setCity(string $city): self
    {
        $this->artisan->setCity($city);

        return $this;
    }

    #[Length(max: 256)]
    public function getProductionModelsComment(): string
    {
        return $this->artisan->getProductionModelsComment();
    }

    public function setProductionModelsComment(string $productionModelsComment): self
    {
        $this->artisan->setProductionModelsComment($productionModelsComment);

        return $this;
    }

    #[Length(max: 256)]
    public function getProductionModels(): string
    {
        return $this->artisan->getProductionModels();
    }

    public function setProductionModels(string $productionModels): self
    {
        $this->artisan->setProductionModels($productionModels);

        return $this;
    }

    #[Length(max: 256)]
    public function getStylesComment(): string
    {
        return $this->artisan->getStylesComment();
    }

    public function setStylesComment(string $stylesComment): self
    {
        $this->artisan->setStylesComment($stylesComment);

        return $this;
    }

    #[Length(max: 1024)]
    public function getStyles(): string
    {
        return $this->artisan->getStyles();
    }

    public function setStyles(string $styles): self
    {
        $this->artisan->setStyles($styles);

        return $this;
    }

    #[Length(max: 1024)]
    public function getOtherStyles(): string
    {
        return $this->artisan->getOtherStyles();
    }

    public function setOtherStyles(string $otherStyles): self
    {
        $this->artisan->setOtherStyles($otherStyles);

        return $this;
    }

    #[Length(max: 256)]
    public function getOrderTypesComment(): string
    {
        return $this->artisan->getOrderTypesComment();
    }

    public function setOrderTypesComment(string $orderTypesComment): self
    {
        $this->artisan->setOrderTypesComment($orderTypesComment);

        return $this;
    }

    #[Length(max: 1024)]
    public function getOrderTypes(): string
    {
        return $this->artisan->getOrderTypes();
    }

    public function setOrderTypes(string $orderTypes): self
    {
        $this->artisan->setOrderTypes($orderTypes);

        return $this;
    }

    #[Length(max: 1024)]
    public function getOtherOrderTypes(): string
    {
        return $this->artisan->getOtherOrderTypes();
    }

    public function setOtherOrderTypes(string $otherOrderTypes): self
    {
        $this->artisan->setOtherOrderTypes($otherOrderTypes);

        return $this;
    }

    #[Length(max: 256)]
    public function getFeaturesComment(): string
    {
        return $this->artisan->getFeaturesComment();
    }

    public function setFeaturesComment(string $featuresComment): self
    {
        $this->artisan->setFeaturesComment($featuresComment);

        return $this;
    }

    #[Length(max: 1024)]
    public function getFeatures(): string
    {
        return $this->artisan->getFeatures();
    }

    public function setFeatures(string $features): self
    {
        $this->artisan->setFeatures($features);

        return $this;
    }

    #[Length(max: 1024)]
    public function getOtherFeatures(): string
    {
        return $this->artisan->getOtherFeatures();
    }

    public function setOtherFeatures(string $otherFeatures): self
    {
        $this->artisan->setOtherFeatures($otherFeatures);

        return $this;
    }

    #[Length(max: 256)]
    public function getPaymentPlans(): string
    {
        return $this->artisan->getPaymentPlans();
    }

    public function setPaymentPlans(string $paymentPlans): self
    {
        $this->artisan->setPaymentPlans($paymentPlans);

        return $this;
    }

    #[Length(max: 256)]
    public function getPaymentMethods(): string
    {
        return $this->artisan->getPaymentMethods();
    }

    public function setPaymentMethods(string $paymentMethods): self
    {
        $this->artisan->setPaymentMethods($paymentMethods);

        return $this;
    }

    #[Length(max: 64)]
    public function getCurrenciesAccepted(): string
    {
        return $this->artisan->getCurrenciesAccepted();
    }

    public function setCurrenciesAccepted(string $currenciesAccepted): self
    {
        $this->artisan->setCurrenciesAccepted($currenciesAccepted);

        return $this;
    }

    #[Length(max: 256)]
    public function getSpeciesComment(): string
    {
        return $this->artisan->getSpeciesComment();
    }

    public function setSpeciesComment(string $speciesComment): self
    {
        $this->artisan->setSpeciesComment($speciesComment);

        return $this;
    }

    #[Length(max: 256)]
    public function getSpeciesDoes(): string
    {
        return $this->artisan->getSpeciesDoes();
    }

    public function setSpeciesDoes(string $speciesDoes): self
    {
        $this->artisan->setSpeciesDoes($speciesDoes);

        return $this;
    }

    #[Length(max: 256)]
    public function getSpeciesDoesnt(): string
    {
        return $this->artisan->getSpeciesDoesnt();
    }

    public function setSpeciesDoesnt(string $speciesDoesnt): self
    {
        $this->artisan->setSpeciesDoesnt($speciesDoesnt);

        return $this;
    }

    #[Length(max: 256)]
    public function getLanguages(): string
    {
        return $this->artisan->getLanguages();
    }

    public function setLanguages(string $languages): self
    {
        $this->artisan->setLanguages($languages);

        return $this;
    }

    #[Length(max: 4096)]
    public function getNotes(): string
    {
        return $this->artisan->getNotes();
    }

    public function setNotes(string $notes): self
    {
        $this->artisan->setNotes($notes);

        return $this;
    }

    #[Length(max: 512)]
    public function getInactiveReason(): string
    {
        return $this->artisan->getInactiveReason();
    }

    public function setInactiveReason(string $inactiveReason): self
    {
        $this->artisan->setInactiveReason($inactiveReason);

        return $this;
    }

    #[Length(max: 16)]
    #[NotBlank(groups: [Validation::GRP_CONTACT_AND_PASSWORD])]
    public function getContactAllowed(): string
    {
        return $this->artisan->getContactAllowed();
    }

    public function setContactAllowed(string $contactAllowed): self
    {
        $this->artisan->setContactAllowed($contactAllowed);

        return $this;
    }

    #[Length(max: 32)]
    public function getContactMethod(): string
    {
        return $this->artisan->getContactMethod();
    }

    public function setContactMethod(string $contactMethod): self
    {
        $this->artisan->setContactMethod($contactMethod);

        return $this;
    }

    #[Length(max: 128)]
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

    #[Valid]
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

    private function getBoolValue(Field $field): ?bool
    {
        $value = $this->getStringValue($field);

        return null !== $value ? Parse::nBool($value) : null;
    }

    private function setBoolValue(Field $field, ?bool $newValue): self
    {
        if (null !== $newValue) {
            $newValue = StrUtils::asStr($newValue);
        }

        return $this->setStringValue($field, $newValue);
    }

    private function getDateTimeValue(Field $field): ?DateTimeImmutable
    {
        $value = $this->getStringValue($field);

        try {
            return null !== $value ? UtcClock::at($value) : null;
        } catch (DateTimeException) {
            return null;
        }
    }

    private function setDateTimeValue(Field $field, ?DateTimeImmutable $newValue): self
    {
        if (null !== $newValue) {
            $newValue = StrUtils::asStr($newValue);
        }

        return $this->setStringValue($field, $newValue);
    }

    private function getStringValue(Field $field): ?string
    {
        foreach ($this->artisan->getValues() as $value) {
            if ($value->getFieldName() === $field->name) {
                return $value->getValue();
            }
        }

        return null;
    }

    private function setStringValue(Field $field, ?string $newValue): self
    {
        foreach ($this->artisan->getValues() as $value) {
            if ($value->getFieldName() === $field->name) {
                if (null === $newValue) {
                    $this->artisan->getValues()->removeElement($value);
                } else {
                    $value->setValue($newValue);
                }

                return $this;
            }
        }

        if (null !== $newValue) {
            $newEntity = (new ArtisanValue())
                ->setFieldName($field->name)
                ->setValue($newValue);
            $this->artisan->addValue($newEntity);
        }

        return $this;
    }
}
