<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Controller\Traits\ButtonClickedTrait;
use App\Data\Definitions\Fields\Fields;
use App\Form\Mx\SubmissionType;
use App\IuHandling\Exception\MissingSubmissionException;
use App\IuHandling\Import\SubmissionsService;
use App\IuHandling\Import\UpdatesService;
use App\Repository\ArtisanRepository;
use App\Service\Cache as CacheService;
use App\Service\EnvironmentsService;
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

#[Route(path: '/mx/submissions')]
class SubmissionsController extends FuzzrakeAbstractController
{
    use ButtonClickedTrait;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SubmissionsService $submissions,
        private readonly UpdatesService $updates,
        private readonly CacheService $cache,
        EnvironmentsService $environments,
    ) {
        parent::__construct($environments);
    }

    #[Route(path: '/', name: RouteName::MX_SUBMISSIONS)]
    #[Cache(maxage: 0, public: false)]
    public function submissions(): Response
    {
        $this->authorize();

        $submissions = $this->submissions->getSubmissions();

        return $this->render('mx/submissions/index.html.twig', [
            'submissions' => $submissions,
        ]);
    }

    /**
     * @throws DateTimeException
     */
    #[Route(path: '/social', name: RouteName::MX_SUBMISSIONS_SOCIAL)]
    #[Cache(maxage: 0, public: false)]
    public function social(ArtisanRepository $repository): Response
    {
        $this->authorize();

        $fourHoursAgo = UtcClock::at('-4 hours')->getTimestamp();

        $artisans = array_filter(Artisan::wrapAll($repository->getNewWithLimit()),
            fn (Artisan $artisan) => ($artisan->getDateAdded()?->getTimestamp() ?? 0) > $fourHoursAgo);

        return $this->render('mx/submissions/social.html.twig', [
            'artisans' => $artisans,
        ]);
    }

    #[Route(path: '/{id}', name: RouteName::MX_SUBMISSION)]
    #[Cache(maxage: 0, public: false)]
    public function submission(Request $request, string $id): Response
    {
        $this->authorize();

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
