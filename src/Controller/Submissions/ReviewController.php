<?php

declare(strict_types=1);

namespace App\Controller\Submissions;

use App\Controller\Utils\ButtonClickedTrait;
use App\Data\Definitions\Fields\Fields;
use App\Data\Submission\Filter;
use App\Data\Submission\Status;
use App\Entity\DiscussionTopic;
use App\Entity\Submission;
use App\Entity\User;
use App\Form\Submission\FilterType;
use App\Form\Submission\TopicType;
use App\IuHandling\Import\ImportService;
use App\Repository\DiscussionTopicRepository;
use App\Repository\SubmissionRepository;
use App\Security\Role;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::REVIEWER->value)]
#[Cache(maxage: 0, public: false, noStore: true)]
class ReviewController extends AbstractController
{
    use ButtonClickedTrait;

    private const string SESSION_SUBMISSIONS_FILTER = 'submissions_filter_settings';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SubmissionRepository $submissionRepository,
        private readonly ImportService $importService,
        private readonly DiscussionTopicRepository $topicRepository,
    ) {
    }

    /**
     * @param positive-int $page
     */
    #[Route(path: '/submissions/{page}/', name: RouteName::SUBMISSIONS_LIST, requirements: ['page' => Requirement::POSITIVE_INT], defaults: ['page' => 1])]
    public function list(Request $request, int $page): Response
    {
        if ($this->isGranted(Role::ADMIN->value)) {
            $filter = $request->getSession()->get(self::SESSION_SUBMISSIONS_FILTER);
            if (!$filter instanceof Filter) {
                $filter = new Filter();
            }

            $filterForm = $this->createForm(FilterType::class, $filter);
            $filterForm->handleRequest($request);

            if ($filterForm->isSubmitted() && $filterForm->isValid()) {
                $request->getSession()->set(self::SESSION_SUBMISSIONS_FILTER, $filter);

                $this->redirectToRoute(RouteName::SUBMISSIONS_LIST, ['page' => $page]);
            }
        } else {
            $filter = new Filter();
            $filter->statuses = [Status::IN_REVIEW];
            $filterForm = null;
        }

        $submissionsPage = $this->submissionRepository->getPage($filter, $page);

        return $this->render('submissions/list.html.twig', [
            'filter_form' => $filterForm,
            'submissions_page' => $submissionsPage,
        ]);
    }

    // TODO: Allow non-admin only to submission in review
    #[Route(path: '/submission/{id}/review', name: RouteName::SUBMISSION_REVIEW)]
    public function submissionReview(#[MapEntity] Submission $submission, #[CurrentUser] User $user, Request $request): Response
    {
        $importData = $this->importService->getImportDataFor($submission);

        $newTopic = new DiscussionTopic($submission, $user);
        $newTopicForm = $this->createForm(TopicType::class, $newTopic)->handleRequest($request);

        if ($newTopicForm->isSubmitted() && $newTopicForm->isValid()) {
            $this->entityManager->persist($newTopic);
            $this->entityManager->flush();

            return $this->redirectToRoute(RouteName::SUBMISSION_REVIEW, ['id' => $submission->getId()]);
        }

        return $this->render('submissions/review.html.twig', [
            'importData' => $importData,
            'submission' => $submission,
            'fields' => Fields::inIuForm(),
            'newTopicForm' => $newTopicForm,
            'topics' => $this->topicRepository->getSubmissionDiscussions($submission),
        ]);
    }
}
