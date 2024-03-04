<?php

declare(strict_types=1);

namespace App\Service;

use App\Utils\TestUtils\TestsBridge;
use Psr\Log\LoggerInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

class Captcha
{
    private readonly float $threshold;

    public function __construct(
        private readonly ReCaptcha $reCaptcha,
        private readonly LoggerInterface $logger,
        #[Autowire('%env(GOOGLE_RECAPTCHA_SCORE_THRESHOLD)%')]
        string $threshold,
    ) {
        $threshold = floatval($threshold);
        $this->threshold = $threshold >= 0.0 && $threshold <= 1.0 ? $threshold : 0.9;
    }

    public function isValid(Request $request, string $action): bool
    {
        if (TestsBridge::shouldSkipSingleCaptcha()) {
            return true;
        }

        $token = $request->get('token');
        $token = is_string($token) ? $token : '';

        $response = $this->reCaptcha
            ->setExpectedHostname($request->getHost())
            ->setExpectedAction($action)
            ->setScoreThreshold($this->threshold)
            ->verify($token, $request->getClientIp());

        if (!$response->isSuccess()) {
            $this->logger->info('reCAPTCHA verification failed', [$response->toArray()]);
        }

        return $response->isSuccess();
    }
}
