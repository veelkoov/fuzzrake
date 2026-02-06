<?php

declare(strict_types=1);

namespace App\Captcha\Challenge;

final readonly class Question
{
    /**
     * @param list<QuestionOption> $options
     */
    public function __construct(
        public string $question,
        public array $options,
    ) {
    }
}
