<?php

declare(strict_types=1);

namespace App\Captcha\Challenge;

final readonly class Challenge
{
    /**
     * @param list<Question> $questions
     */
    public function __construct(
        public array $questions,
    ) {
    }
}
