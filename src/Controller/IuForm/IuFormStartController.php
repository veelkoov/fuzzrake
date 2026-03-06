<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\Controller\IuForm\IuFormUtils\StartData;
use App\Form\InclusionUpdate\Start;
use App\ValueObject\Routing\RouteName;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/user/iu_form')] // grep-code-route-user-prefix
class IuFormStartController extends IuFormAbstractController
{
    /**
     * @throws NotFoundHttpException
     */
    #[Route(path: '/start/{creatorId}', name: RouteName::USER_IU_FORM_START)] // TODO: Redirection from legacy
    #[Cache(maxage: 0, public: false)]
    public function iuFormStart(Request $request, ?string $creatorId = null): Response
    {
        $form = $this->createForm(Start::class, new StartData());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute(RouteName::USER_IU_FORM_DATA, ['creatorId' => $creatorId]);
        }

        return $this->render('iu_form/start.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
