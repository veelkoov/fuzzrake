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
     */
    public function info(): Response
    {
        return $this->render('pages/info.html.twig', []);
    }

    /**
     * @Route("/tracking.html", name="tracking")
     */
    public function tracking(): Response
    {
        return $this->render('pages/tracking.html.twig', []);
    }

    /**
     * @Route("/whoopsies.html", name="whoopsies")
     */
    public function whoopsies(): Response
    {
        return $this->render('pages/whoopsies.html.twig', []);
    }

    /**
     * @Route("/maker_ids.html", name="maker_ids")
     */
    public function makerIds(): Response
    {
        return $this->render('pages/maker_ids.html.twig', []);
    }

    /**
     * @Route("/donate.html", name="donate")
     */
    public function donate(): Response
    {
        return $this->render('pages/donate.html.twig', []);
    }
}
