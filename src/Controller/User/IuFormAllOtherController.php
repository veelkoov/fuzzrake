<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Utils\DateTime\UtcClock;
use App\ValueObject\Routing\RouteName;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/user/iu_form')] // grep-code-route-user-prefix
class IuFormAllOtherController extends IuFormAbstractController
{
    #[Route(path: '/confirmation', name: RouteName::USER_IU_FORM_CONFIRMATION)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormConfirmation(Request $request): Response
    {
        return $this->render('iu_form/confirmation.html.twig', [
            'password_ok'            => 'yes' === $request->query->get('passwordOk', 'no'),
            'contact_allowed'        => 'yes' === $request->query->get('contactAllowed', 'is_no'),
            'no_selected_previously' => 'was_no' === $request->query->get('contactAllowed', 'is_no'),
            'submission_id'          => $request->query->get('submissionId', UtcClock::now()->format(DATE_RFC3339)),
            'creator_id'             => $request->query->get('creatorId', self::NEW_CREATOR_ID_PLACEHOLDER),
            'is_new'                 => null !== $request->query->get('creatorId'),
        ]);
    }

    #[Route(path: '/fill/{creatorId}')]
    #[Route(path: '/{creatorId}', priority: -10)]
    #[Cache(maxage: 0, public: false)]
    public function oldAddressRedirect(?string $creatorId = null): Response
    {
        return $this->redirectToRoute(RouteName::USER_IU_FORM_START, ['creatorId' => $creatorId]);
    }
}
