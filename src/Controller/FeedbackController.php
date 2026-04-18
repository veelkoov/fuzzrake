<?php

declare(strict_types=1);

namespace App\Controller;

use App\Captcha\CaptchaService;
use App\Form\FeedbackType;
use App\Service\EmailService;
use App\ValueObject\Feedback;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class FeedbackController extends AbstractController
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly EmailService $emailService,
        private readonly CaptchaService $captcha,
    ) {
    }

    #[Route('/feedback', 'rt_feedback_form')]
    public function feedback(Request $request, Session $session): Response
    {
        $feedback = new Feedback();

        if (null !== $creator = $request->query->get('creator')) { // grep-creator-query-parameter
            $feedback->creator = $creator;
        }

        $form = $this->createForm(FeedbackType::class, $feedback, [
            'router' => $this->router,
        ])->handleRequest($request);
        $captcha = $this->captcha->getCaptcha($session)->handleRequest($request, $form);

        if ($form->isSubmitted() && $form->isValid() && $captcha->isSolved()) {
            $this->sendFeedback($feedback);

            return $this->redirectToRoute('rt_feedback_sent');
        }

        return $this->render('feedback/feedback.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/feedback-sent', 'rt_feedback_sent')]
    public function feedbackSent(): Response
    {
        return $this->render('feedback/feedback_sent.html.twig');
    }

    private function sendFeedback(Feedback $feedback): void
    {
        $contents = <<<contents
            Subject: $feedback->subject
            Creator: $feedback->creator
            Details:
            $feedback->details
            contents;

        $this->emailService->send('Feedback submitted', $contents);
    }
}
