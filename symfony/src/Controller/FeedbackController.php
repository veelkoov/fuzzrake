<?php

declare(strict_types=1);

namespace App\Controller;

use App\Captcha\CaptchaService;
use App\Form\FeedbackType;
use App\ValueObject\Feedback;
use App\ValueObject\Messages\EmailNotificationV1;
use App\ValueObject\Routing\RouteName;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class FeedbackController extends AbstractController
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly MessageBusInterface $messageBus,
        private readonly CaptchaService $captcha,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/feedback', RouteName::FEEDBACK_FORM)]
    public function feedback(Request $request, Session $session): Response
    {
        $feedback = new Feedback();

        if (null !== $maker = $request->query->get('maker')) { // grep-maker-query-parameter
            $feedback->maker = $maker;
        }

        $form = $this->createForm(FeedbackType::class, $feedback, [
            'router' => $this->router,
        ]);

        $captcha = $this->captcha->getCaptcha($session);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid() && $captcha->hasBeenSolved($request, $form)) {
            try {
                $this->sendFeedback($feedback);

                return $this->redirectToRoute(RouteName::FEEDBACK_SENT);
            } catch (ExceptionInterface $exception) {
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
     * @throws ExceptionInterface
     */
    private function sendFeedback(Feedback $feedback): void
    {
        $contents = <<<contents
            Subject: $feedback->subject
            Maker: $feedback->maker
            Details:
            $feedback->details
            contents;

        $this->messageBus->dispatch(new EmailNotificationV1('Feedback submitted', $contents));
    }
}
