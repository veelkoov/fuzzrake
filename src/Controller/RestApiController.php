<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ArtisanRepository;
use App\Service\HealthCheckService;
use ReCaptcha\ReCaptcha;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RestApiController extends AbstractController
{
    /**
     * @Route("/api/", name="api")
     * @Cache(maxage=3600, public=true)
     */
    public function api(): Response
    {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/api/info/email.part.html")
     * @Cache(maxage=0, public=false)
     */
    public function info_emailHtml(Request $request, ReCaptcha $captcha, string $contactEmail): Response
    {
        $ok = $this->isReCaptchaTokenOk($captcha, $request);

        if ($ok) {
            $contactEmail = htmlspecialchars($contactEmail);

            return new Response("<a href=\"mailto:$contactEmail\" class=\"btn btn-primary my-1 btn-sm\"><i class=\"fas fa-envelope\"></i> $contactEmail</a>");
        } else {
            return new Response('', Response::HTTP_FORBIDDEN, ['X-Fuzzrake-Debug' => 'reCAPTCHA validation failed']);
        }
    }

    /**
     * @Route("/api/artisans.json", name="api_artisans")
     * @Cache(maxage=3600, public=true)
     */
    public function artisans(ArtisanRepository $artisanRepository): JsonResponse
    {
        return new JsonResponse($artisanRepository->getAll());
    }

    /**
     * @Route("/api/old_to_new_maker_ids_map.json", name="api_old_to_new_maker_ids_map")
     * @Cache(maxage=3600, public=true)
     */
    public function oldToNewMakerIdsMap(ArtisanRepository $artisanRepository): JsonResponse
    {
        return new JsonResponse($artisanRepository->getOldToNewMakerIdsMap());
    }

    /**
     * @Route("/health", name="health")
     * @Cache(maxage=0, public=false)
     */
    public function healthcheck(HealthCheckService $healthCheckService): JsonResponse
    {
        return new JsonResponse($healthCheckService->getStatus());
    }

    private function isReCaptchaTokenOk(ReCaptcha $captcha, Request $request): bool
    {
        return $captcha
            ->setExpectedHostname($request->getHttpHost())
            ->setExpectedAction('info_emailHtml')
            ->setScoreThreshold($_ENV['GOOGLE_RECAPTCHA_SCORE_THRESHOLD'] ?: 0.8)
            ->verify($request->get('token', 'missing-token'), $request->getClientIp())->isSuccess();
    }
}
