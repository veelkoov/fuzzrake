<?php

declare(strict_types=1);

namespace App\ValueObject;

use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class Feedback
{
    #[IsTrue(message: 'This is required.')]
    public bool $noContactBack = false;

    #[NotBlank(message: 'This is required.')]
    public string $subject = '';

    #[Length(max: 100)]
    public string $maker = '';

    #[NotBlank(message: 'This is required.')]
    public string $details = '';
}
