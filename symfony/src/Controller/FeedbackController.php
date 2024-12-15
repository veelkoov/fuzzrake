<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\FeedbackType;
use App\Service\Captcha;
use App\Service\DataService;
use App\ValueObject\Feedback;
use App\ValueObject\Messages\EmailNotificationV1;
use App\ValueObject\Routing\RouteName;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class FeedbackController extends AbstractController
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly MessageBusInterface $messageBus,
        private readonly DataService $dataService,
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
            } else {
                try {
                    $this->sendFeedback($feedback);

                    return $this->redirectToRoute(RouteName::FEEDBACK_SENT);
                } catch (ExceptionInterface) {
                    $big_error_message = 'Could not sent the message due to a server error. Sorry for the inconvenience!';
                }
            }
        }

        return $this->render('feedback/feedback.html.twig', [
            'form'              => $form,
            'big_error_message' => $big_error_message,
            'ooo_notice'        => $this->dataService->getOooNotice(),
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
