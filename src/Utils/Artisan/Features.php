<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

class Features extends Dictionary
{
    public const FOLLOW_ME_EYES = 'Follow-me eyes';
    public const OUTDOOR_FEET = 'Outdoor feet';
    public const INDOOR_FEET = 'Indoor feet';
    public const ATTACHED_TAIL = 'Attached tail';
    public const EXCHANGEABLE_TONGUES = 'Exchangeable tongues';
    public const REMOVABLE_EYELIDS = 'Removable eyelids';
    public const MOVABLE_JAW = 'Movable jaw';
    public const ATTACHED_HANDPAWS_AND_FEETPAWS = 'Attached handpaws and feetpaws';
    public const IN_HEAD_FANS = 'In-head fans';
    public const LED_EYES = 'LED eyes';
    public const EXCHANGEABLE_HAIRS = 'Exchangeable hairs';
    public const REMOVABLE_HORNS_ANTLERS = 'Removable horns/antlers';
    public const WASHABLE_HEADS = 'Washable heads';
    public const LED_EL_LIGHTS = 'LED/EL lights';
    public const ADJUSTABLE_WIGGLE_EARS = 'Adjustable/wiggle ears';
    public const ADJUSTABLE_EYEBROWS = 'Adjustable eyebrows';
    public const ELECTRONICS_ANIMATRONICS = 'Electronics/animatronics';
    public const REMOVABLE_BLUSH = 'Removable blush';

    public static function getValues(): array
    {
        return [
            self::FOLLOW_ME_EYES                 => self::FOLLOW_ME_EYES,
            self::OUTDOOR_FEET                   => self::OUTDOOR_FEET,
            self::INDOOR_FEET                    => self::INDOOR_FEET,
            self::ATTACHED_TAIL                  => self::ATTACHED_TAIL,
            self::EXCHANGEABLE_TONGUES           => self::EXCHANGEABLE_TONGUES,
            self::REMOVABLE_EYELIDS              => self::REMOVABLE_EYELIDS,
            self::MOVABLE_JAW                    => self::MOVABLE_JAW,
            self::ATTACHED_HANDPAWS_AND_FEETPAWS => self::ATTACHED_HANDPAWS_AND_FEETPAWS,
            self::IN_HEAD_FANS                   => self::IN_HEAD_FANS,
            self::LED_EYES                       => self::LED_EYES,
            self::EXCHANGEABLE_HAIRS             => self::EXCHANGEABLE_HAIRS,
            self::REMOVABLE_HORNS_ANTLERS        => self::REMOVABLE_HORNS_ANTLERS,
            self::WASHABLE_HEADS                 => self::WASHABLE_HEADS,
            self::LED_EL_LIGHTS                  => self::LED_EL_LIGHTS,
            self::ADJUSTABLE_WIGGLE_EARS         => self::ADJUSTABLE_WIGGLE_EARS,
            self::ADJUSTABLE_EYEBROWS            => self::ADJUSTABLE_EYEBROWS,
            self::ELECTRONICS_ANIMATRONICS       => self::ELECTRONICS_ANIMATRONICS,
            self::REMOVABLE_BLUSH                => self::REMOVABLE_BLUSH,
        ];
    }
}
