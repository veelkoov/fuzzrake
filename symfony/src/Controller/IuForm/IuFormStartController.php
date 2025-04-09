<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\Controller\IuForm\Utils\StartData;
use App\Form\InclusionUpdate\Start;
use App\Service\Captcha;
use App\Service\DataService;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\ValueObject\Routing\RouteName;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class IuFormStartController extends AbstractIuFormController
{
    /**
     * @throws NotFoundHttpException
     */
    #[Route(path: '/iu_form/start/{makerId}', name: RouteName::IU_FORM_START)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormStart(Request $request, Captcha $captcha, DataService $dataService, ?string $makerId = null): Response
    {
        $subject = $this->getSubject($makerId);

        $form = $this->createForm(Start::class, new StartData(), [
            Start::OPT_STUDIO_NAME => $this->getCreatorDescription($subject->creator),
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($captcha->isValid($request, 'iu_form_captcha')) {
                $this->markCaptchaDone($request->getSession());

                return $this->redirectToRoute(RouteName::IU_FORM_DATA, ['makerId' => $makerId]);
            }

            $bigErrorMessage = 'Automatic captcha failed. Please try again. If it fails once more, try different browser, different device or different network.';
        } else {
            $bigErrorMessage = '';
        }

        return $this->render('iu_form/start.html.twig', [
            'is_new'            => $subject->isNew,
            'noindex'           => true,
            'form'              => $form->createView(),
            'big_error_message' => $bigErrorMessage,
        ]);
    }

    private function getCreatorDescription(Creator $creator): ?string
    {
        if (null === $creator->getId()) {
            return null;
        }

        $makerId = '' !== $creator->getMakerId() ? ' ('.$creator->getMakerId().')' : '';

        return $creator->getName().$makerId;
    }
}
