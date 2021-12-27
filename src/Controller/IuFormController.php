<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\IuFormUtils\Steps;
use App\DataDefinitions\ContactPermit;
use App\Entity\Artisan as ArtisanE;
use App\Form\IuForm;
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
    #[Route(path: '/iu_form/fill/{makerId}', name: RouteName::IU_FORM)]
    #[Cache(maxage: 0, public: false)]
    public function iuForm(Request $request, ?string $makerId = null): Response
    {
        $artisan = $this->getArtisanByMakerIdOrThrow404($makerId);
        $steps = new Steps($request->getSession());

        if (!$steps->captchaDone() && null !== ($result = $this->stepCaptcha($request, $steps, $makerId))) {
            return $result;
        }

        return $this->stepDataForm($request, $steps, $makerId, $artisan);
    }

    private function stepDataForm(Request $request, Steps $steps, ?string $makerId, Artisan $artisan): Response
    {
        $isNew = null === $artisan->getId();
        $previousPassword = $artisan->getPassword();
        $wasContactAllowed = ContactPermit::NO !== $artisan->getContactAllowed();

        $artisan->setPassword(''); // Should never appear in the form

        $form = $this->getIuForm($artisan, $makerId);
        $form->handleRequest($request);
        $this->validatePhotosCopyright($form, $artisan);

        if ($form->isSubmitted() && $form->isValid()) {
            $artisan->setContactInfoOriginal($artisan->getContactInfoObfuscated());
            StrUtils::fixNewlines($artisan);

            if ($isNew) {
                $passwordOk = true;
                Password::encryptOn($artisan);
            } elseif (password_verify($artisan->getPassword(), $previousPassword)) {
                $passwordOk = true;
                $artisan->setPassword($previousPassword); // Was already hashed; use old hash - must not appear changed
            } else {
                $passwordOk = false;
                Password::encryptOn($artisan); // Will become new password if confirmed with maintainer
            }

            $isContactAllowed = ContactPermit::NO !== $artisan->getContactAllowed();

            if ($this->iuFormService->submit($artisan)) {
                $steps->reset();

                return $this->redirectToRoute(RouteName::IU_FORM_CONFIRMATION, [
                    'isNew'          => $isNew ? 'yes' : 'no',
                    'passwordOk'     => $passwordOk ? 'yes' : 'no',
                    'contactAllowed' => $isContactAllowed ? ($wasContactAllowed ? 'yes' : 'was_no') : 'is_no',
                ]);
            } else {
                $form->addError(new FormError('There was an error while trying to submit the form.'
                    .' Please contact the website maintainer. I am terribly sorry for this inconvenience!'));
            }
        }

        return $this->renderForm('iu_form/iu_form.html.twig', [
            'form'             => $form,
            'noindex'          => true,
            'submitted'        => $form->isSubmitted(),
            'disable_tracking' => true,
            'is_update'        => !$isNew,
        ]);
    }

    private function stepCaptcha(Request $request, Steps $steps, ?string $makerId): ?Response
    {
        if ($request->isMethod('POST') && $this->isReCaptchaTokenOk($request, 'iu_form_captcha')) {
            $steps->markCaptchaDone();

            return null;
        }

        // TODO: Warn about failed captcha, provide suggestions

        return $this->render('iu_form/captcha_and_rules.html.twig', [
            'next_step_url' => $this->generateUrl(RouteName::IU_FORM, ['makerId' => $makerId]),
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

    #[Route(path: '/iu_form/{makerId}')]
    #[Cache(maxage: 0, public: false)]
    public function oldAddressRedirect(?string $makerId = null): Response
    {
        return $this->redirectToRoute(RouteName::IU_FORM, ['makerId' => $makerId]);
    }

    private function getIuForm(Artisan $artisan, ?string $makerId): FormInterface
    {
        return $this->createForm(IuForm::class, $artisan, [
            IuForm::PHOTOS_COPYRIGHT_OK => '' !== $makerId && '' !== $artisan->getPhotoUrls(),
            IuForm::OPT_ROUTER          => $this->router,
        ]);
    }

    private function validatePhotosCopyright(FormInterface $form, Artisan $artisan): void
    {
        $field = $form->get(IuForm::FLD_PHOTOS_COPYRIGHT);

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
}
