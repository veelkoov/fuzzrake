<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Artisan;
use App\Form\IuForm;
use App\Repository\ArtisanRepository;
use App\Utils\IuSubmissions\IuSubmissionService;
use App\Utils\StrUtils;
use Doctrine\ORM\UnexpectedResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class IuFormController extends AbstractRecaptchaBackedController
{
    /**
     * @Route("/iu_form/{makerId}", name="iu_form")
     * @Cache(maxage=0, public=false)
     *
     * @throws NotFoundHttpException
     */
    public function iuForm(Request $request, ArtisanRepository $artisanRepository, IuSubmissionService $iuFormService, ?string $makerId = null): Response
    {
        try {
            $artisan = $makerId ? $artisanRepository->findByMakerId($makerId) : new Artisan();
            $artisan->setPasscode(''); // Should never appear in the form
        } catch (UnexpectedResultException $e) {
            throw $this->createNotFoundException('Failed to find a maker with given ID');
        }

        $form = $this->getIuForm($artisan, $makerId);
        $form->handleRequest($request);
        $this->validatePhotosCopyright($form, $artisan);

        if ($form->isSubmitted() && $form->isValid() && $this->isReCaptchaTokenOk($request, 'iu_form_submit')) {
            $artisan->setContactInfoOriginal($artisan->getContactInfoObfuscated());
            StrUtils::fixNewlines($artisan);

            if ($iuFormService->submit($artisan)) {
                return $this->redirectToRoute('iu_form_confirmation');
            } else {
                $form->addError(new FormError('There was an error while trying to submit the form.'
                .' Please contact the website maintainer. I am terribly sorry for this inconvenience!'));
            }
        }

        return $this->render('iu_form/iu_form.html.twig', [
            'form'      => $form->createView(),
            'noindex'   => true,
            'submitted' => $form->isSubmitted(),
        ]);
    }

    /**
     * @Route("/iu_form_confirmation", name="iu_form_confirmation")
     * @Cache(maxage=0, public=false)
     */
    public function iuFormConfirmation(): Response
    {
        return $this->render('iu_form/confirmation.html.twig', [
        ]);
    }

    private function getIuForm(Artisan $artisan, ?string $makerId): FormInterface
    {
        return $this->createForm(IuForm::class, $artisan, [
            IuForm::PHOTOS_COPYRIGHT_OK => '' !== $makerId && '' !== $artisan->getPhotoUrls(),
        ]);
    }

    private function validatePhotosCopyright(FormInterface $form, Artisan $artisan): void
    {
        $field = $form->get(IuForm::FLD_PHOTOS_COPYRIGHT);

        if ('' !== $artisan->getPhotoUrls() && 'OK' !== ($field->getData()[0] ?? null)) {
            $field->addError(new FormError('Permission to use the photos is required'));
        }
    }
}
