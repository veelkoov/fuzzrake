<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\EnvironmentsService;
use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class AbstractRecaptchaBackedController extends AbstractController
{
    private ReCaptcha $reCaptcha;
    private EnvironmentsService $environments;

    public function __construct(ReCaptcha $reCaptcha, EnvironmentsService $environments)
    {
        $this->reCaptcha = $reCaptcha;
        $this->environments = $environments;
    }

    protected function isReCaptchaTokenOk(Request $request, $action): bool
    {
        if ($this->environments->isTest()) {
            return true;
        }

        return $this->reCaptcha
            ->setExpectedHostname($request->getHttpHost())
            ->setExpectedAction($action)
            ->setScoreThreshold($_ENV['GOOGLE_RECAPTCHA_SCORE_THRESHOLD'] ?: 0.8)
            ->verify($request->get('token', 'missing-token'), $request->getClientIp())->isSuccess();
    }
}
