<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\User\IuFormUtils\StartData;
use App\Form\InclusionUpdate\Start;
use App\Utils\Creator\SmartAccessDecorator as Creator;
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
        $subject = $this->getSubject($creatorId);

        $form = $this->createForm(Start::class, new StartData(), [
            Start::OPT_STUDIO_NAME => $this->getCreatorDescription($subject->creator),
        ])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute(RouteName::USER_IU_FORM_DATA, ['creatorId' => $creatorId]);
        }

        return $this->render('iu_form/start.html.twig', [
            'is_new'  => $subject->isNew,
            'noindex' => true,
            'form'    => $form->createView(),
        ]);
    }

    private function getCreatorDescription(Creator $creator): ?string
    {
        if (null === $creator->getId()) {
            return null;
        }

        $creatorId = '' !== $creator->getCreatorId() ? ' ('.$creator->getCreatorId().')' : '';

        return $creator->getName().$creatorId;
    }
}
