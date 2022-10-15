<?php

declare(strict_types=1);

namespace App\Feedback;

use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class Feedback
{
    #[IsTrue(message: 'This is required.')]
    public bool $noContactBack;

    #[NotBlank(message: 'This is required.')]
    public string $subject;

    #[Length(min: 1, max: 100)]
    public string $maker;

    #[NotBlank(message: 'This is required.')]
    public string $details;
}
