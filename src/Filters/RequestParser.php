<?php

declare(strict_types=1);

namespace App\Filters;

use Psl\Type;
use Symfony\Component\HttpFoundation\Request;

class RequestParser
{
    public function getChoices(Request $request): Choices
    {
        $dataShape = Type\shape([
            'countries' => Type\vec(Type\string()),
        ]);

        $data = $dataShape->coerce([
            'countries' => $request->get('country', []),
        ]);

        return new Choices(
            $data['countries'],
        );
    }
}
