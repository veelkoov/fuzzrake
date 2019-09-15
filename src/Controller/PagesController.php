<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PagesController extends AbstractController
{
    /**
     * @Route("/info.html", name="info")
     *
     * @return Response
     */
    public function info(): Response
    {
        return $this->render('pages/info.html.twig', []);
    }

    /**
     * @Route("/tracking.html", name="tracking")
     *
     * @return Response
     */
    public function tracking(): Response
    {
        return $this->render('pages/tracking.html.twig', []);
    }

    /**
     * @Route("/whoopsies.html", name="whoopsies")
     *
     * @return Response
     */
    public function whoopsies(): Response
    {
        return $this->render('pages/whoopsies.html.twig', []);
    }

    /**
     * @Route("/maker_ids.html", name="maker_ids")
     *
     * @return Response
     */
    public function makerIds(): Response
    {
        return $this->render('pages/maker_ids.html.twig', []);
    }
}
