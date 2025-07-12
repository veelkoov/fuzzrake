<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use App\Data\Definitions\Fields\Properties as Props;
use App\Data\Definitions\Fields\ValidationRegexps as V;
use App\Data\FieldValue;
use App\Utils\Creator\CreatorId;
use App\Utils\FieldReadInterface;

enum Field: string // Backing by strings gives free ::from() and ::tryFrom()
{
    #[Props('creatorId', validationRegex: CreatorId::VALID_REGEX)]
    case MAKER_ID = 'MAKER_ID';

    #[Props('formerCreatorIds', type: Type::STR_LIST, inIuForm: false, freeForm: false, validationRegex: V::FORMER_CREATOR_IDS, affectedByIuForm: true)]
    case FORMER_MAKER_IDS = 'FORMER_MAKER_IDS';

    #[Props('name', validationRegex: V::NON_EMPTY)]
    case NAME = 'NAME';

    #[Props('formerly', type: Type::STR_LIST)]
    case FORMERLY = 'FORMERLY';

    #[Props('dateAdded', type: Type::DATE, inIuForm: false, inStats: false, freeForm: false, affectedByIuForm: true)]
    case DATE_ADDED = 'DATE_ADDED';

    #[Props('dateUpdated', type: Type::DATE, inIuForm: false, inStats: false, freeForm: false, affectedByIuForm: true)]
    case DATE_UPDATED = 'DATE_UPDATED';

    #[Props('intro')]
    case INTRO = 'INTRO';

    #[Props('since', freeForm: false, validationRegex: V::SINCE)]
    case SINCE = 'SINCE';

    #[Props('languages', type: Type::STR_LIST)]
    case LANGUAGES = 'LANGUAGES';

    #[Props('country', freeForm: false, validationRegex: V::COUNTRY)]
    case COUNTRY = 'COUNTRY';

    #[Props('state', validationRegex: V::STATE)]
    case STATE = 'STATE';

    #[Props('city')]
    case CITY = 'CITY';

    #[Props('productionModelsComment', inStats: false)]
    case PRODUCTION_MODELS_COMMENT = 'PRODUCTION_MODELS_COMMENT';

    #[Props('productionModels', type: Type::STR_LIST, freeForm: false, validationRegex: V::LIST_VALIDATION)]
    case PRODUCTION_MODELS = 'PRODUCTION_MODELS';

    #[Props('stylesComment', inStats: false)]
    case STYLES_COMMENT = 'STYLES_COMMENT';

    #[Props('styles', type: Type::STR_LIST, freeForm: false, validationRegex: V::LIST_VALIDATION)]
    case STYLES = 'STYLES';

    #[Props('otherStyles', type: Type::STR_LIST, validationRegex: V::LIST_VALIDATION)]
    case OTHER_STYLES = 'OTHER_STYLES';

    #[Props('orderTypesComment', inStats: false)]
    case ORDER_TYPES_COMMENT = 'ORDER_TYPES_COMMENT';

    #[Props('orderTypes', type: Type::STR_LIST, freeForm: false, validationRegex: V::LIST_VALIDATION)]
    case ORDER_TYPES = 'ORDER_TYPES';

    #[Props('otherOrderTypes', type: Type::STR_LIST, validationRegex: V::LIST_VALIDATION)]
    case OTHER_ORDER_TYPES = 'OTHER_ORDER_TYPES';

    #[Props('featuresComment', inStats: false)]
    case FEATURES_COMMENT = 'FEATURES_COMMENT';

    #[Props('features', type: Type::STR_LIST, freeForm: false, validationRegex: V::LIST_VALIDATION)]
    case FEATURES = 'FEATURES';

    #[Props('otherFeatures', type: Type::STR_LIST, validationRegex: V::LIST_VALIDATION)]
    case OTHER_FEATURES = 'OTHER_FEATURES';

    #[Props('paymentPlans', type: Type::STR_LIST)]
    case PAYMENT_PLANS = 'PAYMENT_PLANS';

    #[Props('paymentMethods', type: Type::STR_LIST, validationRegex: V::PAY_METHODS)]
    case PAYMENT_METHODS = 'PAYMENT_METHODS';

    #[Props('currenciesAccepted', type: Type::STR_LIST, validationRegex: V::CURRENCIES)]
    case CURRENCIES_ACCEPTED = 'CURRENCIES_ACCEPTED';

    #[Props('speciesComment')]
    case SPECIES_COMMENT = 'SPECIES_COMMENT';

    #[Props('speciesDoes', type: Type::STR_LIST)]
    case SPECIES_DOES = 'SPECIES_DOES';

