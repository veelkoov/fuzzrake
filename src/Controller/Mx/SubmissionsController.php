<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\DataDefinitions\Fields\Fields;
use App\Entity\Submission;
use App\Form\Mx\SubmissionType;
use App\Repository\SubmissionRepository;
use App\Submissions\Manager;
use App\Submissions\ManagerConfigError;
use App\Submissions\SubmissionData;
use App\Submissions\SubmissionsService;
use App\Utils\UnbelievableRuntimeException;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\NonUniqueResultException;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mx/submissions')]
class SubmissionsController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SubmissionsService $service,
        private readonly SubmissionRepository $repository,
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

    /**
     * @throws NonUniqueResultException
     */
    #[Route(path: '/{id}', name: RouteName::MX_SUBMISSION)]
    #[Cache(maxage: 0, public: false)]
    public function submission(Request $request, string $id): Response
    {
        $submissionData = $this->getSubmissionData($id);
        $submission = $this->getSubmission($id);
        [$directivesError, $manager] = $this->getManager($submission);
        $update = $this->service->getUpdate($submissionData, $manager);

        $form = $this->createForm(SubmissionType::class, $submission);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->repository->add($submission, true);
        }

        if (null !== $directivesError) {
            $message = "The directives have been ignored completely due to an error. $directivesError";
            $form['directives']?->addError(new FormError($message));
        }

        return $this->render('mx/submissions/submission.html.twig', [
            'update' => $update,
            'fields' => Fields::iuFormAffected(),
            'form'   => $form->createView(),
        ]);
    }

    private function getSubmissionData(string $id): SubmissionData
    {
        $result = $this->service->getSubmissionById($id);

        if (null === $result) {
            throw $this->createNotFoundException("Couldn't find the submission with the given ID");
        }

        return $result;
    }

    /**
     * @throws NonUniqueResultException
     */
    private function getSubmission(string $id): Submission
    {
        return $this->repository->findByStrId($id) ?? (new Submission())->setStrId($id);
    }

    /**
     * @return array{0: ?string, 1: Manager}
     */
    private function getManager(Submission $submission): array
    {
        $directives = $submission->getDirectives();
        $strId = $submission->getStrId();

        try {
            return [null, new Manager($this->logger, "with {$strId}:\n$directives")]; // TODO: Remove "with"
        } catch (ManagerConfigError $error) {
            $directivesError = $error->getMessage();

            try {
                return [$directivesError, new Manager($this->logger, '')];
            } catch (ManagerConfigError $error) {
                throw new UnbelievableRuntimeException($error);
            }
        }
    }
}
