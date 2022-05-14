<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\ValueObject\Routing\RouteName;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class IuFormStartController extends AbstractIuFormController
{
    /**
     * @throws NotFoundHttpException
     */
    #[Route(path: '/iu_form/start/{makerId}', name: RouteName::IU_FORM_START)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormStart(Request $request, ?string $makerId = null): Response
    {
        $state = $this->prepareState($makerId, $request);

        if ($request->isMethod('POST') && $this->isReCaptchaTokenOk($request, 'iu_form_captcha')) {
            $state->markCaptchaDone();

            return $this->redirectToStep(RouteName::IU_FORM_DATA, $state);
        }

        return $this->render('iu_form/captcha_and_rules.html.twig', [
            'next_step_url'     => $this->generateUrl(RouteName::IU_FORM_START, ['makerId' => $state->makerId]),
            'big_error_message' => $request->isMethod('POST') ? 'Automatic captcha failed. Please try again. If it fails once more, try different browser, different device or different network.' : '',
        ]);
    }
}
