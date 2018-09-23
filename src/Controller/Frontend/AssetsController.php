<?php

namespace App\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AssetsController extends AbstractController
{
    /**
     * @Route("/main.js", name="main.js")
     */
    public function main_js()
    {
        return $this->render('assets/main.twig.js', []);
    }
}
