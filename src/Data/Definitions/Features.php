<?php

declare(strict_types=1);

namespace App\Data\Definitions;

use Override;

final class Features extends Dictionary
{
    public const string FOLLOW_ME_EYES = 'Follow-me eyes';
    public const string OUTDOOR_FEET = 'Outdoor feet';
    public const string INDOOR_FEET = 'Indoor feet';
    public const string ATTACHED_TAIL = 'Attached tail';
    public const string EXCHANGEABLE_TONGUES = 'Exchangeable tongues';
    public const string REMOVABLE_EYELIDS = 'Removable eyelids';
    public const string MOVABLE_JAW = 'Movable jaw';
    public const string ATTACHED_HANDPAWS_AND_FEETPAWS = 'Attached handpaws and feetpaws';
    public const string IN_HEAD_FANS = 'In-head fans';
    public const string LED_EYES = 'LED eyes';
    public const string EXCHANGEABLE_HAIRS = 'Exchangeable hairs';
    public const string REMOVABLE_HORNS_ANTLERS = 'Removable horns/antlers';
    public const string WASHABLE_HEADS = 'Washable heads';
    public const string LED_EL_LIGHTS = 'LED/EL lights';
    public const string ADJUSTABLE_WIGGLE_EARS = 'Adjustable/wiggle ears';
    public const string ADJUSTABLE_EYEBROWS = 'Adjustable eyebrows';
    public const string ELECTRONICS_ANIMATRONICS = 'Electronics/animatronics';
    public const string REMOVABLE_BLUSH = 'Removable blush';

    #[Override]
    public static function getValues(): array
    {
        return [
            self::FOLLOW_ME_EYES,
            self::OUTDOOR_FEET,
            self::INDOOR_FEET,
            self::ATTACHED_TAIL,
            self::EXCHANGEABLE_TONGUES,
            self::REMOVABLE_EYELIDS,
            self::MOVABLE_JAW,
            self::ATTACHED_HANDPAWS_AND_FEETPAWS,
            self::IN_HEAD_FANS,
            self::LED_EYES,
            self::EXCHANGEABLE_HAIRS,
            self::REMOVABLE_HORNS_ANTLERS,
            self::WASHABLE_HEADS,
            self::LED_EL_LIGHTS,
            self::ADJUSTABLE_WIGGLE_EARS,
            self::ADJUSTABLE_EYEBROWS,
            self::ELECTRONICS_ANIMATRONICS,
            self::REMOVABLE_BLUSH,
        ];
    }
}
