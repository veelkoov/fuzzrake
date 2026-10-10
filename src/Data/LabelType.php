<?php

declare(strict_types=1);

namespace App\Data;

enum LabelType: string
{
    case PRODUCT_VERIFIED = LabelSubject::PRODUCT->value.'_VERIFIED';
    case OFFER_VERIFIED = LabelSubject::OFFER->value.'_VERIFIED';
    case PRODUCT_VERIFIED_BEFORE_2026 = LabelSubject::PRODUCT->value.'_VERIFIED_BEFORE_2026';
    case OFFER_VERIFIED_BEFORE_2026 = LabelSubject::OFFER->value.'_VERIFIED_BEFORE_2026';

    case CREATOR_PENDING_REVIEW = LabelSubject::CREATOR->value.'_PENDING_REVIEW';
    case CREATOR_GOT_3_REVIEWS = LabelSubject::CREATOR->value.'_GOT_3_REVIEWS';
    case CREATOR_ADDED_BEFORE_2026 = LabelSubject::CREATOR->value.'_ADDED_BEFORE_2026';

    public function getSubject(): LabelSubject
    {
        return match ($this) {
            self::PRODUCT_VERIFIED,
            self::PRODUCT_VERIFIED_BEFORE_2026 => LabelSubject::PRODUCT,
            self::OFFER_VERIFIED,
            self::OFFER_VERIFIED_BEFORE_2026 => LabelSubject::OFFER,

            self::CREATOR_PENDING_REVIEW,
            self::CREATOR_GOT_3_REVIEWS,
            self::CREATOR_ADDED_BEFORE_2026 => LabelSubject::CREATOR,
        };
    }

    public function isForCreator(): bool
    {
        return LabelSubject::CREATOR === $this->getSubject();
    }

    public function getText(): string
    {
        return match ($this) {
            self::CREATOR_ADDED_BEFORE_2026 => 'Added before 2026',
            self::PRODUCT_VERIFIED_BEFORE_2026, self::OFFER_VERIFIED_BEFORE_2026 => 'Verified before 2026',

            self::PRODUCT_VERIFIED, self::OFFER_VERIFIED => 'Verified',

            self::CREATOR_GOT_3_REVIEWS => 'Received 3 reviews',
            self::CREATOR_PENDING_REVIEW => 'Pending review',
        };
    }
}
