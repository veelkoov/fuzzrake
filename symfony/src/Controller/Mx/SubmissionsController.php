<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Controller\Traits\ButtonClickedTrait;
use App\Data\Definitions\Fields\Fields;
use App\Entity\Submission;
use App\Form\Mx\SubmissionType;
use App\IuHandling\Import\UpdatesService;
use App\Repository\CreatorRepository;
use App\Repository\SubmissionRepository;
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

#[Route(path: '/mx')]
class SubmissionsController extends AbstractController
{
    use ButtonClickedTrait;

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
    #[Cache(maxage: 0, public: false)]
    public function submissions(int $page): Response
    {
        $submissionsPage = $this->submissionRepository->getPage($page);

        return $this->render('mx/submissions/index.html.twig', [
            'submissions_page' => $submissionsPage,
        ]);
    }

    /**
     * @throws DateTimeException
     */
    #[Route(path: '/submissions/social', name: RouteName::MX_SUBMISSIONS_SOCIAL)]
    #[Cache(maxage: 0, public: false)]
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
    #[Cache(maxage: 0, public: false)]
    public function submission(#[MapEntity(mapping: ['strId' => 'strId'])] Submission $submission, Request $request): Response
    {
        $form = $this->createForm(SubmissionType::class, $submission)->handleRequest($request);

        $update = $this->updates->getUpdateFor($submission);

        if ($form->isSubmitted()) {
            $this->entityManager->flush();
        }

        if ($form->isSubmitted() && $this->clicked($form, SubmissionType::BTN_IMPORT)
                && $form->isValid() && $update->isAccepted) {
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
