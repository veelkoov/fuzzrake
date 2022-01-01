<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\EnvironmentsService;
use Psr\Log\LoggerInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class AbstractRecaptchaBackedController extends AbstractController
{
    public function __construct(
        private readonly ReCaptcha $reCaptcha,
        private readonly EnvironmentsService $environments,
        protected readonly LoggerInterface $logger,
    ) {
    }

    protected function isReCaptchaTokenOk(Request $request, string $action): bool
    {
        if ($this->environments->isTest()) {
            return true;
        }

        $response = $this->reCaptcha
            ->setExpectedHostname($request->getHost())
            ->setExpectedAction($action)
            ->setScoreThreshold($_ENV['GOOGLE_RECAPTCHA_SCORE_THRESHOLD'] ?: 0.8)
            ->verify($request->get('token', 'missing-token'), $request->getClientIp());

        if (!$response->isSuccess()) {
            $this->logger->info('reCAPTCHA verification failed', [$response->toArray()]);
        }

        return $response->isSuccess();
    }
}
