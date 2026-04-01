<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Utils\ButtonClickedTrait;
use App\Data\Definitions\Fields\Fields;
use App\Data\Submission\Filter;
use App\Data\Submission\Status;
use App\Entity\Submission;
use App\Form\Mx\SubmissionFilterType;
use App\Form\Mx\SubmissionType;
use App\IuHandling\Import\ImportData;
use App\IuHandling\Import\ImportService;
use App\Repository\CreatorRepository;
use App\Repository\SubmissionRepository;
use App\Security\Role;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Veelkoov\Debris\Sets\StringSet;

#[IsGranted(Role::REVIEWER->value)]
#[Cache(maxage: 0, public: false, noStore: true)]
class SubmissionsController extends AbstractController
{
    use ButtonClickedTrait;

    private const string SESSION_SUBMISSIONS_FILTER = 'submissions_filter_settings';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SubmissionRepository $submissionRepository,
        private readonly ImportService $importService,
        private readonly CreatorRepository $creatorRepository,
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

            $filterForm = $this->createForm(SubmissionFilterType::class, $filter);
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

    /**
     * @throws DateTimeException
     */
    #[IsGranted(Role::ADMIN->value)]
    #[Route(path: '/submissions/social', name: RouteName::SUBMISSIONS_SOCIAL)]
    public function social(): Response
    {
        $fourHoursAgo = UtcClock::at('-4 hours')->getTimestamp();

        $creators = array_filter(Creator::wrapAll($this->creatorRepository->getNewWithLimit()),
            static fn (Creator $creator) => ($creator->getDateAdded()?->getTimestamp() ?? 0) > $fourHoursAgo);

        return $this->render('mx/submissions/social.html.twig', [
            'creators' => $creators,
        ]);
    }

    #[IsGranted(Role::ADMIN->value)]
    #[Route(path: '/submission/{id}/manage', name: RouteName::SUBMISSION_MANAGE)]
    public function submissionManage(#[MapEntity(mapping: ['id' => 'id'])] Submission $submission, Request $request): Response
    {
        $form = $this->createForm(SubmissionType::class, $submission)->handleRequest($request);

        $importData = $this->importService->getImportDataFor($submission);

        foreach ($importData->errors as $error) {
            $form->get(SubmissionType::FLD_DIRECTIVES)->addError(new FormError($error));
        }

        if ($form->isSubmitted()) {
            if ($this->clicked($form, SubmissionType::BTN_IMPORT) && $form->isValid()) {
                if ($importData->isAccepted) {
                    $submission->setStatus(Status::IMPORTED);
                    $this->importService->import($importData);

                    return $this->redirectToRoute(RouteName::SUBMISSIONS_LIST);
                } else {
                    $form->get(SubmissionType::FLD_DIRECTIVES)->addError(
                        new FormError('Submission has not been accepted yet.'));
                }
            }

            $this->entityManager->flush(); // Save the directives

            if ($this->clicked($form, SubmissionType::BTN_SAVE_AND_CLOSE)) {
                return $this->redirectToRoute(RouteName::SUBMISSIONS_LIST);
            }
        }

        $similarlyNamedCreators = $this->getSimilarlyNamedCreators($importData)->getValuesArray();

        return $this->render('submissions/manage.html.twig', [
            'importData' => $importData,
            'similarlyNamedCreators' => $similarlyNamedCreators,
            'fields' => Fields::iuFormAffected(),
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/submission/{id}/review', name: RouteName::SUBMISSION_REVIEW)]
    public function submissionReview(#[MapEntity(mapping: ['id' => 'id'])] Submission $submission, Request $request): Response
    {
        $importData = $this->importService->getImportDataFor($submission);

        return $this->render('submissions/review.html.twig', [
            'importData' => $importData,
            'submission' => $submission,
            'fields' => Fields::inIuForm(),
        ]);
    }

    private function getSimilarlyNamedCreators(ImportData $update): CreatorList
    {
        return CreatorList::wrap($this->creatorRepository->findNamedSimilarly(
            new StringSet($update->inputData->getAllNames())
                ->plusAll($update->fixedData->getAllNames())
                ->minus('')
        ))->filterNot(static fn (Creator $creator) => $creator->entity === $update->subjectCreator->entity);
    }
}
