<?php

declare(strict_types=1);

namespace App\Captcha;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Controller extends AbstractController
{
    public function challenge(CaptchaService $service, SessionInterface $session, FormView $form): Response
    {
        $form->setRendered();

        $captcha = $service->getCaptcha($session);

        if ($captcha->isSolved()) {
            return new Response(status: Response::HTTP_NO_CONTENT);
        }

        return $this->render('captcha/challenge.html.twig', [
            'form' => $form,
            'challenge' => $captcha->getChallenge(),
        ]);
    }
}
