<?php

declare(strict_types=1);

namespace App\ValueObject;

use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;

final class Feedback
{
    private const EXPLANATION_OPTIONS_ERROR_MESSAGE = 'The selected option serves only for explanation, you cannot use it.';
    private const COMMISSIONS_INFO_INACCURATE = "Maker's commissions info (open/closed) is inaccurate";
    private const OTHER_INFO_OUTDATED = "Other maker's information is (partially) outdated";
    final public const OPTIONS = [
        self::COMMISSIONS_INFO_INACCURATE,
        "Maker's website/social account is no longer working",
        self::OTHER_INFO_OUTDATED,
        'Other information on this website needs attention (not related to a particular maker)',
        'Report a technical problem/bug with this website',
        'Suggest an improvement to this website',
        'Other (please provide adequate details and context)',
    ];

    #[IsTrue(message: 'This is required.')]
    public bool $noContactBack = false;

    #[NotBlank(message: 'This is required.')]
    #[NotEqualTo(self::COMMISSIONS_INFO_INACCURATE, message: self::EXPLANATION_OPTIONS_ERROR_MESSAGE)]
    #[NotEqualTo(self::OTHER_INFO_OUTDATED, message: self::EXPLANATION_OPTIONS_ERROR_MESSAGE)]
    public string $subject = '';

    #[Length(max: 100)]
    public string $maker = '';

    #[NotBlank(message: 'This is required.')]
    public string $details = '';
}
