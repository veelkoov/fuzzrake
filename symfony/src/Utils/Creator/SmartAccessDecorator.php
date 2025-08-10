<?php

declare(strict_types=1);

namespace App\Utils\Creator;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Data\Definitions\Fields\FieldsList;
use App\Data\Definitions\Fields\Validation;
use App\Data\FieldValue;
use App\Entity\Creator as CreatorE;
use App\Entity\CreatorId;
use App\Entity\CreatorPrivateData;
use App\Entity\CreatorUrl;
use App\Entity\CreatorValue;
use App\Entity\CreatorVolatileData;
use App\Utils\Collections\Lists;
use App\Utils\Collections\StringLists;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\DateTime\UtcClock;
use App\Utils\Enforce;
use App\Utils\FieldReadInterface;
use App\Utils\PackedStringList;
use App\Utils\Parse;
use App\Utils\StrUtils;
use App\Validator\StrListLength;
use App\Validator\UpdateableEmail;
use DateTimeImmutable;
use InvalidArgumentException;
use JsonSerializable;
use Override;
use Stringable;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

// FIXME: Valid email should be required also in MX forms https://github.com/veelkoov/fuzzrake/issues/284
#[UpdateableEmail(groups: [Validation::GRP_CONTACT_AND_PASSWORD])]
class SmartAccessDecorator implements FieldReadInterface, JsonSerializable, Stringable
{
    public function __construct(
        public private(set) CreatorE $entity = new CreatorE(),
    ) {
    }

    public function __clone()
    {
        $this->entity = clone $this->entity;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->entity->__toString();
    }

    /**
     * @param CreatorE[] $creators
     *
     * @return self[]
     */
    public static function wrapAll(array $creators): array
    {
        return arr_map($creators, static fn (CreatorE $creator) => self::wrap($creator));
    }

    public static function wrap(CreatorE $creator): self
    {
        return new self($creator);
    }

    public function set(Field $field, mixed $newValue): self
    {
        $callback = [$this, 'set'.ucfirst($field->modelName())];

        if (!is_callable($callback)) {
            throw new InvalidArgumentException("Setter for $field->value does not exist");
        }

        FieldValue::validateType($field, $newValue); // To make sure a string doesn't get coerced to a boolean etc.

        call_user_func($callback, $newValue);

        return $this;
    }

    #[Override]
    public function get(Field $field): mixed
    {
        $callback = [$this, 'get'.ucfirst($field->modelName())];

        if (!is_callable($callback)) {
            throw new InvalidArgumentException("Getter for $field->value does not exist");
        }

        return call_user_func($callback); // @phpstan-ignore return.type (Field choices SHOULD™ guarantee the return value type)
    }

    public function equals(Field $field, self $other): bool // TODO: Improve https://github.com/veelkoov/fuzzrake/issues/221
    {
        if ($field->isList()) {
            return StringLists::sameElements($this->getStringList($field), $other->getStringList($field));
        } elseif ($field->isDate()) {
            return DateTimeUtils::equal($this->getDateTimeValue($field), $other->getDateTimeValue($field));
        } else {
            return $this->get($field) === $other->get($field);
        }
    }

    #[Override]
    public function getString(Field $field): string
    {
        return Enforce::string($this->get($field));
    }

    /**
     * @return list<string>
     */
    #[Override]
    public function getStringList(Field $field): array
    {
        return Enforce::strList($this->get($field));
    }

    #[Override]
    public function hasData(Field $field): bool
    {
        return $field->providedIn($this);
    }

    //
    // ===== CREATOR ID HELPERS =====
    //

    public function getLastCreatorId(): string
    {
        return $this->entity->getLastCreatorId();
    }

    public function hasCreatorId(string $creatorId): bool
    {
        return $this->entity->hasCreatorId($creatorId);
    }

    /**
     * @param list<string> $formerCreatorIdsToSet
     */
    public function setFormerCreatorIds(array $formerCreatorIdsToSet): self
    {
        $this->entity->setFormerCreatorIds($formerCreatorIdsToSet);

        return $this;
    }

    /**
     * Assume that values come from setting the creator ID, which is validated independently.
     *
     * @return list<string>
     */
    public function getFormerCreatorIds(): array
    {
        return $this->entity->getFormerCreatorIds();
    }