    #[Props('speciesDoesnt', type: Type::STR_LIST)]
    case SPECIES_DOESNT = 'SPECIES_DOESNT';

    #[Props('ages', freeForm: false)]
    case AGES = 'AGES';

    #[Props('nsfwWebsite', type: Type::BOOLEAN, inStats: false, freeForm: false)]
    case NSFW_WEBSITE = 'NSFW_WEBSITE';

    #[Props('nsfwSocial', type: Type::BOOLEAN, inStats: false, freeForm: false)]
    case NSFW_SOCIAL = 'NSFW_SOCIAL';

    #[Props('doesNsfw', type: Type::BOOLEAN, inStats: false, freeForm: false)]
    case DOES_NSFW = 'DOES_NSFW';

    #[Props('safeDoesNsfw', type: Type::BOOLEAN, inIuForm: false, inStats: false, freeForm: false, persisted: false)]
    case SAFE_DOES_NSFW = 'SAFE_DOES_NSFW';

    #[Props('worksWithMinors', type: Type::BOOLEAN, public: false, inStats: false, freeForm: false)]
    case WORKS_WITH_MINORS = 'WORKS_WITH_MINORS';

    #[Props('safeWorksWithMinors', type: Type::BOOLEAN, inIuForm: false, inStats: false, freeForm: false, persisted: false)]
    case SAFE_WORKS_WITH_MINORS = 'SAFE_WORKS_WITH_MINORS';

    #[Props('fursuitReviewUrl', validationRegex: V::FSR_URL)]
    case URL_FURSUITREVIEW = 'URL_FURSUITREVIEW';

    #[Props('websiteUrl', validationRegex: V::GENERIC_URL)]
    case URL_WEBSITE = 'URL_WEBSITE';

    #[Props('pricesUrls', type: Type::STR_LIST, validationRegex: V::GENERIC_URL_LIST)]
    case URL_PRICES = 'URL_PRICES';

    #[Props('commissionsUrls', type: Type::STR_LIST, validationRegex: V::GENERIC_URL_LIST)]
    case URL_COMMISSIONS = 'URL_COMMISSIONS';

    #[Props('faqUrl', validationRegex: V::GENERIC_URL)]
    case URL_FAQ = 'URL_FAQ';

    #[Props('furAffinityUrl', validationRegex: V::FA_URL)]
    case URL_FUR_AFFINITY = 'URL_FUR_AFFINITY';

    #[Props('deviantArtUrl', validationRegex: V::DA_URL)]
    case URL_DEVIANTART = 'URL_DEVIANTART';

    #[Props('mastodonUrl', validationRegex: V::GENERIC_URL)]
    case URL_MASTODON = 'URL_MASTODON';

    #[Props('twitterUrl', validationRegex: V::TWITTER_URL)]
    case URL_TWITTER = 'URL_TWITTER';

    #[Props('facebookUrl', validationRegex: V::FACEBOOK_URL)]
    case URL_FACEBOOK = 'URL_FACEBOOK';

    #[Props('tumblrUrl', validationRegex: V::TUMBLR_URL)]
    case URL_TUMBLR = 'URL_TUMBLR';

    #[Props('instagramUrl', validationRegex: V::INSTAGRAM_URL)]
    case URL_INSTAGRAM = 'URL_INSTAGRAM';

    #[Props('youtubeUrl', validationRegex: V::YOUTUBE_URL)]
    case URL_YOUTUBE = 'URL_YOUTUBE';

    #[Props('linklistUrl', validationRegex: V::GENERIC_URL)]
    case URL_LINKLIST = 'URL_LINKLIST';

    #[Props('furryAminoUrl', validationRegex: V::GENERIC_URL)]
    case URL_FURRY_AMINO = 'URL_FURRY_AMINO';

    #[Props('etsyUrl', validationRegex: V::GENERIC_URL)]
    case URL_ETSY = 'URL_ETSY';

    #[Props('theDealersDenUrl', validationRegex: V::GENERIC_URL)]
    case URL_THE_DEALERS_DEN = 'URL_THE_DEALERS_DEN';

    #[Props('otherShopUrl', validationRegex: V::GENERIC_URL)]
    case URL_OTHER_SHOP = 'URL_OTHER_SHOP';

    #[Props('queueUrl', validationRegex: V::GENERIC_URL)]
    case URL_QUEUE = 'URL_QUEUE';

    #[Props('scritchUrl', validationRegex: V::SCRITCH_URL)]
    case URL_SCRITCH = 'URL_SCRITCH';

