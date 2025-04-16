<?php

declare(strict_types=1);

namespace App\Captcha;

use App\Captcha\Challenge\Challenge;

interface ChallengeProvider
{
    public function getNewChallenge(): Challenge;
}