    /**
     * This does not guarantee any order, including current creator ID does not have to be the first one.
     *
     * @return list<string>
     */
    public function getAllCreatorIds(): array
    {
        return $this->entity->getAllCreatorIds();
    }

    //
    // ===== VARIOUS HELPERS, DATA-TABLE HELPERS =====
    //

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
        return match ($this->getAges()) {
            null => null,
            Ages::ADULTS => true,
            default => false,
        };
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
        return match ($this->isAllowedToDoNsfw()) {
            null => null,
            false => false,
            true => $this->getDoesNsfw(),
        };
    }

    public function getSafeWorksWithMinors(): ?bool
    {
        return match ($this->isAllowedToWorkWithMinors()) {
            null => null,
            false => false,
            true => $this->getWorksWithMinors(),
        };
    }

    /**
     * @return list<string>
     */
    public function getAllNames(): array
    {
        return Lists::nonEmptyStrings([$this->getName(), ...$this->getFormerly()]);
    }

    public function allowsFeedback(): bool
    {
        return ContactPermit::FEEDBACK === $this->entity->getContactAllowed();
    }

    public function hasSpeciesInfo(): bool
    {
        return [] !== $this->getSpeciesDoes() || [] !== $this->getSpeciesDoesnt();
    }

    public function isTracked(): bool
    {
        return [] !== $this->getCommissionsUrls();
    }

    public function isStatusKnown(): bool
    {
        return [] !== $this->getOpenFor() || [] !== $this->getClosedFor();
    }

    public function hasValidPhotos(): bool
    {
        return [] !== $this->getPhotoUrls() && count($this->getPhotoUrls()) === count($this->getMiniatureUrls());
    }

    public function isHidden(): bool
    {
        return '' !== $this->getInactiveReason();
    }

    /**
     * Even though we serve only "safe" version of the NSFW-related fields,
     * these internal ("unsafe") values needs to be fixed even in the database,
     * as its snapshots are public.
     */
    public function assureNsfwSafety(): void
    {
        if (true === $this->getWorksWithMinors()) {
            if (true !== $this->isAllowedToWorkWithMinors()) {
                $this->setWorksWithMinors(false); // No, you don't
            }
        }

        if (true === $this->getDoesNsfw()) {
            if (true !== $this->isAllowedToDoNsfw()) {
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

    public function getLastUpdateDateTime(): ?DateTimeImmutable
    {
        return $this->getDateUpdated() ?? $this->getDateAdded();
    }

    //
    // ===== VOLATILE DATA GETTERS AND SETTERS =====
    //

    public function getCsLastCheck(): ?DateTimeImmutable
    {
        return $this->getVolatileData()->getLastCsUpdate();
    }

    public function setCsLastCheck(?DateTimeImmutable $csLastCheck): self
    {
        $this->getVolatileData()->setLastCsUpdate($csLastCheck);

        return $this;
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

    /**
     * @return list<string>
     */
    public function getOpenFor(): array
    {
        return SmartOfferStatusAccessor::getList($this, true);
    }

    /**
     * @param list<string> $openFor
     */
    public function setOpenFor(array $openFor): self
    {
        SmartOfferStatusAccessor::setList($this, true, $openFor);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getClosedFor(): array
    {
        return SmartOfferStatusAccessor::getList($this, false);
    }

    /**
     * @param list<string> $closedFor
     */
    public function setClosedFor(array $closedFor): self
    {
        SmartOfferStatusAccessor::setList($this, false, $closedFor);

        return $this;
    }

    //
    // ===== PRIVATE DATA GETTERS AND SETTERS =====
    //

    #[Length(max: 128)]
    public function getEmailAddress(): string
    {
        return $this->getPrivateData()->getEmailAddress();
    }

    public function setEmailAddress(string $emailAddress): self
    {
        $this->getPrivateData()->setEmailAddress($emailAddress);

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
    public function getMastodonUrl(): string
    {
        return $this->getUrl(Field::URL_MASTODON);
    }

    public function setMastodonUrl(string $mastodonUrl): self
    {
        return $this->setUrl(Field::URL_MASTODON, $mastodonUrl);
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

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getCommissionsUrls(): array
    {
        return $this->getUrls(Field::URL_COMMISSIONS);
    }

    /**
     * @return list<CreatorUrl>
     */
    public function getCommissionsUrlObjects(): array
    {
        return $this->getUrlObjects(Field::URL_COMMISSIONS);
    }

    /**
     * @param list<string> $commissionsUrls
     */
    public function setCommissionsUrls(array $commissionsUrls): self
    {
        return $this->setUrls(Field::URL_COMMISSIONS, $commissionsUrls);
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

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getPricesUrls(): array
    {
        return $this->getUrls(Field::URL_PRICES);
    }

    /**
     * @param list<string> $pricesUrls
     */
    public function setPricesUrls(array $pricesUrls): self
    {
        return $this->setUrls(Field::URL_PRICES, $pricesUrls);
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

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getOtherUrls(): array
    {
        return $this->getUrls(Field::URL_OTHER);
    }

    /**
     * @param list<string> $otherUrls
     */
    public function setOtherUrls(array $otherUrls): self
    {
        return $this->setUrls(Field::URL_OTHER, $otherUrls);
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

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getPhotoUrls(): array
    {
        return $this->getUrls(Field::URL_PHOTOS);
    }

    /**
     * @return list<CreatorUrl>
     */
    public function getPhotoUrlObjects(): array
    {
        return $this->getUrlObjects(Field::URL_PHOTOS);
    }

    /**
     * @param list<string> $photoUrls
     */
    public function setPhotoUrls(array $photoUrls): self
    {
        if ($this->getPhotoUrls() !== $photoUrls) {
            // Make sure the photos are properly ordered in the I/U form; grep-code-order-support-workaround
            $this->setUrls(Field::URL_PHOTOS, []);
        }

        return $this->setUrls(Field::URL_PHOTOS, $photoUrls);
    }

    /**
     * Not validating - internal.
     *
     * @return list<string>
     */
    public function getMiniatureUrls(): array
    {
        return $this->getUrls(Field::URL_MINIATURES);
    }

    /**
     * @param list<string> $miniatureUrls
     */
    public function setMiniatureUrls(array $miniatureUrls): self
    {
        if ($this->getMiniatureUrls() !== $miniatureUrls) {
            // Make sure the miniatures are properly ordered on the creator card; grep-code-order-support-workaround
            $this->setUrls(Field::URL_MINIATURES, []);
        }

        return $this->setUrls(Field::URL_MINIATURES, $miniatureUrls);
    }

    #[Length(max: 1024)]
    public function getBlueskyUrl(): string
    {
        return $this->getUrl(Field::URL_BLUESKY);
    }

    public function setBlueskyUrl(string $blueskyUrl): self
    {
        return $this->setUrl(Field::URL_BLUESKY, $blueskyUrl);
    }

    #[Length(max: 1024)]
    public function getDonationsUrl(): string
    {
        return $this->getUrl(Field::URL_DONATIONS);
    }

    public function setDonationsUrl(string $donationsUrl): self
    {
        return $this->setUrl(Field::URL_DONATIONS, $donationsUrl);
    }

    #[Length(max: 1024)]
    public function getTelegramChannelUrl(): string
    {
        return $this->getUrl(Field::URL_TELEGRAM_CHANNEL);
    }

    public function setTelegramChannelUrl(string $telegramChannelUrl): self
    {
        return $this->setUrl(Field::URL_TELEGRAM_CHANNEL, $telegramChannelUrl);
    }

    #[Length(max: 1024)]
    public function getTikTokUrl(): string
    {
        return $this->getUrl(Field::URL_TIKTOK);
    }

    public function setTikTokUrl(string $tikTokUrl): self
    {
        return $this->setUrl(Field::URL_TIKTOK, $tikTokUrl);
    }

    private function getUrl(Field $urlField): string
    {
        return SmartUrlAccessor::getSingle($this, $urlField->value);
    }

    /**
     * @return list<string>
     */
    private function getUrls(Field $urlField): array
    {
        return SmartUrlAccessor::getList($this, $urlField->value);
    }

    /**
     * @return list<CreatorUrl>
     */
    private function getUrlObjects(Field $urlField): array
    {
        return SmartUrlAccessor::getObjects($this, $urlField->value);
    }

    private function setUrl(Field $urlField, string $newUrl): self
    {
        SmartUrlAccessor::setSingle($this, $urlField->value, $newUrl);

        return $this;
    }

    /**
     * @param list<string> $newUrls
     */
    private function setUrls(Field $urlField, array $newUrls): self
    {
        SmartUrlAccessor::setList($this, $urlField->value, $newUrls);

        return $this;
    }

    //
    // ===== JSON STUFF =====
    //

    /**
     * @return psJsonFieldsData
     */
    private function getValuesForJson(FieldsList $fields): array
    {
        $result = arr_map($fields->toArray(), fn (Field $field) => match ($field) {
            Field::CS_LAST_CHECK => StrUtils::asStr($this->getCsLastCheck()),
            Field::DATE_ADDED => StrUtils::asStr($this->getDateAdded()),
            Field::DATE_UPDATED => StrUtils::asStr($this->getDateUpdated()),
            default => $this->get($field),
        });

        return $result; // @phpstan-ignore return.type (FIXME)
    }

    /**
     * @return psJsonFieldsData
     */
    public function getPublicData(): array
    {
        return $this->getValuesForJson(Fields::public());
    }

    /**
     * @return psJsonFieldsData
     */
    public function getAllData(): array
    {
        return $this->getValuesForJson(Fields::all());
    }

    /**
     * @return psJsonFieldsData
     */
    #[Override]
    public function jsonSerialize(): array // Safely assume "public" for default
    {
        return $this->getPublicData();
    }

    //
    // ===== NON-TRIVIAL VALIDATION =====
    //

    /** @noinspection PhpUnusedParameterInspection */
    #[Callback(groups: [Validation::GRP_DATA])]
    public function validateData(ExecutionContextInterface $context, mixed $payload): void
    {
        if (null === $this->getDoesNsfw() && true === $this->isAllowedToDoNsfw()) {
            $context
                ->buildViolation('You must answer this question.')
                ->atPath(Field::DOES_NSFW->modelName())
                ->addViolation();
        }

        if (null === $this->getWorksWithMinors() && true === $this->isAllowedToWorkWithMinors()) {
            $context
                ->buildViolation('You must answer this question.')
                ->atPath(Field::WORKS_WITH_MINORS->modelName())
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->entity->getId();
    }

    #[Regex(pattern: '/^[A-Z0-9]*$/', message: 'Use only uppercase letters and/or digits (A-Z, 0-9).')]
    #[Regex(pattern: '/^(.{7})?$/', message: 'Use exactly 7 characters.')]
    #[NotBlank(groups: [Validation::GRP_DATA])]
    public function getCreatorId(): string
    {
        return $this->entity->getCreatorId();
    }

    public function setCreatorId(string $creatorId): self
    {
        $this->entity->setCreatorId($creatorId);

        return $this;
    }

    #[Length(max: 128)]
    #[NotBlank]
    public function getName(): string
    {
        return $this->entity->getName();
    }

    public function setName(string $name): self
    {
        $this->entity->setName($name);

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 256)]
    public function getFormerly(): array
    {
        return PackedStringList::unpack($this->entity->getFormerly());
    }

    /**
     * @param list<string> $formerly
     */
    public function setFormerly(array $formerly): self
    {
        $this->entity->setFormerly(PackedStringList::pack($formerly));

        return $this;
    }

    #[Length(max: 1024)]
    public function getIntro(): string
    {
        return $this->entity->getIntro();
    }

    public function setIntro(string $intro): self
    {
        $this->entity->setIntro($intro);

        return $this;
    }

    #[Length(max: 16)]
    public function getSince(): string
    {
        return $this->entity->getSince();
    }

    public function setSince(string $since): self
    {
        $this->entity->setSince($since);

        return $this;
    }

    #[Length(max: 128)]
    #[NotBlank(groups: [Validation::GRP_DATA])]
    public function getCountry(): string
    {
        return $this->entity->getCountry();
    }

    public function setCountry(string $country): self
    {
        $this->entity->setCountry($country);

        return $this;
    }

    #[Length(max: 128)]
    public function getState(): string
    {
        return $this->entity->getState();
    }

    public function setState(string $state): self
    {
        $this->entity->setState($state);

        return $this;
    }

    #[Length(max: 128)]
    public function getCity(): string
    {
        return $this->entity->getCity();
    }

    public function setCity(string $city): self
    {
        $this->entity->setCity($city);

        return $this;
    }

    #[Length(max: 1024)]
    public function getProductionModelsComment(): string
    {
        return $this->entity->getProductionModelsComment();
    }

    public function setProductionModelsComment(string $productionModelsComment): self
    {
        $this->entity->setProductionModelsComment($productionModelsComment);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getProductionModels(): array
    {
        return PartialCreatorValueListAccessor::get($this, Field::PRODUCTION_MODELS);
    }

    /**
     * @param list<string> $productionModels
     */
    public function setProductionModels(array $productionModels): self
    {
        PartialCreatorValueListAccessor::set($this, Field::PRODUCTION_MODELS, $productionModels);

        return $this;
    }

    #[Length(max: 1024)]
    public function getStylesComment(): string
    {
        return $this->entity->getStylesComment();
    }

    public function setStylesComment(string $stylesComment): self
    {
        $this->entity->setStylesComment($stylesComment);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getStyles(): array
    {
        return PartialCreatorValueListAccessor::get($this, Field::STYLES);
    }

    /**
     * @param list<string> $styles
     */
    public function setStyles(array $styles): self
    {
        PartialCreatorValueListAccessor::set($this, Field::STYLES, $styles);

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 4096)]
    public function getOtherStyles(): array
    {
        return PartialCreatorValueListAccessor::get($this, Field::OTHER_STYLES);
    }

    /**
     * @param list<string> $otherStyles
     */
    public function setOtherStyles(array $otherStyles): self
    {
        PartialCreatorValueListAccessor::set($this, Field::OTHER_STYLES, $otherStyles);

        return $this;
    }

    #[Length(max: 1024)]
    public function getOrderTypesComment(): string
    {
        return $this->entity->getOrderTypesComment();
    }

    public function setOrderTypesComment(string $orderTypesComment): self
    {
        $this->entity->setOrderTypesComment($orderTypesComment);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getOrderTypes(): array
    {
        return PartialCreatorValueListAccessor::get($this, Field::ORDER_TYPES);
    }

    /**
     * @param list<string> $orderTypes
     */
    public function setOrderTypes(array $orderTypes): self
    {
        PartialCreatorValueListAccessor::set($this, Field::ORDER_TYPES, $orderTypes);

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 4096)]
    public function getOtherOrderTypes(): array
    {
        return PartialCreatorValueListAccessor::get($this, Field::OTHER_ORDER_TYPES);
    }

    /**
     * @param list<string> $otherOrderTypes
     */
    public function setOtherOrderTypes(array $otherOrderTypes): self
    {
        PartialCreatorValueListAccessor::set($this, Field::OTHER_ORDER_TYPES, $otherOrderTypes);

        return $this;
    }

    #[Length(max: 1024)]
    public function getFeaturesComment(): string
    {
        return $this->entity->getFeaturesComment();
    }

    public function setFeaturesComment(string $featuresComment): self
    {
        $this->entity->setFeaturesComment($featuresComment);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getFeatures(): array
    {
        return PartialCreatorValueListAccessor::get($this, Field::FEATURES);
    }

    /**
     * @param list<string> $features
     */
    public function setFeatures(array $features): self
    {
        PartialCreatorValueListAccessor::set($this, Field::FEATURES, $features);

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 4096)]
    public function getOtherFeatures(): array
    {
        return PartialCreatorValueListAccessor::get($this, Field::OTHER_FEATURES);
    }

    /**
     * @param list<string> $otherFeatures
     */
    public function setOtherFeatures(array $otherFeatures): self
    {
        PartialCreatorValueListAccessor::set($this, Field::OTHER_FEATURES, $otherFeatures);

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getPaymentPlans(): array
    {
        return PackedStringList::unpack($this->entity->getPaymentPlans());
    }

    /**
     * @param list<string> $paymentPlans
     */
    public function setPaymentPlans(array $paymentPlans): self
    {
        $this->entity->setPaymentPlans(PackedStringList::pack($paymentPlans));

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getPaymentMethods(): array
    {
        return PackedStringList::unpack($this->entity->getPaymentMethods());
    }

    /**
     * @param list<string> $paymentMethods
     */
    public function setPaymentMethods(array $paymentMethods): self
    {
        $this->entity->setPaymentMethods(PackedStringList::pack($paymentMethods));

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 64)]
    public function getCurrenciesAccepted(): array
    {
        return PackedStringList::unpack($this->entity->getCurrenciesAccepted());
    }

    /**
     * @param list<string> $currenciesAccepted
     */
    public function setCurrenciesAccepted(array $currenciesAccepted): self
    {
        $this->entity->setCurrenciesAccepted(PackedStringList::pack($currenciesAccepted));

        return $this;
    }

    #[Length(max: 1024)]
    public function getSpeciesComment(): string
    {
        return $this->entity->getSpeciesComment();
    }

    public function setSpeciesComment(string $speciesComment): self
    {
        $this->entity->setSpeciesComment($speciesComment);

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getSpeciesDoes(): array
    {
        return PackedStringList::unpack($this->entity->getSpeciesDoes());
    }

    /**
     * @param list<string> $speciesDoes
     */
    public function setSpeciesDoes(array $speciesDoes): self
    {
        $this->entity->setSpeciesDoes(PackedStringList::pack($speciesDoes));

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getSpeciesDoesnt(): array
    {
        return PackedStringList::unpack($this->entity->getSpeciesDoesnt());
    }

    /**
     * @param list<string> $speciesDoesnt
     */
    public function setSpeciesDoesnt(array $speciesDoesnt): self
    {
        $this->entity->setSpeciesDoesnt(PackedStringList::pack($speciesDoesnt));

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getLanguages(): array
    {
        return PartialCreatorValueListAccessor::get($this, Field::LANGUAGES);
    }

    /**
     * @param list<string> $languages
     */
    public function setLanguages(array $languages): self
    {
        PartialCreatorValueListAccessor::set($this, Field::LANGUAGES, $languages);

        return $this;
    }

    #[Length(max: 4096)]
    public function getNotes(): string
    {
        return $this->entity->getNotes();
    }

    public function setNotes(string $notes): self
    {
        $this->entity->setNotes($notes);

        return $this;
    }

    #[Length(max: 512)]
    public function getInactiveReason(): string
    {
        return $this->entity->getInactiveReason();
    }

    public function setInactiveReason(string $inactiveReason): self
    {
        $this->entity->setInactiveReason($inactiveReason);

        return $this;
    }

    #[NotNull(groups: [Validation::GRP_CONTACT_AND_PASSWORD])]
    public function getContactAllowed(): ?ContactPermit
    {
        return $this->entity->getContactAllowed();
    }

    public function setContactAllowed(?ContactPermit $contactAllowed): self
    {
        $this->entity->setContactAllowed($contactAllowed);

        return $this;
    }

    public function getVolatileData(): CreatorVolatileData
    {
        if (null === ($res = $this->entity->getVolatileData())) {
            $this->entity->setVolatileData($res = new CreatorVolatileData());
        }

        return $res;
    }

    public function setVolatileData(?CreatorVolatileData $volatileData): self
    {
        $this->entity->setVolatileData($volatileData);

        return $this;
    }

    #[Valid]
    public function getPrivateData(): CreatorPrivateData
    {
        if (null === ($res = $this->entity->getPrivateData())) {
            $this->entity->setPrivateData($res = new CreatorPrivateData());
        }

        return $res;
    }

    public function setPrivateData(?CreatorPrivateData $privateData): self
    {
        $this->entity->setPrivateData($privateData);

        return $this;
    }

    public function addCreatorId(CreatorId|string $creatorId): self
    {
        $this->entity->addCreatorId($creatorId);

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

    /** @noinspection PhpReturnValueOfMethodIsNeverUsedInspection */
    private function setDateTimeValue(Field $field, ?DateTimeImmutable $newValue): self
    {
        if (null !== $newValue) {
            $newValue = StrUtils::asStr($newValue);
        }

        return $this->setStringValue($field, $newValue);
    }

    private function getStringValue(Field $field): ?string
    {
        foreach ($this->entity->getValues() as $value) {
            if ($value->getFieldName() === $field->value) {
                return $value->getValue();
            }
        }

        return null;
    }

    private function setStringValue(Field $field, ?string $newValue): self
    {
        foreach ($this->entity->getValues() as $value) {
            if ($value->getFieldName() === $field->value) {
                if (null === $newValue) {
                    $this->entity->getValues()->removeElement($value);
                } else {
                    $value->setValue($newValue);
                }

                return $this;
            }
        }

        if (null !== $newValue) {
            $newEntity = new CreatorValue()
                ->setFieldName($field->value)
                ->setValue($newValue);
            $this->entity->addValue($newEntity);
        }

        return $this;
    }
}
