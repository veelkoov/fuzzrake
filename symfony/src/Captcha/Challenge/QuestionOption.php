<?php

declare(strict_types=1);

namespace App\Captcha\Challenge;

use Symfony\Component\Uid\Uuid;

final readonly class QuestionOption
{
    public string $id;

    public function __construct(
        public string $description,
        public string $emoticon,
        public bool $correct,
    ) {
        $this->id = Uuid::v4()->toRfc4122();
    }
}
