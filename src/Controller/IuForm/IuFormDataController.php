<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\Captcha\CaptchaService;
use App\Data\Definitions\Fields\Field;
use App\Entity\User;
use App\Form\InclusionUpdate\Data;
use App\IuHandling\Exception\SubmissionException;
use App\IuHandling\SubmissionService;
use App\Utils\Collections\ArrayReader;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/user/iu_form')] // grep-code-route-user-prefix
class IuFormDataController extends AbstractIuFormController
{
    /**
     * @throws NotFoundHttpException
     */
    #[Route(path: '/data/{creatorId}', name: RouteName::USER_IU_FORM_DATA)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormData(
        #[CurrentUser] User $user,
        Request $request,
        SessionInterface $session,
        CaptchaService $captchaService,
        RouterInterface $router,
        SubmissionService $submissionService,
        ?string $creatorId = null,
    ): Response {
        $creator = null === $user->getCreator() ? new Creator() : Creator::wrap($user->getCreator());
        $photosCopyrightOk = null !== $creatorId && $creator->hasData(Field::URL_PHOTOS);

        $form = $this->createForm(Data::class, $creator, [
            Data::OPT_PHOTOS_COPYRIGHT_OK => $photosCopyrightOk,
            'router' => $router,
        ])->handleRequest($request);
        $captcha = $captchaService->getCaptcha($session)->handleRequest($request, $form);

        $this->validatePhotosCopyright($form, $creator);
        $this->validateCreatorId($form, $creator);

        if ($form->isSubmitted() && $form->isValid() && $captcha->isSolved()) {
            try {
                $submissionService->submit($creator);

                return $this->redirectToRoute(RouteName::USER_IU_FORM_CONFIRMATION, [
                    'creatorId' => $creatorId, // FIXME!!!
                ]);
            } catch (SubmissionException $exception) {
                $this->logger->error('Failed to submit I/U form data.', ['exception' => $exception]);

                $form->addError(new FormError('There was an error while submitting the form. Please try again or contact the website maintainer.'));
            }
        }

        return $this->render('iu_form/data.html.twig', [
            'form'                => $form,
            'errors'              => $form->getErrors(true),
            'submitted'           => $form->isSubmitted(),
            'creator_id'          => $creatorId ?? self::NEW_CREATOR_ID_PLACEHOLDER,
        ]);
    }

    private function validatePhotosCopyright(FormInterface $form, Creator $creator): void
    {
        $field = $form->get(Data::FLD_PHOTOS_COPYRIGHT);

        $isOK = 'OK' === ArrayReader::of($field->getData())->getOrDefault('[0]', null);

        if ($creator->hasData(Field::URL_PHOTOS) && !$isOK) {
            $field->addError(new FormError('You must not use any photos without permission from the photographer.'));
        }
    }

    private function validateCreatorId(FormInterface $form, Creator $creator): void
    {
        try {
            $creatorIdOwner = $this->creatorRepository->findByCreatorId($creator->getCreatorId());

            if ($creatorIdOwner->getId() !== $creator->getId()) {
                $form->get(Data::FLD_CREATOR_ID)
                    ->addError(new FormError('This maker ID has been already used by another maker.'));
            }
        } catch (NoResultException) {
            // Unused ID = OK
        }
    }
}
