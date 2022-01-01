<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\IuFormUtils\IuState;
use App\DataDefinitions\ContactPermit;
use App\Entity\Artisan as ArtisanE;
use App\Form\InclusionUpdate\ContactAndPassword;
use App\Form\InclusionUpdate\Data;
use App\Repository\ArtisanRepository;
use App\Service\EnvironmentsService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\IuSubmissions\IuSubmissionService;
use App\Utils\Password;
use App\Utils\StrUtils;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\UnexpectedResultException;
use Psr\Log\LoggerInterface;
use ReCaptcha\ReCaptcha;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class IuFormController extends AbstractRecaptchaBackedController
{
    public function __construct(
        ReCaptcha $reCaptcha,
        EnvironmentsService $environments,
        LoggerInterface $logger,
        private readonly IuSubmissionService $iuFormService,
        private readonly RouterInterface $router,
        private readonly ArtisanRepository $artisanRepository,
    ) {
        parent::__construct($reCaptcha, $environments, $logger);
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Route(path: '/iu_form/start/{makerId}', name: RouteName::IU_FORM_START)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormStart(Request $request, ?string $makerId = null): Response
    {
        $artisan = $this->getArtisanByMakerIdOrThrow404($makerId);
        $state = new IuState($request->getSession(), $makerId, $artisan);
        $state->restore(); // TODO: Report any errors in $result
        $artisan->setPassword(''); // Should never appear in the form

        if ($request->isMethod('POST') && $this->isReCaptchaTokenOk($request, 'iu_form_captcha')) {
            $state->markCaptchaDone();

            return $this->redirectToRoute(RouteName::IU_FORM_DATA, ['makerId' => $state->makerId]);
        }

        // TODO: Warn about failed captcha, provide suggestions

        return $this->render('iu_form/captcha_and_rules.html.twig', [
            'next_step_url' => $this->generateUrl(RouteName::IU_FORM_START, ['makerId' => $state->makerId]),
        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Route(path: '/iu_form/data/{makerId}', name: RouteName::IU_FORM_DATA)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormData(Request $request, ?string $makerId = null): Response
    {
        $artisan = $this->getArtisanByMakerIdOrThrow404($makerId);
        $state = new IuState($request->getSession(), $makerId, $artisan);
        $state->restore(); // TODO: Report any errors in $result
        $artisan->setPassword(''); // Should never appear in the form

        $form = $this->createForm(Data::class, $state->artisan, [
            Data::PHOTOS_COPYRIGHT_OK => !$state->isNew() && '' !== $state->artisan->getPhotoUrls(),
            Data::OPT_ROUTER          => $this->router,
        ]);

        $form->handleRequest($request);
        $this->validatePhotosCopyright($form, $state->artisan);

        if ($form->isSubmitted() && $form->isValid()) {
            $state->save();

            $state->markDataDone();

            return $this->redirectToRoute(RouteName::IU_FORM_CONTACT_AND_PASSWORD, ['makerId' => $state->makerId]);
        }

        return $this->renderForm('iu_form/iu_form.html.twig', [
            'form'             => $form,
            'noindex'          => true,
            'submitted'        => $form->isSubmitted(),
            'disable_tracking' => true,
            'is_update'        => !$state->isNew(),
        ]);
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Route(path: '/iu_form/contact_and_password/{makerId}', name: RouteName::IU_FORM_CONTACT_AND_PASSWORD)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormContactAndPassword(Request $request, ?string $makerId = null): Response
    {
        $artisan = $this->getArtisanByMakerIdOrThrow404($makerId);
        $state = new IuState($request->getSession(), $makerId, $artisan);
        $state->restore(); // TODO: Report any errors in $result
        $artisan->setPassword(''); // Should never appear in the form

        $form = $this->createForm(ContactAndPassword::class, $state->artisan);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            StrUtils::fixNewlines($state->artisan);
            $state->artisan->setContactInfoOriginal($state->artisan->getContactInfoObfuscated()); // grep-contact-updates-magic

            $submittedPasswordOk = $this->handlePassword($state);

            $isContactAllowed = ContactPermit::NO !== $state->artisan->getContactAllowed();

            if ($this->iuFormService->submit($state->artisan)) {
                $state->reset();

                return $this->redirectToRoute(RouteName::IU_FORM_CONFIRMATION, [
                    'isNew'          => $state->isNew() ? 'yes' : 'no',
                    'passwordOk'     => $submittedPasswordOk ? 'yes' : 'no',
                    'contactAllowed' => $isContactAllowed ? ($state->wasContactAllowed ? 'yes' : 'was_no') : 'is_no',
                ]);
            } else {
                $form->addError(new FormError('There was an error while trying to submit the form.'
                    .' Please contact the website maintainer. I am terribly sorry for this inconvenience!'));
            }
        }

        return $this->renderForm('iu_form/contact_and_password.html.twig', [
            'form'             => $form,
            'noindex'          => true,
            'disable_tracking' => true,
            'is_update'        => !$state->isNew(),
        ]);
    }

    #[Route(path: '/iu_form/confirmation', name: RouteName::IU_FORM_CONFIRMATION)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormConfirmation(Request $request): Response
    {
        return $this->render('iu_form/confirmation.html.twig', [
            'disable_tracking'       => true,
            'password_ok'            => 'yes' === $request->get('passwordOk', 'no'),
            'contact_allowed'        => 'yes' === $request->get('contactAllowed', 'is_no'),
            'no_selected_previously' => 'was_no' === $request->get('contactAllowed', 'is_no'),
        ]);
    }

    #[Route(path: '/iu_form/fill/{makerId}')]
    #[Route(path: '/iu_form/{makerId}')]
    #[Cache(maxage: 0, public: false)]
    public function oldAddressRedirect(?string $makerId = null): Response
    {
        return $this->redirectToRoute(RouteName::IU_FORM_START, ['makerId' => $makerId]);
    }

    private function validatePhotosCopyright(FormInterface $form, Artisan $artisan): void
    {
        $field = $form->get(Data::FLD_PHOTOS_COPYRIGHT);

        if ('' !== $artisan->getPhotoUrls() && 'OK' !== ($field->getData()[0] ?? null)) {
            $field->addError(new FormError('Permission to use the photos is required'));
        }
    }

    private function getArtisanByMakerIdOrThrow404(?string $makerId): Artisan
    {
        try {
            return Artisan::wrap($makerId ? $this->artisanRepository->findByMakerId($makerId) : new ArtisanE());
        } catch (UnexpectedResultException) {
            throw $this->createNotFoundException('Failed to find a maker with given ID');
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
        } elseif (password_verify($data->artisan->getPassword(), $data->previousPassword)) {
            $data->artisan->setPassword($data->previousPassword); // Was already hashed; use old hash - must not appear changed
            return true;
        } else {
            Password::encryptOn($data->artisan); // Will become new password if confirmed with maintainer
            return false;
        }
    }
}
