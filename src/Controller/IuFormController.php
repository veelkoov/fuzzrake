<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Artisan;
use App\Form\IuForm;
use App\Repository\ArtisanRepository;
use App\Utils\IuSubmissions\IuSubmissionService;
use Doctrine\ORM\UnexpectedResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Form\FormError;
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

        $form = $this->createForm(IuForm::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->isReCaptchaTokenOk($request, 'iu_form_submit')) {
            $artisan->setContactInfoOriginal($artisan->getContactInfoObfuscated());
            $iuFormService->submit($artisan);

            return $this->redirectToRoute('data_updates', ['_fragment' => 'UPDATES_SENT']); // TODO: Should show a nice message instead
        }

        return $this->render('iu_form/iu_form.html.twig', [
            'form'      => $form->createView(),
            'noindex'   => true,
            'submitted' => $form->isSubmitted(),
        ]);
    }
}
