<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\Controller\IuForm\Utils\IuState;
use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Form\InclusionUpdate\BaseForm;
use App\Form\InclusionUpdate\Data;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Collections\ArrayReader;
use App\Utils\Password;
use App\ValueObject\Routing\RouteName;
use App\ValueObject\Texts;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class IuFormDataController extends AbstractIuFormController
{
    /**
     * @throws NotFoundHttpException
     */
    #[Route(path: '/iu_form/data/{makerId}', name: RouteName::IU_FORM_DATA)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormData(Request $request, ?string $makerId = null): Response
    {
        $state = $this->prepareState($makerId, $request);

        $form = $this->handleForm($request, $state, Data::class, [
            Data::OPT_PHOTOS_COPYRIGHT_OK => !$state->isNew() && $state->artisan->hasData(Field::URL_PHOTOS),
            'router'                      => $this->router,
        ]);
        $this->validatePassword($form, $state);
        $this->validatePhotosCopyright($form, $state->artisan);
        $this->validateMakerId($form, $state->artisan);

        if (self::clicked($form, BaseForm::BTN_RESET)) {
            $state->reset();
        }

        if (null !== ($redirection = $this->redirectToUnfinishedStep(RouteName::IU_FORM_DATA, $state))) {
            return $redirection;
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedPasswordOk = $this->handlePassword($state);

            $isContactAllowed = ContactPermit::NO !== $state->artisan->getContactAllowed();

            $state->save();

            if ($this->iuFormService->submit($state->artisan)) {
                $state->reset();

                return $this->redirectToRoute(RouteName::IU_FORM_CONFIRMATION, [
                    'isNew'          => $state->isNew() ? 'yes' : 'no',
                    'passwordOk'     => $submittedPasswordOk ? 'yes' : 'no',
                    'contactAllowed' => $isContactAllowed ? ($state->wasContactAllowed ? 'yes' : 'was_no') : 'is_no',
                    // TODO 'submissionId'   =>
                ]);
            } else {
                $form->addError(new FormError('There was an error while trying to submit the form. Please note the time of seeing this message and contact the website maintainer. I am terribly sorry for the inconvenience!'));
            }
        }

        return $this->render('iu_form/data.html.twig', [
            'form'               => $form,
            'errors'             => $form->getErrors(true),
            'noindex'            => true,
            'submitted'          => $form->isSubmitted(),
            'is_update'          => !$state->isNew(),
            'was_contact_allowed' => $state->wasContactAllowed,
            'session_start_time' => $state->getStarted(),
            'big_error_message'  => $this->getRestoreFailedMessage($state),
        ]);
    }

    private function validatePhotosCopyright(FormInterface $form, Artisan $artisan): void
    {
        $field = $form->get(Data::FLD_PHOTOS_COPYRIGHT);

        $isOK = 'OK' === ArrayReader::of($field->getData())->getOrDefault('[0]', null);

        if ($artisan->hasData(Field::URL_PHOTOS) && !$isOK) {
            $field->addError(new FormError('You must not use any photos without permission from the photographer.'));
        }
    }

    private function validateMakerId(FormInterface $form, Artisan $artisan): void
    {
        try {
            $makerIdOwner = $this->creatorRepository->findByMakerId($artisan->getMakerId());

            if ($makerIdOwner->getId() !== $artisan->getId()) {
                $form->get(Data::FLD_MAKER_ID)
                    ->addError(new FormError('This maker ID has been already used by another maker.'));
            }
        } catch (NoResultException) {
            // Unused ID = OK
        }
    }

    private function validatePassword(FormInterface $form, IuState $state): void
    {
        if (!$form->isSubmitted() || $state->isNew()) {
            return;
        }

        $verificationAcknowledgmentField = $form->get(Data::FLD_VERIFICATION_ACKNOWLEDGEMENT);

        $wantsPasswordChange = $form->get(Data::FLD_CHANGE_PASSWORD)->getData() ?? false;
        $contactAllowed = ContactPermit::NO !== $form->get(Data::FLD_CONTACT_ALLOWED)->getData();
        $verificationAcknowledgment = $verificationAcknowledgmentField->getData() ?? false;

        if ($wantsPasswordChange && (!$contactAllowed || !$state->wasContactAllowed) && !$verificationAcknowledgment) {
            $errorMessage = 'Your action is required; your submission will be rejected otherwise.';
            $verificationAcknowledgmentField->addError(new FormError($errorMessage));
        }

        if (!$wantsPasswordChange && !Password::verify($state->artisan, $state->previousPassword)) {
            $errorMessage = 'Wrong password. To change your password, please select the "'.Texts::WANT_TO_CHANGE_PASSWORD.'" checkbox.';
            $form->get(Data::FLD_PASSWORD)->addError(new FormError($errorMessage));
        }
    }

    /**
     * @return bool If the I/U submission requires confirmation (password didn't match previous one)
     */
    private function handlePassword(IuState $data): bool
    {
        if ($data->isNew()) {
            Password::encryptOn($data->artisan);

            return true;
        } elseif (Password::verify($data->artisan, $data->previousPassword)) {
            $data->artisan->setPassword($data->previousPassword); // Was already hashed; use old hash - must not appear changed

            return true;
        } else {
            Password::encryptOn($data->artisan); // Will become new password if confirmed with maintainer

            return false;
        }
    }
}
