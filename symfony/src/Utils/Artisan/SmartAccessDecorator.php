<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Data\Definitions\Fields\Fields;
use App\Data\Definitions\Fields\FieldsList;
use App\Data\Definitions\Fields\Validation;
use App\Data\FieldValue;
use App\Entity\Artisan as ArtisanE;
use App\Entity\ArtisanValue;
use App\Entity\ArtisanVolatileData;
use App\Entity\CreatorPrivateData;
use App\Entity\MakerId;
use App\Utils\Contact;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\DateTimeUtils;
use App\Utils\DateTime\UtcClock;
use App\Utils\Enforce;
use App\Utils\FieldReadInterface;
use App\Utils\PackedStringList;
use App\Utils\Parse;
use App\Utils\StringList;
use App\Utils\StrUtils;
use App\Validator\StrListLength;
use DateTimeImmutable;
use InvalidArgumentException;
use JsonSerializable;
use LogicException;
use Override;
use Psl\Dict;
use Psl\Iter;
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
    private ArtisanE $artisan;

    public function __construct(ArtisanE $artisan = null)
    {
        $this->artisan = $artisan ?? new ArtisanE();
    }

    public static function new(): self
    {
        return new self();
    }

    public function __clone()
    {
        $this->artisan = clone $this->artisan;
    }

    #[Override]
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

        return call_user_func($callback);
    }

    public function equals(Field $field, self $other): bool // TODO: Improve https://github.com/veelkoov/fuzzrake/issues/221
    {
        if ($field->isList()) {
            return StringList::sameElements($this->getStringList($field), $other->getStringList($field));
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
    // ===== MAKER ID HELPERS =====
    //

    public function getLastMakerId(): string
    {
        return $this->artisan->getMakerId() ?: Iter\first($this->getFormerMakerIds()) ?: throw new LogicException('Maker does not have any maker ID');
    }

    public function hasMakerId(string $makerId): bool
    {
        return in_array($makerId, $this->artisan->getMakerIds()
            ->map(fn (MakerId $makerId): ?string => $makerId->getMakerId())
            ->toArray(), true);
    }

    /**
     * @param list<string> $formerMakerIdsToSet
     */
    public function setFormerMakerIds(array $formerMakerIdsToSet): self
    {
        $allMakerIdsToSet = [...$formerMakerIdsToSet, $this->artisan->getMakerId()];

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
     * Assume that values come from setting the maker ID, which is validated independently.
     *
     * @return list<string>
     */
    public function getFormerMakerIds(): array
    {
        return array_values(array_filter($this->artisan->getMakerIds()
            ->map(fn (MakerId $makerId): ?string => $makerId->getMakerId())
            ->filter(fn (?string $makerId): bool => $makerId !== $this->getMakerId())
            ->toArray()));
    }

    /**
     * @return list<string>
     */
    public function getAllMakerIds(): array
    {
        return array_values(array_filter([$this->artisan->getMakerId(), ...$this->getFormerMakerIds()]));
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
     * @return list<string>
     */
    public function getAllNames(): array
    {
        return array_values(array_filter([$this->getName(), ...$this->getFormerly()]));
    }

    public function getCompleteness(): int
    {
        return CompletenessCalc::count($this);
    }

    public function allowsFeedback(): bool
    {
        return ContactPermit::FEEDBACK === $this->artisan->getContactAllowed();
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

    public function updateContact(string $newOriginalContactValue): self
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

        return $this;
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

    public function getContactAddressPlain(): string
    {
        return $this->getPrivateData()->getContactAddress();
    }

    public function setContactAddressPlain(string $contactAddressPlain): self
    {
        $this->getPrivateData()->setContactAddress($contactAddressPlain);

        return $this;
    }

    /**
     * Validated by obfuscated contact info.
     */
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
     * @param list<string> $photoUrls
     */
    public function setPhotoUrls(array $photoUrls): self
    {
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
     * @param list<string> $scritchMiniatureUrls
     */
    public function setMiniatureUrls(array $scritchMiniatureUrls): self
    {
        return $this->setUrls(Field::URL_MINIATURES, $scritchMiniatureUrls);
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
     * @return array<string, psJsonFieldValue>
     */
    private function getValuesForJson(FieldsList $fields): array
    {
        return Dict\map($fields, fn (Field $field) => match ($field) { // @phpstan-ignore-line FIXME
            Field::COMPLETENESS => $this->getCompleteness(),
            Field::CS_LAST_CHECK => StrUtils::asStr($this->getCsLastCheck()),
            Field::DATE_ADDED => StrUtils::asStr($this->getDateAdded()),
            Field::DATE_UPDATED => StrUtils::asStr($this->getDateUpdated()),
            default => $this->get($field),
        });
    }

    /**
     * @return array<string, psJsonFieldValue>
     */
    public function getPublicData(): array
    {
        return $this->getValuesForJson(Fields::public());
    }

    /**
     * @return array<string, psJsonFieldValue>
     */
    public function getAllData(): array
    {
        return $this->getValuesForJson(Fields::all());
    }

    /**
     * @return array<string, psJsonFieldValue>
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
    public function validateContactAndPassword(ExecutionContextInterface $context, mixed $payload): void
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

    /**
     * @return list<string>
     */
    #[StrListLength(max: 256)]
    public function getFormerly(): array
    {
        return PackedStringList::unpack($this->artisan->getFormerly());
    }

    /**
     * @param list<string> $formerly
     */
    public function setFormerly(array $formerly): self
    {
        $this->artisan->setFormerly(PackedStringList::pack($formerly));

        return $this;
    }

    #[Length(max: 1024)]
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

    #[Length(max: 128)]
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

    #[Length(max: 128)]
    public function getState(): string
    {
        return $this->artisan->getState();
    }

    public function setState(string $state): self
    {
        $this->artisan->setState($state);

        return $this;
    }

    #[Length(max: 128)]
    public function getCity(): string
    {
        return $this->artisan->getCity();
    }

    public function setCity(string $city): self
    {
        $this->artisan->setCity($city);

        return $this;
    }

    #[Length(max: 1024)]
    public function getProductionModelsComment(): string
    {
        return $this->artisan->getProductionModelsComment();
    }

    public function setProductionModelsComment(string $productionModelsComment): self
    {
        $this->artisan->setProductionModelsComment($productionModelsComment);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getProductionModels(): array
    {
        return PackedStringList::unpack($this->artisan->getProductionModels());
    }

    /**
     * @param list<string> $productionModels
     */
    public function setProductionModels(array $productionModels): self
    {
        $this->artisan->setProductionModels(PackedStringList::pack($productionModels));

        return $this;
    }

    #[Length(max: 1024)]
    public function getStylesComment(): string
    {
        return $this->artisan->getStylesComment();
    }

    public function setStylesComment(string $stylesComment): self
    {
        $this->artisan->setStylesComment($stylesComment);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getStyles(): array
    {
        return PackedStringList::unpack($this->artisan->getStyles());
    }

    /**
     * @param list<string> $styles
     */
    public function setStyles(array $styles): self
    {
        $this->artisan->setStyles(PackedStringList::pack($styles));

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 4096)]
    public function getOtherStyles(): array
    {
        return PackedStringList::unpack($this->artisan->getOtherStyles());
    }

    /**
     * @param list<string> $otherStyles
     */
    public function setOtherStyles(array $otherStyles): self
    {
        $this->artisan->setOtherStyles(PackedStringList::pack($otherStyles));

        return $this;
    }

    #[Length(max: 1024)]
    public function getOrderTypesComment(): string
    {
        return $this->artisan->getOrderTypesComment();
    }

    public function setOrderTypesComment(string $orderTypesComment): self
    {
        $this->artisan->setOrderTypesComment($orderTypesComment);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getOrderTypes(): array
    {
        return PackedStringList::unpack($this->artisan->getOrderTypes());
    }

    /**
     * @param list<string> $orderTypes
     */
    public function setOrderTypes(array $orderTypes): self
    {
        $this->artisan->setOrderTypes(PackedStringList::pack($orderTypes));

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 4096)]
    public function getOtherOrderTypes(): array
    {
        return PackedStringList::unpack($this->artisan->getOtherOrderTypes());
    }

    /**
     * @param list<string> $otherOrderTypes
     */
    public function setOtherOrderTypes(array $otherOrderTypes): self
    {
        $this->artisan->setOtherOrderTypes(PackedStringList::pack($otherOrderTypes));

        return $this;
    }

    #[Length(max: 1024)]
    public function getFeaturesComment(): string
    {
        return $this->artisan->getFeaturesComment();
    }

    public function setFeaturesComment(string $featuresComment): self
    {
        $this->artisan->setFeaturesComment($featuresComment);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getFeatures(): array
    {
        return PackedStringList::unpack($this->artisan->getFeatures());
    }

    /**
     * @param list<string> $features
     */
    public function setFeatures(array $features): self
    {
        $this->artisan->setFeatures(PackedStringList::pack($features));

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 4096)]
    public function getOtherFeatures(): array
    {
        return PackedStringList::unpack($this->artisan->getOtherFeatures());
    }

    /**
     * @param list<string> $otherFeatures
     */
    public function setOtherFeatures(array $otherFeatures): self
    {
        $this->artisan->setOtherFeatures(PackedStringList::pack($otherFeatures));

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getPaymentPlans(): array
    {
        return PackedStringList::unpack($this->artisan->getPaymentPlans());
    }

    /**
     * @param list<string> $paymentPlans
     */
    public function setPaymentPlans(array $paymentPlans): self
    {
        $this->artisan->setPaymentPlans(PackedStringList::pack($paymentPlans));

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getPaymentMethods(): array
    {
        return PackedStringList::unpack($this->artisan->getPaymentMethods());
    }

    /**
     * @param list<string> $paymentMethods
     */
    public function setPaymentMethods(array $paymentMethods): self
    {
        $this->artisan->setPaymentMethods(PackedStringList::pack($paymentMethods));

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 64)]
    public function getCurrenciesAccepted(): array
    {
        return PackedStringList::unpack($this->artisan->getCurrenciesAccepted());
    }

    /**
     * @param list<string> $currenciesAccepted
     */
    public function setCurrenciesAccepted(array $currenciesAccepted): self
    {
        $this->artisan->setCurrenciesAccepted(PackedStringList::pack($currenciesAccepted));

        return $this;
    }

    #[Length(max: 1024)]
    public function getSpeciesComment(): string
    {
        return $this->artisan->getSpeciesComment();
    }

    public function setSpeciesComment(string $speciesComment): self
    {
        $this->artisan->setSpeciesComment($speciesComment);

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getSpeciesDoes(): array
    {
        return PackedStringList::unpack($this->artisan->getSpeciesDoes());
    }

    /**
     * @param list<string> $speciesDoes
     */
    public function setSpeciesDoes(array $speciesDoes): self
    {
        $this->artisan->setSpeciesDoes(PackedStringList::pack($speciesDoes));

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getSpeciesDoesnt(): array
    {
        return PackedStringList::unpack($this->artisan->getSpeciesDoesnt());
    }

    /**
     * @param list<string> $speciesDoesnt
     */
    public function setSpeciesDoesnt(array $speciesDoesnt): self
    {
        $this->artisan->setSpeciesDoesnt(PackedStringList::pack($speciesDoesnt));

        return $this;
    }

    /**
     * @return list<string>
     */
    #[StrListLength(max: 1024)]
    public function getLanguages(): array
    {
        return PackedStringList::unpack($this->artisan->getLanguages());
    }

    /**
     * @param list<string> $languages
     */
    public function setLanguages(array $languages): self
    {
        $this->artisan->setLanguages(PackedStringList::pack($languages));

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

    #[NotNull(groups: [Validation::GRP_CONTACT_AND_PASSWORD])]
    public function getContactAllowed(): ?ContactPermit
    {
        return $this->artisan->getContactAllowed();
    }

    public function setContactAllowed(?ContactPermit $contactAllowed): self
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
    public function getPrivateData(): CreatorPrivateData
    {
        if (null === ($res = $this->artisan->getPrivateData())) {
            $this->artisan->setPrivateData($res = new CreatorPrivateData());
        }

        return $res;
    }

    public function setPrivateData(?CreatorPrivateData $privateData): self
    {
        $this->artisan->setPrivateData($privateData);

        return $this;
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
        foreach ($this->artisan->getValues() as $value) {
            if ($value->getFieldName() === $field->value) {
                return $value->getValue();
            }
        }

        return null;
    }

    private function setStringValue(Field $field, ?string $newValue): self
    {
        foreach ($this->artisan->getValues() as $value) {
            if ($value->getFieldName() === $field->value) {
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
                ->setFieldName($field->value)
                ->setValue($newValue);
            $this->artisan->addValue($newEntity);
        }

        return $this;
    }
}
