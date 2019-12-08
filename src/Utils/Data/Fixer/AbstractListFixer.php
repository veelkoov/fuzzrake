<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Artisan\Features;
use App\Utils\Artisan\OrderTypes;
use App\Utils\Regexp\Utils as Regexp;
use App\Utils\StrUtils;

abstract class AbstractListFixer extends StringFixer
{
    private const REPLACEMENTS = [
        'Three-fourth \(Head, handpaws, tail, legs/pants, feetpaws\)' => OrderTypes::THREE_FOURTH,
        'Partial \(Head, handpaws, tail, feetpaws\)'                  => OrderTypes::PARTIAL,
        'Mini partial \(Head, handpaws, tail\)'                       => OrderTypes::MINI_PARTIAL,
        'Three-fourth \(Head+handpaws+tail+legs/pants+feetpaws\)'     => OrderTypes::THREE_FOURTH,
        'Partial \(Head+handpaws+tail+feetpaws\)'                     => OrderTypes::PARTIAL,
        'Mini partial \(Head+handpaws+tail\)'                         => OrderTypes::MINI_PARTIAL,
        'Follow me eyes'                                              => Features::FOLLOW_ME_EYES,
        'Adjustable ears / wiggle ears'                               => Features::ADJUSTABLE_WIGGLE_EARS,

        'Excellent vision &amp; breathability'                       => 'Excellent vision & breathability',
        'Bases, jawsets, silicone noses/tongues'                     => "Bases\nJawsets\nSilicone noses\nSilicone tongues",
        'Silicone and resin parts'                                   => "Silicone parts\nResin parts",
        'accessories and cleaning'                                   => 'Accessories and cleaning', // TODO
        'backpacks'                                                  => 'Backpacks',
        'claws'                                                      => 'Claws',
        'Armsleeves|Arm Sleeves'                                     => 'Arm sleeves',
        'Head Bases'                                                 => 'Head bases',
        'Plushes'                                                    => 'Plushies',
        'Plushie, backpacks, bandanas, collars, general accessories' => "Plushies\nBackpacks\nBandanas\nCollars\nGeneral accessories",
        'Eyes, noses, claws'                                         => "Eyes\nNoses\nClaws",
        'Resin and silicone parts'                                   => "Resin parts\nSilicone parts",
        'Sleeves \(legs and arms\)'                                  => "Arm sleeves\nLeg sleeves",
        'Legsleeves'                                                 => 'Leg sleeves',
        'Fursuit Props'                                              => 'Fursuit props',
        'Fursuit Props and Accessories, Fursuit supplies'            => "Fursuit props\nFursuit accessories\nFursuit supplies",
        'Fleece Props, Other accessories'                            => "Fleece props\nOther accessories",
        'Sock paws'                                                  => 'Sockpaws',
        'Removable magnetic parts, secret pockets'                   => "Removable magnetic parts\nHidden pockets",
        'Plush Suits'                                                => 'Plush suits',
        'Femme Suits'                                                => 'Femme suits',
        'Just Ask'                                                   => 'Just ask',
        'props and can do plushies'                                  => "Props\nCan do plushies",
        'Removable Eyes'                                             => 'Removable eyes',
        'Removable/interchangeable eyes'                             => "Removable eyes\nInterchangeable eyes",
        'Pickable Nose'                                              => 'Pickable nose',
        '(.+)changable(.+)'                                          => '$1changeable$2',
        'Fursuit Sprays?'                                            => 'Fursuit sprays',
        'Arm sleeves, plush props, fursuit spray'                    => "Arm sleeves\nPlush props\nFursuit sprays",
        'Body padding/plush suits'                                   => "Body padding\nPlush suits",
        'Dry brushing'                                               => 'Drybrushing',
        'Bendable wings and tails'                                   => "Bendable wings\nBendable tails",
        'Poseable tongues'                                           => 'Poseable tongue',
        'Accessories/jewelry'                                        => "Accessories\nJewelry",
        'Bandannas'                                                  => 'Bandanas',
    ];

    public function fix(string $fieldName, string $subject): string
    {
        $items = array_filter(array_map([$this, 'fixItem'], $this->split($subject)));

        $subject = implode("\n", $items);

        foreach (static::getReplacements() as $pattern => $replacement) {
            $subject = Regexp::replace("#(?<=^|\n)$pattern(?=\n|$)#i", $replacement, $subject);
        }

        $subject = parent::fix($fieldName, $subject);
        $subject = explode("\n", $subject);

        if (static::shouldSort()) {
            sort($subject);
        }

        return implode("\n", array_unique($subject));
    }

    abstract protected static function shouldSort(): bool;

    abstract protected static function getSeparatorRegexp(): string;

    /**
     * @return string[]
     */
    protected static function getNonsplittable(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    protected static function getReplacements(): array
    {
        return self::REPLACEMENTS;
    }

    /**
     * @return string[]
     */
    private function split(string $subject): array
    {
        $nonsplittable = array_fill_keys(static::getNonsplittable(), null);
        $i = 0;

        foreach ($nonsplittable as &$uItem) {
            $uItem = uniqid((string) $i++, false);
        }

        $subject = str_replace(array_keys($nonsplittable), array_values($nonsplittable), $subject);

        $items = Regexp::split(static::getSeparatorRegexp(), $subject);

        $nonsplittable = array_flip($nonsplittable);

        foreach ($items as &$sItem) {
            $sItem = $nonsplittable[$sItem] ?? $sItem;
        }

        return $items;
    }

    private function fixItem(string $subject): string
    {
        $subject = trim($subject);

        if ('http' !== substr($subject, 0, 4)) {
            $subject = StrUtils::ucfirst($subject);
        }

        return $subject;
    }
}
