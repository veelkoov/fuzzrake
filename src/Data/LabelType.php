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

    private const array IMPORTANT = [
        self::CREATOR_PENDING_REVIEW,
    ];

    public function isForCreator(): bool
    {
        return LabelSubject::CREATOR === $this->getSubject();
    }

    public function isImportant(): bool
    {
        return arr_contains(self::IMPORTANT, $this);
    }

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

    public function getText(): string
    {
        return match ($this) {
            self::PRODUCT_VERIFIED_BEFORE_2026, self::OFFER_VERIFIED_BEFORE_2026 => 'Verified before 2026',

            self::PRODUCT_VERIFIED, self::OFFER_VERIFIED => 'Verified',

            self::CREATOR_ADDED_BEFORE_2026 => 'Before 2026',
            self::CREATOR_GOT_3_REVIEWS => 'Reviewed 3×',
            self::CREATOR_PENDING_REVIEW => 'Pending review',
        };
    }

    public function getExplanation(): string
    {
        return match ($this) {
            self::CREATOR_PENDING_REVIEW => 'This studio/maker has not yet been reviewed yet.',
            self::CREATOR_ADDED_BEFORE_2026 => 'This studio/maker has only been briefly reviewed, and added to the list before 2026.',
            self::CREATOR_GOT_3_REVIEWS => 'This studio/maker have been checked by 3+ different reviewers during the 2026 reviews experiment.',

            self::PRODUCT_VERIFIED, self::OFFER_VERIFIED, self::PRODUCT_VERIFIED_BEFORE_2026, self::OFFER_VERIFIED_BEFORE_2026 => '', // Not needed
        };
    }
}
