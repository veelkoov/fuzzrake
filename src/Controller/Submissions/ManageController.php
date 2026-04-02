<?php

declare(strict_types=1);

namespace App\Controller\Submissions;

use App\Controller\Utils\ButtonClickedTrait;
use App\Data\Definitions\Fields\Fields;
use App\Data\Submission\Status;
use App\Entity\Submission;
use App\Form\Submission\ManageType;
use App\IuHandling\Import\ImportData;
use App\IuHandling\Import\ImportService;
use App\Repository\CreatorRepository;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Veelkoov\Debris\Sets\StringSet;

#[IsGranted(Role::ADMIN->value)]
#[Cache(maxage: 0, public: false, noStore: true)]
class ManageController extends AbstractController
{
    use ButtonClickedTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ImportService $importService,
        private readonly CreatorRepository $creatorRepository,
    ) {
    }

    /**
     * @throws DateTimeException
     */
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

    #[Route(path: '/submission/{id}/manage', name: RouteName::SUBMISSION_MANAGE)]
    public function submissionManage(#[MapEntity] Submission $submission, Request $request): Response
    {
        $form = $this->createForm(ManageType::class, $submission)->handleRequest($request);

        $importData = $this->importService->getImportDataFor($submission);

        foreach ($importData->errors as $error) {
            $form->get(ManageType::FLD_DIRECTIVES)->addError(new FormError($error));
        }

        if ($form->isSubmitted()) {
            if ($this->clicked($form, ManageType::BTN_IMPORT) && $form->isValid()) {
                if ($importData->isAccepted) {
                    $submission->setStatus(Status::IMPORTED);
                    $this->importService->import($importData);

                    return $this->redirectToRoute(RouteName::SUBMISSIONS_LIST);
                } else {
                    $form->get(ManageType::FLD_DIRECTIVES)->addError(
                        new FormError('Submission has not been accepted yet.'));
                }
            }

            $this->entityManager->flush(); // Save the directives

            if ($this->clicked($form, ManageType::BTN_SAVE_AND_CLOSE)) {
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

    private function getSimilarlyNamedCreators(ImportData $update): CreatorList
    {
        return CreatorList::wrap($this->creatorRepository->findNamedSimilarly(
            new StringSet($update->inputData->getAllNames())
                ->plusAll($update->fixedData->getAllNames())
                ->minus('')
        ))->filterNot(static fn (Creator $creator) => $creator->entity === $update->subjectCreator->entity);
    }
}
