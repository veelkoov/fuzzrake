<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Controller\Utils\ButtonClickedTrait;
use App\Data\Definitions\Fields\Fields;
use App\Data\Submission\Filter;
use App\Data\Submission\Status;
use App\Entity\Submission;
use App\Form\Mx\SubmissionFilterType;
use App\Form\Mx\SubmissionType;
use App\IuHandling\Import\Update;
use App\IuHandling\Import\UpdatesService;
use App\Repository\CreatorRepository;
use App\Repository\SubmissionRepository;
use App\Utils\Creator\CreatorList;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Veelkoov\Debris\Sets\StringSet;

#[Cache(noStore: true)]
#[Route(path: '/mx')]
class SubmissionsController extends AbstractController
{
    use ButtonClickedTrait;

    private const string SESSION_SUBMISSIONS_FILTER = 'submissions_filter_settings';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SubmissionRepository $submissionRepository,
        private readonly UpdatesService $updates,
        private readonly CreatorRepository $creatorRepository,
    ) {
    }

    /**
     * @param positive-int $page
     */
    #[Route(path: '/submissions/{page}/', name: RouteName::MX_SUBMISSIONS, requirements: ['page' => Requirement::POSITIVE_INT], defaults: ['page' => 1])]
    public function submissions(Request $request, int $page): Response
    {
        $filter = $request->getSession()->get(self::SESSION_SUBMISSIONS_FILTER);
        if (!$filter instanceof Filter) {
            $filter = new Filter();
        }

        $filterForm = $this->createForm(SubmissionFilterType::class, $filter);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $request->getSession()->set(self::SESSION_SUBMISSIONS_FILTER, $filter);

            return $this->redirectToRoute(RouteName::MX_SUBMISSIONS, ['page' => $page]);
        }

        $submissionsPage = $this->submissionRepository->getPage($filter, $page);

        return $this->render('mx/submissions/index.html.twig', [
            'filter_form' => $filterForm,
            'submissions_page' => $submissionsPage,
        ]);
    }

    /**
     * @throws DateTimeException
     */
    #[Route(path: '/submissions/social', name: RouteName::MX_SUBMISSIONS_SOCIAL)]
    public function social(): Response
    {
        $fourHoursAgo = UtcClock::at('-4 hours')->getTimestamp();

        $creators = array_filter(Creator::wrapAll($this->creatorRepository->getNewWithLimit()),
            static fn (Creator $creator) => ($creator->getDateAdded()?->getTimestamp() ?? 0) > $fourHoursAgo);

        return $this->render('mx/submissions/social.html.twig', [
            'creators' => $creators,
        ]);
    }

    #[Route(path: '/submission/{strId}', name: RouteName::MX_SUBMISSION)]
    public function submission(#[MapEntity(mapping: ['strId' => 'strId'])] Submission $submission, Request $request): Response
    {
        $form = $this->createForm(SubmissionType::class, $submission)->handleRequest($request);

        $update = $this->updates->getUpdateFor($submission);

        foreach ($update->errors as $error) {
            $form->get(SubmissionType::FLD_DIRECTIVES)->addError(new FormError($error));
        }

        if ($form->isSubmitted()) {
            if ($this->clicked($form, SubmissionType::BTN_IMPORT) && $form->isValid()) {
                if ($update->isAccepted) {
                    $submission->setStatus(Status::IMPORTED);
                    $this->updates->import($update);

                    return $this->redirectToRoute(RouteName::MX_SUBMISSIONS);
                } else {
                    $form->get(SubmissionType::FLD_DIRECTIVES)->addError(
                        new FormError('Submission has not been accepted yet.'));
                }
            }

            $this->entityManager->flush(); // Save the directives

            if ($this->clicked($form, SubmissionType::BTN_SAVE_AND_CLOSE)) {
                return $this->redirectToRoute(RouteName::MX_SUBMISSIONS);
            }
        }

        $similarlyNamedCreators = $this->getSimilarlyNamedCreators($update)->getValuesArray();

        return $this->render('mx/submissions/submission.html.twig', [
            'update' => $update,
            'similarlyNamedCreators' => $similarlyNamedCreators,
            'fields' => Fields::iuFormAffected(),
            'form' => $form->createView(),
        ]);
    }

    private function getSimilarlyNamedCreators(Update $update): CreatorList
    {
        return CreatorList::wrap($this->creatorRepository->findNamedSimilarly(
            new StringSet($update->originalInput->getAllNames())
                ->plusAll($update->updatedCreator->getAllNames())
                ->minus('')
        ))->filterNot(static fn (Creator $creator) => $creator->entity === $update->originalCreator->entity);
    }
}
