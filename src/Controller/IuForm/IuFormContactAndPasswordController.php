<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\Controller\IuForm\Utils\IuState;
use App\DataDefinitions\ContactPermit;
use App\Form\InclusionUpdate\BaseForm;
use App\Form\InclusionUpdate\ContactAndPassword;
use App\Utils\Password;
use App\ValueObject\Routing\RouteName;
use App\ValueObject\Texts;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class IuFormContactAndPasswordController extends AbstractIuFormController
{
    /**
     * @throws NotFoundHttpException
     */
    #[Route(path: '/iu_form/contact_and_password/{makerId}', name: RouteName::IU_FORM_CONTACT_AND_PASSWORD)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormContactAndPassword(Request $request, ?string $makerId = null): Response
    {
        $state = $this->prepareState($makerId, $request);

        $form = $this->handleForm($request, $state, ContactAndPassword::class, []);
        $this->validatePassword($form, $state);

        if (self::clicked($form, BaseForm::BTN_RESET)) {
            $state->reset();
        }

        if (self::clicked($form, ContactAndPassword::BTN_BACK)) {
            $state->save();

            return $this->redirectToStep(RouteName::IU_FORM_DATA, $state);
        }

        if (null !== ($redirection = $this->redirectToUnfinishedStep(RouteName::IU_FORM_CONTACT_AND_PASSWORD, $state))) {
            return $redirection;
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedPasswordOk = $this->handlePassword($state);

            $isContactAllowed = ContactPermit::NO !== $state->artisan->getContactAllowed();

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

        return $this->render('iu_form/contact_and_password.html.twig', [
            'form'               => $form,
            'errors'             => $form->getErrors(true),
            'noindex'            => true,
            'is_update'          => !$state->isNew(),
            'session_start_time' => $state->getStarted(),
            'big_error_message'  => $this->getRestoreFailedMessage($state),
        ]);
    }

    private function validatePassword(FormInterface $form, IuState $state): void
    {
        if (!$form->isSubmitted() || $state->isNew()) {
            return;
        }

        $changePassword = $form->get(ContactAndPassword::FLD_CHANGE_PASSWORD);
        $password = $form->get(ContactAndPassword::FLD_PASSWORD);

        if (!($changePassword->getData() ?? false) && !Password::verify($state->artisan, $state->previousPassword)) {
            $password->addError(new FormError('Wrong password. To change your password, please select the "'.Texts::WANT_TO_CHANGE_PASSWORD.'" checkbox.'));
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
