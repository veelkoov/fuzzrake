<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Utils\CreatorByCreatorIdTrait;
use App\Controller\Utils\IuFormChecklist;
use App\Data\Definitions\Fields\Field;
use App\Entity\Creator as CreatorE;
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
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/user/iu_form')] // grep-code-route-user-prefix
#[Cache(maxage: 0, public: false)]
class IuFormController extends AbstractController
{
    use CreatorByCreatorIdTrait;

    public function __construct(
        protected readonly CreatorRepository $creatorRepository,
        protected readonly LoggerInterface $logger,
    ) {
    }

    #[Route(path: '/start', name: RouteName::USER_IU_FORM_START)]
    public function start(Request $request): Response
    {
        $form = $this->createForm(Start::class, new IuFormChecklist());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute(RouteName::USER_IU_FORM_DATA);
        }

        return $this->render('iu_form/start.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/data', name: RouteName::USER_IU_FORM_DATA)]
    public function data(
        #[CurrentUser] User $user,
        Request $request,
        RouterInterface $router,
        SubmissionService $submissionService,
    ): Response {
        if (null === $user->getCreator()) {
            $isUpdate = false;
            $submissionData = new Creator();
            $initialPhotosCopyrightOk = false;
        } else {
            $isUpdate = true;
            $submissionData = Creator::wrap($user->getCreator())->copy();
            $initialPhotosCopyrightOk = $submissionData->hasData(Field::URL_PHOTOS);
        }

        $form = $this->createForm(Data::class, $submissionData, [
            Data::OPT_PHOTOS_COPYRIGHT_OK => $initialPhotosCopyrightOk,
            'router' => $router,
        ])->handleRequest($request);

        $this->validatePhotosCopyright($form, $submissionData);
        $this->validateCreatorId($form, $submissionData, $user->getCreator());

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $submissionService->submit($user, $submissionData, $isUpdate);

                return $this->redirectToRoute(RouteName::USER_IU_FORM_CONFIRMATION);
            } catch (SubmissionException $exception) {
                $this->logger->error('Failed to submit I/U form data.', ['exception' => $exception]);

                $form->addError(new FormError('There was an error while submitting the form. Please try again or contact the website maintainer.'));
            }
        }

        return $this->render('iu_form/data.html.twig', [
            'form'       => $form,
            'errors'     => $form->getErrors(true),
            'submitted'  => $form->isSubmitted(),
            'creator_id' => $this->getCreatorIdTplValue($user),
        ]);
    }

    #[Route(path: '/confirmation', name: RouteName::USER_IU_FORM_CONFIRMATION)]
    public function confirmation(#[CurrentUser] User $user): Response
    {
        return $this->render('iu_form/confirmation.html.twig', [
            'creator_id' => $this->getCreatorIdTplValue($user),
        ]);
    }

    private function validatePhotosCopyright(FormInterface $form, Creator $submissionData): void
    {
        $field = $form->get(Data::FLD_PHOTOS_COPYRIGHT);

        $isOK = 'OK' === ArrayReader::of($field->getData())->getOrDefault('[0]', null);

        if ($submissionData->hasData(Field::URL_PHOTOS) && !$isOK) {
            $field->addError(new FormError('You must not use any photos without permission from the photographer.'));
        }
    }

    private function validateCreatorId(FormInterface $form, Creator $submissionData, ?CreatorE $creator): void
    {
        try {
            $creatorIdOwner = $this->creatorRepository->findByCreatorId($submissionData->getCreatorId());

            if ($creatorIdOwner->getId() !== $creator?->getId()) {
                $form->get(Data::FLD_CREATOR_ID)
                    ->addError(new FormError('This maker ID has been already used by another maker.'));
            }
        } catch (NoResultException) {
            // Unused ID = OK
        }
    }

    private function getCreatorIdTplValue(User $user): string
    {
        // grep-code-iu-form-data-local-storage The creator ID (or "(new)" placeholder) is used to allow
        // using a single browser/machine to manage multiple studios. Low probability, but it's here.
        return $user->getCreator()?->getLastCreatorId() ?? '(new)';
    }
}
