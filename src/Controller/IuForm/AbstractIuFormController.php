<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\Controller\IuForm\Utils\IuState;
use App\Controller\Traits\ButtonClickedTrait;
use App\Data\Definitions\Fields\SecureValues;
use App\Entity\Artisan as ArtisanE;
use App\IuHandling\Submission\SubmissionService;
use App\Repository\ArtisanRepository;
use App\Service\Captcha;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\UnexpectedResultException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractIuFormController extends AbstractController
{
    use ButtonClickedTrait;

    public function __construct(
        protected readonly Captcha $captcha,
        protected readonly LoggerInterface $logger,
        protected readonly SubmissionService $iuFormService,
        protected readonly RouterInterface $router,
        protected readonly ArtisanRepository $artisanRepository,
    ) {
    }

    private function getArtisanByMakerIdOrThrow404(?string $makerId): Artisan
    {
        try {
            return Artisan::wrap($makerId ? $this->artisanRepository->findByMakerId($makerId) : new ArtisanE());
        } catch (UnexpectedResultException) {
            throw $this->createNotFoundException('Failed to find a maker with given ID');
        }
    }

    protected function prepareState(?string $makerId, Request $request): IuState
    {
        $state = new IuState($this->logger, $request->getSession(), $makerId, $this->getArtisanByMakerIdOrThrow404($makerId));
        SecureValues::forIuForm($state->artisan);

        return $state;
    }

    protected function getRestoreFailedMessage(IuState $state): string
    {
        return $state->hasRestoreErrors() ? 'There were some issues while handling the information you entered. It is possible that once submitted, some of it may be lost. Try to finish sending the form, but even if you succeed, please note the time of seeing this message and contact the website maintainer. I am terribly sorry for the inconvenience!' : '';
    }

    protected function redirectToUnfinishedStep(string $currentRoute, IuState $state): ?RedirectResponse
    {
        if (!$state->captchaDone() && RouteName::IU_FORM_START !== $currentRoute) {
            return $this->redirectToStep(RouteName::IU_FORM_START, $state);
        }

        if (!$state->dataDone() && !in_array($currentRoute, [RouteName::IU_FORM_START, RouteName::IU_FORM_DATA])) {
            return $this->redirectToStep(RouteName::IU_FORM_DATA, $state);
        }

        return null;
    }

    protected function redirectToStep(string $route, IuState $state): RedirectResponse
    {
        return $this->redirectToRoute($route, ['makerId' => $state->makerId]);
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function handleForm(Request $request, IuState $state, string $type, array $options): FormInterface
    {
        return $this
            ->createForm($type, $state->artisan, $options)
            ->handleRequest($request);
    }
}
