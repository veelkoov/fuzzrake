<?php

declare(strict_types=1);

namespace App\Controller\Submissions;

use App\Controller\Utils\ButtonClickedTrait;
use App\Data\Definitions\Fields\Fields;
use App\Data\Submission\Filter;
use App\Data\Submission\Status;
use App\Entity\Post;
use App\Entity\PostVote;
use App\Entity\Submission;
use App\Entity\User;
use App\Form\Submission\FilterType;
use App\Form\Submission\PostType;
use App\IuHandling\Import\ImportService;
use App\Repository\PostRepository;
use App\Repository\PostVoteRepository;
use App\Repository\SubmissionRepository;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_REVIEWER')]
#[Cache(maxage: 0, public: false, noStore: true)]
class ReviewController extends AbstractController
{
    use ButtonClickedTrait;

    private const string SESSION_SUBMISSIONS_FILTER = 'submissions_filter_settings';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SubmissionRepository $submissionRepository,
        private readonly ImportService $importService,
        private readonly PostRepository $postRepository,
        private readonly PostVoteRepository $postVoteRepository,
    ) {
    }

    /**
     * @param positive-int $page
     */
    #[Route(path: '/submissions/{page}/', name: RouteName::SUBMISSIONS_LIST, requirements: ['page' => Requirement::POSITIVE_INT], defaults: ['page' => 1])]
    public function list(Request $request, int $page): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
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

    #[IsGranted('review', 'submission')]
    #[Route(path: '/submission/{id}/review', name: RouteName::SUBMISSION_REVIEW)]
    public function submissionReview(#[MapEntity] Submission $submission, #[CurrentUser] User $user, Request $request): Response
    {
        $importData = $this->importService->getImportDataFor($submission);

        $newTopic = new Post($user, $submission);
        $newTopicForm = $this->createForm(PostType::class, $newTopic, [
            PostType::OPT_PREFIX => 'new_topic',
        ]);
        $newTopicForm->handleRequest($request);

        if ($newTopicForm->isSubmitted() && $newTopicForm->isValid()) {
            $this->entityManager->persist($newTopic);
            $this->entityManager->flush();

            return $this->redirectToReview($submission);
        }

        $topics = [];

        foreach ($this->postRepository->getSubmissionTopics($submission) as $topic) {
            $responseForm = $this->createForm(PostType::class, new Post($user, $submission, $topic), [
                PostType::OPT_PREFIX => "topic_{$topic->getId()}",
            ]);
            $responseForm->handleRequest($request);

            if ($responseForm->isSubmitted() && $responseForm->isValid()) {
                $this->entityManager->persist($responseForm->getData());
                $this->entityManager->flush();

                return $this->redirectToReview($submission);
            }

            $topics[] = [
                'entity' => $topic,
                'response_form' => $responseForm->createView(),
            ];
        }

        return $this->render('submissions/review.html.twig', [
            'import_data' => $importData,
            'submission' => $submission,
            'fields' => Fields::inIuForm(),
            'new_topic_form' => $newTopicForm,
            'topics' => $topics,
        ]);
    }

    #[IsGranted('vote', 'post')]
    #[Route(path: '/submission/{id}/vote-post/{postId}/{positive}', name: 'route_vote_post')]
    public function votePost(#[MapEntity] Submission $submission, #[MapEntity(id: 'postId')] Post $post, #[CurrentUser] User $user,
        Request $request, bool $positive): Response
    {
        $votes = $this->postVoteRepository->findFor($user, $post);
        $shouldRecreateVote = 0 === count($votes) || array_first($votes)->isPositive() !== $positive;

        foreach ($votes as $vote) {
            $this->entityManager->remove($vote);
        }
        if ($shouldRecreateVote) {
            $this->entityManager->flush();
            $this->entityManager->persist(new PostVote($user, $post, $positive));
        }
        $this->entityManager->flush();

        return $this->redirectToReview($submission);
    }

    private function redirectToReview(Submission $submission): RedirectResponse
    {
        return $this->redirectToRoute(RouteName::SUBMISSION_REVIEW, ['id' => $submission->getId()]);
    }
}
