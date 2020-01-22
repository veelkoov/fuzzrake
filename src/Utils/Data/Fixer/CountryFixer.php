<?php

declare(strict_types=1);

namespace App\Utils\Data\Fixer;

use App\Utils\Regexp\Regexp;

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
        'hungary'                                       => 'HU',
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
        $subject = Regexp::replaceAll(self::COUNTRIES_REPLACEMENTS, $subject, '#^', '$#i');

        return $subject;
    }
}
