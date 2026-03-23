<?php

declare(strict_types=1);

namespace App\Controller;

use App\Captcha\CaptchaService;
use App\Controller\Utils\CreatorByCreatorIdTrait;
use App\Controller\Utils\IuFormChecklist;
use App\Data\Definitions\Fields\Field;
use App\Entity\User;
use App\Form\InclusionUpdate\Data;
use App\Form\InclusionUpdate\Start;
use App\IuHandling\Exception\SubmissionException;
use App\IuHandling\SubmissionService;
use App\Repository\CreatorRepository;
use App\Utils\Collections\ArrayReader;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/user/iu_form')] // grep-code-route-user-prefix
class IuFormController extends AbstractController
{
    use CreatorByCreatorIdTrait;

    protected const string NEW_CREATOR_ID_PLACEHOLDER = '(new)';

    public function __construct(
        protected readonly CreatorRepository $creatorRepository,
        protected readonly LoggerInterface $logger,
    ) {
    }

    #[Route(path: '/start', name: RouteName::USER_IU_FORM_START)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormStart(Request $request): Response
    {
        $form = $this->createForm(Start::class, new IuFormChecklist());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute(RouteName::USER_IU_FORM_DATA, ['creatorId' => $creatorId]); // FIXME
        }

        return $this->render('iu_form/start.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route(path: '/data', name: RouteName::USER_IU_FORM_DATA)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormData(
        #[CurrentUser] User $user,
        Request $request,
        SessionInterface $session,
        CaptchaService $captchaService,
        RouterInterface $router,
        SubmissionService $submissionService,
    ): Response {
        $creator = null === $user->getCreator() ? new Creator() : Creator::wrap($user->getCreator());
        $photosCopyrightOk = null !== $creatorId && $creator->hasData(Field::URL_PHOTOS); // FIXME

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
            'form'       => $form,
            'errors'     => $form->getErrors(true),
            'submitted'  => $form->isSubmitted(),
            'creator_id' => $creatorId ?? self::NEW_CREATOR_ID_PLACEHOLDER,
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

    #[Route(path: '/confirmation', name: RouteName::USER_IU_FORM_CONFIRMATION)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormConfirmation(Request $request): Response
    {
        return $this->render('iu_form/confirmation.html.twig', [
            'creator_id' => $request->query->get('creatorId', self::NEW_CREATOR_ID_PLACEHOLDER),
        ]);
    }
}
