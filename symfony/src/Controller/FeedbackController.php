<?php

declare(strict_types=1);

namespace App\Controller;

use App\Captcha\CaptchaService;
use App\Form\FeedbackType;
use App\Service\EmailService;
use App\ValueObject\Feedback;
use App\ValueObject\Routing\RouteName;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class FeedbackController extends AbstractController
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly EmailService $emailService,
        private readonly CaptchaService $captcha,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/feedback', RouteName::FEEDBACK_FORM)]
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
            try {
                $this->sendFeedback($feedback);

                return $this->redirectToRoute(RouteName::FEEDBACK_SENT);
            } catch (TransportExceptionInterface $exception) {
                $this->logger->error('Exception while sending feedback.', ['exception' => $exception]);

                $errorMessage = 'Could not sent the message due to a server error. Sorry for the inconvenience!';
                $form->get(FeedbackType::FLD_DETAILS)->addError(new FormError($errorMessage));
            }
        }

        return $this->render('feedback/feedback.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/feedback-sent', RouteName::FEEDBACK_SENT)]
    public function feedbackSent(): Response
    {
        return $this->render('feedback/feedback_sent.html.twig');
    }

    /**
     * @throws TransportExceptionInterface
     */
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
