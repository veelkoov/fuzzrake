<?php

declare(strict_types=1);

namespace App\Controller;

use App\Feedback\Feedback;
use App\Form\FeedbackType;
use App\Utils\Notifications\MessengerInterface;
use App\Utils\Notifications\Notification;
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
    ) {
    }

    #[Route('/feedback', RouteName::FEEDBACK_FORM)]
    public function feedback(Request $request): Response
    {
        $feedback = new Feedback();

        if (null !== $maker = $request->query->get('maker')) {
            $feedback->maker = $maker;
        }

        $form = $this->createForm(FeedbackType::class, $feedback, [
            'router' => $this->router,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            if ($this->sendFeedback($feedback)) {
                return $this->redirectToRoute(RouteName::FEEDBACK_SENT);
            }
        }

        return $this->renderForm('feedback/feedback.html.twig', [
            'feedback_form' => $form,
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
