<?php

declare(strict_types=1);

namespace App\Utils\Artisan;

class ContactPermit extends Dictionary
{
    public const NO = 'NO';
    public const CORRECTIONS = 'CORRECTIONS';
    public const ANNOUNCEMENTS = 'ANNOUNCEMENTS';
    public const FEEDBACK = 'FEEDBACK';

    public static function getValues(): array
    {
        return [
            self::NO => 'NO, if I made a mistake in the form, feel free to reject whole submission; I don\'t care about keeping my info complete in the future as well.',
            self::CORRECTIONS => 'If I made a mistake in the form - contact me for CORRECTIONS; I don\'t care about keeping my info complete in the future.',
            self::ANNOUNCEMENTS => 'Contact me for CORRECTIONS and also send me ANNOUNCEMENTS about getfursu.it updates, so I can keep my info complete & up to date. Contact me to correct links, when old break.',
            self::FEEDBACK => 'Contact for CORRECTIONS and ANNOUNCEMENTS is OK. I can also fill some questionnaires to provide FEEDBACK and make the website better. I will remember I DON\'T HAVE TO fill the questionnaire or respond when I receive a request.',
        ];
    }
}
