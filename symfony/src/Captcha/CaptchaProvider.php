<?php

declare(strict_types=1);

namespace App\Captcha;

use App\Captcha\Challenge\Challenge;

interface CaptchaProvider
{
    public function getNewChallenge(): Challenge;
}
