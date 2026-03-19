<?php

declare(strict_types=1);

namespace App\Data\Submission;

enum Status: string
{
    public const array ACTION_REQUIRED = [
        self::NEW,
        self::OTHER,
    ];

    case NEW = 'NEW';
    case AWAITING_RESPONSE = 'AWAITING_RESPONSE';
    case REPLACED = 'REPLACED';
    case IMPORTED = 'IMPORTED';
    case REJECTED = 'REJECTED';
    case OTHER = 'OTHER';

    public function getLabel(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::AWAITING_RESPONSE => 'Awaiting response',
            self::REPLACED => 'Replaced',
            self::IMPORTED => 'Imported',
            self::REJECTED => 'Rejected',
            self::OTHER => 'Other',
        };
    }

    public function isActionRequired(): bool
    {
        return arr_contains(self::ACTION_REQUIRED, $this);
    }
}
