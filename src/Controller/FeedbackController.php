<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\FeedbackType;
use App\Service\Captcha;
use App\Service\Notifications\MessengerInterface;
use App\ValueObject\Feedback;
use App\ValueObject\Notification;
use App\ValueObject\Routing\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

class FeedbackController extends AbstractController
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly MessengerInterface $messenger,
        private readonly Captcha $captcha,
    ) {
    }

    #[Route('/feedback', RouteName::FEEDBACK_FORM)]
    public function feedback(Request $request): Response
    {
        $feedback = new Feedback();

        if (null !== $maker = $request->query->get('maker')) { // grep-maker-query-parameter
            $feedback->maker = $maker;
        }

        $form = $this->createForm(FeedbackType::class, $feedback, [
            'router' => $this->router,
        ]);

        $big_error_message = '';

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            if (!$this->captcha->isValid($request, 'feedback_form_captcha')) {
                $big_error_message = "Captcha failed. Please retry submitting. If this doesn't help, try another browser or other network connection.";
            } elseif (!$this->sendFeedback($feedback)) {
                $big_error_message = 'Could not sent the message due to server error. Sorry for the inconvenience!';
            } else {
                return $this->redirectToRoute(RouteName::FEEDBACK_SENT);
            }
        }

        // TODO: https://stackoverflow.com/questions/41665935/html5-form-validation-before-recaptchas
        return $this->renderForm('feedback/feedback.html.twig', [
            'form'              => $form,
            'big_error_message' => $big_error_message,
        ]);
    }

    #[Route('/feedback-sent', RouteName::FEEDBACK_SENT)]
    public function feedbackSent(): Response
    {
        return $this->render('feedback/feedback_sent.html.twig');
    }

    private function sendFeedback(Feedback $feedback): bool
    {
        $contents = <<<contents
            Subject: $feedback->subject
            Maker: $feedback->maker
            Details:
            $feedback->details
            contents;

        $notification = new Notification('Feedback submitted', $contents);

        return $this->messenger->send($notification);
    }
}
