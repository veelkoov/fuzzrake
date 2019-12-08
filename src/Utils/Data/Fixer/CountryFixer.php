<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Utils as Regexp;

class CountryFixer implements FixerInterface
{
    private const COUNTRIES_REPLACEMENTS = [
        'argentina'                                     => 'AR',
        'australia'                                     => 'AU',
        'belgium'                                       => 'BE',
        'canada'                                        => 'CA',
        'costa rica'                                    => 'CR',
        'czech republic'                                => 'CZ',
        'd[ea]nmark'                                    => 'DK',
        'germany'                                       => 'DE',
        'finland'                                       => 'FI',
        'france'                                        => 'FR',
        'uk|england|united kingdom'                     => 'GB',
        'ireland'                                       => 'IE',
        'italia|italy'                                  => 'IT',
        'mexico'                                        => 'MX',
        '(the )?netherlands'                            => 'NL',
        'new zealand'                                   => 'NZ',
        'russia'                                        => 'RU',
        'poland'                                        => 'PL',
        'sweden'                                        => 'SE',
        'ukraine'                                       => 'UA',
        'united states( of america)?|us of america|usa' => 'US',
    ];

    public function fix(string $fieldName, string $subject): string
    {
        $subject = trim($subject);

        foreach (self::COUNTRIES_REPLACEMENTS as $regexp => $replacement) {
            $subject = Regexp::replace("#^$regexp$#i", $replacement, $subject);
        }

        return $subject;
    }
}
