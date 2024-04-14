<?php

declare(strict_types=1);

namespace App\Data\Definitions\Fields;

use App\Utils\FieldReadInterface;
use TRegx\CleanRegex\Pattern;

enum Field: string // Backing by strings gives free ::from() and ::tryFrom()
{
    case MAKER_ID = 'MAKER_ID';
    case FORMER_MAKER_IDS = 'FORMER_MAKER_IDS';
    case NAME = 'NAME';
    case FORMERLY = 'FORMERLY';
    case DATE_ADDED = 'DATE_ADDED';
    case DATE_UPDATED = 'DATE_UPDATED';
    case INTRO = 'INTRO';
    case SINCE = 'SINCE';
    case LANGUAGES = 'LANGUAGES';
    case COUNTRY = 'COUNTRY';
    case STATE = 'STATE';
    case CITY = 'CITY';
    case PRODUCTION_MODELS_COMMENT = 'PRODUCTION_MODELS_COMMENT';
    case PRODUCTION_MODELS = 'PRODUCTION_MODELS';
    case STYLES_COMMENT = 'STYLES_COMMENT';
    case STYLES = 'STYLES';
    case OTHER_STYLES = 'OTHER_STYLES';
    case ORDER_TYPES_COMMENT = 'ORDER_TYPES_COMMENT';
    case ORDER_TYPES = 'ORDER_TYPES';
    case OTHER_ORDER_TYPES = 'OTHER_ORDER_TYPES';
    case FEATURES_COMMENT = 'FEATURES_COMMENT';
    case FEATURES = 'FEATURES';
    case OTHER_FEATURES = 'OTHER_FEATURES';
    case PAYMENT_PLANS = 'PAYMENT_PLANS';
    case PAYMENT_METHODS = 'PAYMENT_METHODS';
    case CURRENCIES_ACCEPTED = 'CURRENCIES_ACCEPTED';
    case SPECIES_COMMENT = 'SPECIES_COMMENT';
    case SPECIES_DOES = 'SPECIES_DOES';
    case SPECIES_DOESNT = 'SPECIES_DOESNT';
    case IS_MINOR = 'IS_MINOR'; // TODO: Remove https://github.com/veelkoov/fuzzrake/issues/103
    case AGES = 'AGES';
    case NSFW_WEBSITE = 'NSFW_WEBSITE';
    case NSFW_SOCIAL = 'NSFW_SOCIAL';
    case DOES_NSFW = 'DOES_NSFW';
    case SAFE_DOES_NSFW = 'SAFE_DOES_NSFW';
    case WORKS_WITH_MINORS = 'WORKS_WITH_MINORS';
    case SAFE_WORKS_WITH_MINORS = 'SAFE_WORKS_WITH_MINORS';
    case URL_FURSUITREVIEW = 'URL_FURSUITREVIEW';
    case URL_WEBSITE = 'URL_WEBSITE';
    case URL_PRICES = 'URL_PRICES';
    case URL_COMMISSIONS = 'URL_COMMISSIONS';
    case URL_FAQ = 'URL_FAQ';
    case URL_FUR_AFFINITY = 'URL_FUR_AFFINITY';
    case URL_DEVIANTART = 'URL_DEVIANTART';
    case URL_MASTODON = 'URL_MASTODON';
    case URL_TWITTER = 'URL_TWITTER';
    case URL_FACEBOOK = 'URL_FACEBOOK';
    case URL_TUMBLR = 'URL_TUMBLR';
    case URL_INSTAGRAM = 'URL_INSTAGRAM';
    case URL_YOUTUBE = 'URL_YOUTUBE';
    case URL_LINKLIST = 'URL_LINKLIST';
    case URL_FURRY_AMINO = 'URL_FURRY_AMINO';
    case URL_ETSY = 'URL_ETSY';
    case URL_THE_DEALERS_DEN = 'URL_THE_DEALERS_DEN';
    case URL_OTHER_SHOP = 'URL_OTHER_SHOP';
    case URL_QUEUE = 'URL_QUEUE';
    case URL_SCRITCH = 'URL_SCRITCH';
    case URL_FURTRACK = 'URL_FURTRACK';
    case URL_PHOTOS = 'URL_PHOTOS';
    case URL_MINIATURES = 'URL_MINIATURES';
    case URL_OTHER = 'URL_OTHER';
    case NOTES = 'NOTES';
    case INACTIVE_REASON = 'INACTIVE_REASON';
    case PASSWORD = 'PASSWORD';
    case CS_LAST_CHECK = 'CS_LAST_CHECK';
    case CS_TRACKER_ISSUE = 'CS_TRACKER_ISSUE';
    case OPEN_FOR = 'OPEN_FOR';
    case CLOSED_FOR = 'CLOSED_FOR';
    case COMPLETENESS = 'COMPLETENESS';
    case CONTACT_ALLOWED = 'CONTACT_ALLOWED';
    case CONTACT_METHOD = 'CONTACT_METHOD';
    case CONTACT_ADDRESS_PLAIN = 'CONTACT_ADDRESS_PLAIN';
    case CONTACT_INFO_OBFUSCATED = 'CONTACT_INFO_OBFUSCATED';
    case CONTACT_INFO_ORIGINAL = 'CONTACT_INFO_ORIGINAL';

    public function modelName(): string
    {
        return FieldsData::getModelName($this->value);
    }

    public function getType(): Type
    {
        return FieldsData::getType($this->value);
    }

    public function validationPattern(): ?Pattern
    {
        return FieldsData::getValidationPattern($this->value);
    }

    public function isList(): bool
    {
        return Type::STR_LIST === $this->getType();
    }

    public function isDate(): bool
    {
        return Type::DATE === $this->getType();
    }

    public function isPersisted(): bool
    {
        return FieldsData::isPersisted($this->value);
    }

    public function isValidated(): bool
    {
        return FieldsData::isValidated($this->value);
    }

    public function isInIuForm(): bool
    {
        return FieldsData::isInIuForm($this->value);
    }

    public function affectedByIuForm(): bool
    {
        return FieldsData::isAffectedByIuForm($this->value);
    }

    public function notInspectedUrl(): bool
    {
        return FieldsData::isNotInspectedUrl($this->value);
    }

    public function public(): bool
    {
        return FieldsData::isPublic($this->value);
    }

    public function inStats(): bool
    {
        return FieldsData::isInStats($this->value);
    }

    public function isFreeForm(): bool
    {
        return FieldsData::isFreeForm($this->value);
    }

    public function isProvided(mixed $value): bool
    {
        return null !== $value && '' !== $value;
    }

    public function providedIn(FieldReadInterface $source): bool
    {
        return $this->isProvided($source->get($this));
    }
}
