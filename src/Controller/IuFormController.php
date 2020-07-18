<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\IuForm;
use App\Repository\ArtisanRepository;
use Doctrine\ORM\UnexpectedResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class IuFormController extends AbstractController
{
    /**
     * @Route("/iu_form/{makerId}", name="iu_form")
     * @Cache(maxage=0, public=false)
     *
     * @throws NotFoundHttpException
     */
    public function iuForm(Request $request, ArtisanRepository $artisanRepository, string $makerId): Response
    {
        try {
            $artisan = $artisanRepository->findByMakerId($makerId);
        } catch (UnexpectedResultException $e) {
            throw $this->createNotFoundException('Failed to find a maker with given ID');
        }

        $form = $this->createForm(IuForm::class, $artisan);
        $form->handleRequest($request);

        return $this->render('iu_form/iu_form.html.twig', ['form' => $form->createView()]);
    }
}
