<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\DataDefinitions\Fields\Fields;
use App\Form\Mx\SubmissionType;
use App\IuHandling\Exception\MissingSubmissionException;
use App\IuHandling\Import\SubmissionsService;
use App\IuHandling\Import\UpdatesService;
use App\Service\EnvironmentsService;
use App\ValueObject\Routing\RouteName;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mx/submissions')]
class SubmissionsController extends FuzzrakeAbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SubmissionsService $submissions,
        private readonly UpdatesService $updates,
        EnvironmentsService $environments,
    ) {
        parent::__construct($environments);
    }

    #[Route(path: '/', name: RouteName::MX_SUBMISSIONS)]
    #[Cache(maxage: 0, public: false)]
    public function submissions(): Response
    {
        $this->authorize();

        $submissions = $this->submissions->getSubmissions();

        return $this->render('mx/submissions/index.html.twig', [
            'submissions' => $submissions,
        ]);
    }

    #[Route(path: '/{id}', name: RouteName::MX_SUBMISSION)]
    #[Cache(maxage: 0, public: false)]
    public function submission(Request $request, string $id): Response
    {
        $this->authorize();

        try {
            $input = $this->submissions->getUpdateInputBySubmissionId($id);
        } catch (MissingSubmissionException $exception) {
            $this->logger->warning($exception);

            throw $this->createNotFoundException($exception->getMessage());
        }

        $form = $this->createForm(SubmissionType::class, $input->submission)->handleRequest($request);

        $update = $this->updates->getUpdateFor($input);

        if ($form->isSubmitted()) {
            $this->submissions->updateEntity($update);
        }

        if ($form->isSubmitted() && $form->isValid() && $update->isAccepted) {
            $this->updates->import($update);

            return $this->redirectToRoute(RouteName::MX_SUBMISSIONS);
        }

        foreach ($update->errors as $error) {
            $form->get('directives')->addError(new FormError($error));
        }

        return $this->render('mx/submissions/submission.html.twig', [
            'update' => $update,
            'fields' => Fields::iuFormAffected(),
            'form'   => $form->createView(),
        ]);
    }
}
