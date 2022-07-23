<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\Controller\IuForm\Utils\StartData;
use App\Form\InclusionUpdate\Start;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
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

        $form = $this->createForm(Start::class, new StartData(), [
            Start::OPT_STUDIO_NAME => $this->getMakerDesc($state->artisan),
            Start::OPT_ROUTER      => $this->router,
        ])->handleRequest($request);

        $bigErrorMessage = '';

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->captcha->isValid($request, 'iu_form_captcha')) {
                $state->markCaptchaDone();

                return $this->redirectToStep(RouteName::IU_FORM_DATA, $state);
            } else {
                $bigErrorMessage = 'Automatic captcha failed. Please try again. If it fails once more, try different browser, different device or different network.';
            }
        }

        return $this->render('iu_form/start.html.twig', [
            'do_not_track'      => true,
            'is_new'            => null === $state->artisan->getId(),
            'form'              => $form->createView(),
            'big_error_message' => $bigErrorMessage,
        ]);
    }

    private function getMakerDesc(Artisan $artisan): ?string
    {
        if (null === $artisan->getId()) {
            return null;
        }

        $makerId = '' !== $artisan->getMakerId() ? ' ('.$artisan->getMakerId().')' : '';

        return $artisan->getName().$makerId;
    }
}
