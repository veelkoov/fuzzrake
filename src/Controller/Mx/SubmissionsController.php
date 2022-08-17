<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\DataDefinitions\Fields\Fields;
use App\Submissions\SubmissionsService;
use App\Utils\IuSubmissions\IuSubmission;
use App\ValueObject\Routing\RouteName;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mx/submissions')]
class SubmissionsController extends AbstractController
{
    public function __construct(
        private readonly SubmissionsService $service,
    ) {
    }

    #[Route(path: '/', name: RouteName::MX_SUBMISSIONS)]
    #[Cache(maxage: 0, public: false)]
    public function submissions(): Response
    {
        $submissions = $this->service->getSubmissions();

        return $this->render('mx/submissions/index.html.twig', [
            'submissions' => $submissions,
        ]);
    }

    #[Route(path: '/{id}', name: RouteName::MX_SUBMISSION)]
    #[Cache(maxage: 0, public: false)]
    public function submission(string $id): Response
    {
        $submission = $this->getSubmission($id);

        $update = $this->service->getUpdate($submission);

        return $this->render('mx/submissions/submission.html.twig', [
            'update'     => $update,
            'fields'     => Fields::all(), // TODO: Only required/changeable
        ]);
    }

    private function getSubmission(string $id): IuSubmission
    {
        $result = $this->service->getSubmissionById($id);

        if (null === $result) {
            throw $this->createNotFoundException("Couldn't find the submission with the given ID");
        }

        return $result;
    }
}
