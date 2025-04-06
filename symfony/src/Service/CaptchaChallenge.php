<?php

declare(strict_types=1);

namespace App\Service;

final readonly class CaptchaChallenge
{
    /**
     * @param list<CaptchaChallengeQuestion> $questions
     */
    public function __construct(
        public array $questions,
    ) {
    }
}
