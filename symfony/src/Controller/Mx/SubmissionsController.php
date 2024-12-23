<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Controller\Traits\ButtonClickedTrait;
use App\Data\Definitions\Fields\Fields;
use App\Form\Mx\SubmissionType;
use App\IuHandling\Exception\MissingSubmissionException;
use App\IuHandling\Import\SubmissionsService;
use App\IuHandling\Import\UpdatesService;
use App\Repository\ArtisanRepository as CreatorRepository;
use App\Service\Cache as CacheService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\DateTime\DateTimeException;
use App\Utils\DateTime\UtcClock;
use App\ValueObject\CacheTags;
use App\ValueObject\Routing\RouteName;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(path: '/mx')]
class SubmissionsController extends FuzzrakeAbstractController
{
    use ButtonClickedTrait;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SubmissionsService $submissions,
        private readonly UpdatesService $updates,
        private readonly CacheService $cache,
        CreatorRepository $creatorRepository,
    ) {
        parent::__construct($creatorRepository);
    }

    /**
     * @param positive-int $page
     */
    #[Route(path: '/submissions/{page}/', name: RouteName::MX_SUBMISSIONS, requirements: ['page' => Requirement::POSITIVE_INT], defaults: ['page' => 1])]
    #[Cache(maxage: 0, public: false)]
    public function submissions(int $page): Response
    {
        $submissionsPage = $this->submissions->getSubmissions($page);

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

        $artisans = array_filter(Artisan::wrapAll($this->creatorRepository->getNewWithLimit()),
            fn (Artisan $artisan) => ($artisan->getDateAdded()?->getTimestamp() ?? 0) > $fourHoursAgo);

        return $this->render('mx/submissions/social.html.twig', [
            'artisans' => $artisans,
        ]);
    }

    #[Route(path: '/submission/{id}', name: RouteName::MX_SUBMISSION)]
    #[Cache(maxage: 0, public: false)]
    public function submission(Request $request, string $id): Response
    {
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

        if ($form->isSubmitted() && $this->clicked($form, SubmissionType::BTN_IMPORT)
                && $form->isValid() && $update->isAccepted) {
            $this->updates->import($update);
            $this->cache->invalidate(CacheTags::ARTISANS);

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