    #[Props('furtrackUrl', validationRegex: V::FURTRACK_URL)]
    case URL_FURTRACK = 'URL_FURTRACK';

    #[Props('photoUrls', type: Type::STR_LIST, validationRegex: V::PHOTO_URL_LIST, notInspectedUrl: true)]
    case URL_PHOTOS = 'URL_PHOTOS';

    #[Props('miniatureUrls', type: Type::STR_LIST, inIuForm: false, validationRegex: V::MINIATURE_URL_LIST, notInspectedUrl: true)]
    case URL_MINIATURES = 'URL_MINIATURES';

    #[Props('otherUrls', type: Type::STR_LIST, notInspectedUrl: true)]
    case URL_OTHER = 'URL_OTHER'; // TODO: Rename "-s"

    #[Props('blueskyUrl', validationRegex: V::BLUESKY_URL)]
    case URL_BLUESKY = 'URL_BLUESKY';

    #[Props('donationsUrl', validationRegex: V::DONATIONS_URL)]
    case URL_DONATIONS = 'URL_DONATIONS';

    #[Props('telegramChannelUrl', validationRegex: V::TELEGRAM_CHANNEL_URL)]
    case URL_TELEGRAM_CHANNEL = 'URL_TELEGRAM_CHANNEL';

    #[Props('tikTokUrl', validationRegex: V::TIKTOK_URL)]
    case URL_TIKTOK = 'URL_TIKTOK';

    #[Props('notes', inStats: false)]
    case NOTES = 'NOTES';

    #[Props('inactiveReason', inIuForm: false, freeForm: false)]
    case INACTIVE_REASON = 'INACTIVE_REASON';

    #[Props('password', public: false, inStats: false, freeForm: false)]
    case PASSWORD = 'PASSWORD';

    #[Props('csLastCheck', type: Type::DATE, inIuForm: false, inStats: false, freeForm: false)]
    case CS_LAST_CHECK = 'CS_LAST_CHECK';

    #[Props('csTrackerIssue', type: Type::BOOLEAN, inIuForm: false, inStats: false, freeForm: false)]
    case CS_TRACKER_ISSUE = 'CS_TRACKER_ISSUE';

    #[Props('openFor', type: Type::STR_LIST, inIuForm: false, inStats: false, freeForm: false)]
    case OPEN_FOR = 'OPEN_FOR';

    #[Props('closedFor', type: Type::STR_LIST, inIuForm: false, inStats: false, freeForm: false)]
    case CLOSED_FOR = 'CLOSED_FOR';

    #[Props('completeness', inIuForm: false, inStats: false, freeForm: false, persisted: false)]
    case COMPLETENESS = 'COMPLETENESS';

    #[Props('contactAllowed', inStats: false, freeForm: false)]
    case CONTACT_ALLOWED = 'CONTACT_ALLOWED';

    #[Props('emailAddress', public: false, inStats: false, freeForm: false, affectedByIuForm: true)]
    case EMAIL_ADDRESS = 'EMAIL_ADDRESS';

    public function getData(): FieldData
    {
        return FieldsData::get($this);
    }

    public function modelName(): string
    {
        return $this->getData()->modelName;
    }

    /**
     * @return ?non-empty-string
     */
    public function validationPattern(): ?string
    {
        return $this->getData()->validationPattern;
    }

    public function isList(): bool
    {
        return Type::STR_LIST === $this->getData()->type;
    }

    public function isDate(): bool
    {
        return Type::DATE === $this->getData()->type;
    }

    public function isBoolean(): bool
    {
        return Type::BOOLEAN === $this->getData()->type;
    }

    public function isPersisted(): bool
    {
        return $this->getData()->isPersisted;
    }

    public function isValidated(): bool
    {
        return $this->getData()->isValidated;
    }

    public function isInIuForm(): bool
    {
        return $this->getData()->isInIuForm;
    }

    public function affectedByIuForm(): bool
    {
        return $this->getData()->affectedByIuForm;
    }

    public function notInspectedUrl(): bool
    {
        return $this->getData()->notInspectedUrl;
    }

    public function public(): bool
    {
        return $this->getData()->public;
    }

    public function inStats(): bool
    {
        return $this->getData()->inStats;
    }

    public function isFreeForm(): bool
    {
        return $this->getData()->isFreeForm;
    }

    public function providedIn(FieldReadInterface $source): bool
    {
        return FieldValue::isProvided($this, $source->get($this));
    }

    /**
     * @param Field[] $fields
     *
     * @return list<string>
     */
    public static function strings(array $fields): array
    {
        return iter_lmap($fields, static fn (self $field): string => $field->value);
    }
}
