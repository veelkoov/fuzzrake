<?php

declare(strict_types=1);

namespace App\Filters;

use Symfony\Component\HttpFoundation\Request;

class RequestParser
{
    public function getChoices(Request $request): Choices
    {
        /**
         * @var string[] $countries TODO: Validate
         */
        $countries = $request->get('country', []);

        return new Choices(
            $countries,
        );
    }
}
