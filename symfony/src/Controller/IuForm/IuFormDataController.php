<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\Captcha\CaptchaService;
use App\Controller\IuForm\Utils\IuSubject;
use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Field;
use App\Form\InclusionUpdate\Data;
use App\IuHandling\Submission\SubmissionService;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use App\Utils\Collections\ArrayReader;
use App\Utils\Password;
use App\ValueObject\Routing\RouteName;
use App\ValueObject\Texts;
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

class IuFormDataController extends AbstractIuFormController
{
    /**
     * @throws NotFoundHttpException
     */
    #[Route(path: '/iu_form/data/{makerId}', name: RouteName::IU_FORM_DATA)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormData(
        Request $request,
        SessionInterface $session,
        CaptchaService $captchaService,
        RouterInterface $router,
        SubmissionService $submissionService,
        ?string $makerId = null,
    ): Response {
        $subject = $this->getSubject($makerId);

        $form = $this->createForm(Data::class, $subject->creator, [
            Data::OPT_PHOTOS_COPYRIGHT_OK => !$subject->isNew && $subject->creator->hasData(Field::URL_PHOTOS),
            Data::OPT_CURRENT_EMAIL_ADDRESS => $subject->previousEmailAddress,
            'router' => $router,
        ])->handleRequest($request);
        $captcha = $captchaService->getCaptcha($session)->handleRequest($request, $form);

        $this->validatePassword($form, $subject);
        $this->validatePhotosCopyright($form, $subject->creator);
        $this->validateMakerId($form, $subject->creator);

        if ($form->isSubmitted() && $form->isValid() && $captcha->isSolved()) {
            $submittedPasswordOk = $this->handlePassword($subject);

            $isContactAllowed = ContactPermit::NO !== $subject->creator->getContactAllowed();

            if ($submissionService->submit($subject->creator)) {
                return $this->redirectToRoute(RouteName::IU_FORM_CONFIRMATION, [
                    'isNew'          => $subject->isNew ? 'yes' : 'no',
                    'passwordOk'     => $submittedPasswordOk ? 'yes' : 'no',
                    'contactAllowed' => $isContactAllowed ? ($subject->wasContactAllowed ? 'yes' : 'was_no') : 'is_no',
                    'makerId'        => $makerId,
                    // TODO 'submissionId'   =>
                ]);
            } else {
                $form->addError(new FormError('There was an error while trying to submit the form. Please note the time of seeing this message and contact the website maintainer. I am terribly sorry for the inconvenience!'));
            }
        }

        return $this->render('iu_form/data.html.twig', [
            'form'                => $form,
            'errors'              => $form->getErrors(true),
            'noindex'             => true,
            'submitted'           => $form->isSubmitted(),
            'is_new'              => $subject->isNew,
            'creator_id'          => $makerId ?? self::NEW_CREATOR_ID_PLACEHOLDER,
            'was_contact_allowed' => $subject->wasContactAllowed,
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

    private function validateMakerId(FormInterface $form, Creator $creator): void
    {
        try {
            $creatorIdOwner = $this->creatorRepository->findByMakerId($creator->getMakerId());

            if ($creatorIdOwner->getId() !== $creator->getId()) {
                $form->get(Data::FLD_MAKER_ID)
                    ->addError(new FormError('This maker ID has been already used by another maker.'));
            }
        } catch (NoResultException) {
            // Unused ID = OK
        }
    }

    private function validatePassword(FormInterface $form, IuSubject $subject): void
    {
        if (!$form->isSubmitted() || $subject->isNew) {
            return;
        }

        $verificationAcknowledgmentField = $form->get(Data::FLD_VERIFICATION_ACKNOWLEDGEMENT);

        $wantsPasswordChange = $form->get(Data::FLD_CHANGE_PASSWORD)->getData() ?? false;
        $contactAllowed = ContactPermit::NO !== $form->get(Data::FLD_CONTACT_ALLOWED)->getData();
        $verificationAcknowledgment = $verificationAcknowledgmentField->getData() ?? false;

        if ($wantsPasswordChange && (!$contactAllowed || !$subject->wasContactAllowed) && !$verificationAcknowledgment) {
            $errorMessage = 'Your action is required; your submission will be rejected otherwise.';
            $verificationAcknowledgmentField->addError(new FormError($errorMessage));
        }

        if (!$wantsPasswordChange && !Password::verify($subject->creator, $subject->previousPassword)) {
            $errorMessage = 'Wrong password. To change your password, please select the "'.Texts::WANT_TO_CHANGE_PASSWORD.'" checkbox.';
            $form->get(Data::FLD_PASSWORD)->addError(new FormError($errorMessage));
        }
    }

    /**
     * @return bool If the I/U submission requires confirmation (password didn't match previous one)
     */
    private function handlePassword(IuSubject $subject): bool
    {
        if ($subject->isNew) {
            Password::encryptOn($subject->creator);

            return true;
        } elseif (Password::verify($subject->creator, $subject->previousPassword)) {
            $subject->creator->setPassword($subject->previousPassword); // Was already hashed; use old hash - must not appear changed

            return true;
        } else {
            Password::encryptOn($subject->creator); // Will become new password if confirmed with maintainer

            return false;
        }
    }
}
