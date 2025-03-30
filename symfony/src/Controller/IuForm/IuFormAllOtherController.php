<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\Utils\DateTime\UtcClock;
use App\ValueObject\Routing\RouteName;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

class IuFormAllOtherController extends AbstractIuFormController
{
    #[Route(path: '/iu_form/confirmation', name: RouteName::IU_FORM_CONFIRMATION)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormConfirmation(Request $request): Response
    {
        return $this->render('iu_form/confirmation.html.twig', [
            'password_ok'            => 'yes' === $request->get('passwordOk', 'no'),
            'contact_allowed'        => 'yes' === $request->get('contactAllowed', 'is_no'),
            'no_selected_previously' => 'was_no' === $request->get('contactAllowed', 'is_no'),
            'submission_id'          => $request->get('submissionId', UtcClock::now()->format(DATE_RFC3339)),
            'creator_id'             => $request->get('makerId', self::NEW_CREATOR_ID_PLACEHOLDER),
            'is_new'                 => null !== $request->get('makerId'),
        ]);
    }

    #[Route(path: '/iu_form/fill/{makerId}')]
    #[Route(path: '/iu_form/{makerId}', priority: -10)]
    #[Cache(maxage: 0, public: false)]
    public function oldAddressRedirect(?string $makerId = null): Response
    {
        return $this->redirectToRoute(RouteName::IU_FORM_START, ['makerId' => $makerId]);
    }
}
