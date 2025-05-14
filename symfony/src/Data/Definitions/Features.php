<?php

declare(strict_types=1);

namespace App\Data\Definitions;

use Override;

class Features extends Dictionary
{
    final public const string FOLLOW_ME_EYES = 'Follow-me eyes';
    final public const string OUTDOOR_FEET = 'Outdoor feet';
    final public const string INDOOR_FEET = 'Indoor feet';
    final public const string ATTACHED_TAIL = 'Attached tail';
    final public const string EXCHANGEABLE_TONGUES = 'Exchangeable tongues';
    final public const string REMOVABLE_EYELIDS = 'Removable eyelids';
    final public const string MOVABLE_JAW = 'Movable jaw';
    final public const string ATTACHED_HANDPAWS_AND_FEETPAWS = 'Attached handpaws and feetpaws';
    final public const string IN_HEAD_FANS = 'In-head fans';
    final public const string LED_EYES = 'LED eyes';
    final public const string EXCHANGEABLE_HAIRS = 'Exchangeable hairs';
    final public const string REMOVABLE_HORNS_ANTLERS = 'Removable horns/antlers';
    final public const string WASHABLE_HEADS = 'Washable heads';
    final public const string LED_EL_LIGHTS = 'LED/EL lights';
    final public const string ADJUSTABLE_WIGGLE_EARS = 'Adjustable/wiggle ears';
    final public const string ADJUSTABLE_EYEBROWS = 'Adjustable eyebrows';
    final public const string ELECTRONICS_ANIMATRONICS = 'Electronics/animatronics';
    final public const string REMOVABLE_BLUSH = 'Removable blush';

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
