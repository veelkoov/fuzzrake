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
use App\Utils\DateTime\UtcClock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
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
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * @param positive-int $page
     */
    #[Route(path: '/submissions/{page}/', name: 'rt_submissions_list', requirements: ['page' => Requirement::POSITIVE_INT], defaults: ['page' => 1])]
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

                return $this->redirectToRoute('rt_submissions_list', ['page' => $page]);
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
    #[Route(path: '/submission/{id}/review', name: 'rt_submission_review')]
    public function submissionReview(
        #[MapEntity] Submission $submission,
        #[CurrentUser] User $user,
        Request $request,
    ): Response {
        $importData = $this->importService->getImportDataFor($submission);

        $newTopic = new Post($user, $submission);
        $newTopicForm = $this->getPostForm($newTopic);
        $newTopicForm->handleRequest($request);

        if ($newTopicForm->isSubmitted() && $newTopicForm->isValid()) {
            $this->entityManager->persist($newTopic);
            $this->entityManager->flush();

            return $this->redirectToReviewPost($newTopic);
        }

        $topics = [];

        foreach ($this->postRepository->getSubmissionTopics($submission) as $topic) {
            $response = new Post($user, $submission, $topic);
            $responseForm = $this->getPostForm($response);
            $responseForm->handleRequest($request);

            if ($responseForm->isSubmitted() && $responseForm->isValid()) {
                $this->entityManager->persist($response);
                $this->entityManager->flush();

                return $this->redirectToReviewPost($response);
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
    #[Route(path: '/submission/{id}/vote-post/{postId}/{positive}', name: 'rt_vote_post')]
    public function votePost(#[MapEntity] Submission $submission, #[MapEntity(id: 'postId')] Post $post,
        #[CurrentUser] User $user, bool $positive): Response
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

        return $this->redirectToReviewPost($post);
    }

    #[IsGranted('edit', 'post')] // TODO: TEST?
    #[Route(path: '/edit-post/{id}', name: 'rt_edit_post')]
    public function editPost(Request $request, #[MapEntity] Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setEditedUtc(UtcClock::now());
            $this->entityManager->flush();

            return $this->redirectToReviewPost($post);
        }

        return $this->render('submissions/post_edit.html.twig', [
            'post' => $post,
            'post_form' => $form,
        ]);
    }

    private function redirectToReviewPost(Post $post): RedirectResponse
    {
        return $this->redirectToRoute('rt_submission_review', [
            'id' => $post->getSubmission()->getId(),
            '_fragment' => 'post-'.$post->getId(), // grep-code-post-id-anchor
        ]);
    }

    /**
     * @return FormInterface<Post>
     */
    private function getPostForm(Post $post): FormInterface
    {
        if (null === $post->getParent()) {
            $formName = 'new_topic';
        } else {
            $formName = "topic_{$post->getParent()->getId()}";
        }

        return $this->formFactory->createNamed($formName, PostType::class, $post);
    }
}
