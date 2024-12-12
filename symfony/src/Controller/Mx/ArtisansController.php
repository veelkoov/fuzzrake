<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Controller\Traits\ButtonClickedTrait;
use App\Form\Mx\AbstractTypeWithDelete;
use App\Form\Mx\ArtisanType;
use App\Repository\ArtisanRepository;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\ValueObject\Routing\RouteName;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/mx/artisans')]
class ArtisansController extends FuzzrakeAbstractController
{
    use ButtonClickedTrait;

    public function __construct(
        ArtisanRepository $creatorRepository,
    ) {
        parent::__construct($creatorRepository);
    }

    #[Route(path: '/{makerId}/edit', name: RouteName::MX_ARTISAN_EDIT, methods: ['GET', 'POST'])]
    #[Route(path: '/new', name: RouteName::MX_ARTISAN_NEW, methods: ['GET', 'POST'])]
    #[Cache(maxage: 0, public: false)]
    public function edit(Request $request, ?string $makerId): Response
    {
        $artisan = $this->getCreatorByCreatorIdOrNew($makerId);

        $prevObfuscated = $artisan->getEmailAddressObfuscated();
        $prevOriginal = $artisan->getEmailAddress();

        $form = $this->createForm(ArtisanType::class, $artisan, [
            AbstractTypeWithDelete::OPT_DELETABLE => null !== $artisan->getId(),
        ]);
        $form->handleRequest($request);

        $artisan->assureNsfwSafety();

        if ($form->isSubmitted() && $this->success($artisan, $form, $prevObfuscated, $prevOriginal)) {
            return $this->redirectToRoute(RouteName::MAIN, ['_fragment' => $artisan->getLastMakerId()]);
        }

        return $this->render('mx/artisans/edit.html.twig', [
            'artisan' => $artisan,
            'form'    => $form,
        ]);
    }

    private function success(Artisan $artisan, FormInterface $form, string $prevObfuscated, string $prevOriginal): bool
    {
        if (null !== $artisan->getId() && self::clicked($form, ArtisanType::BTN_DELETE)) {
            $this->creatorRepository->remove($artisan, true);

            return true;
        }

        if ($form->isValid()) {
            $this->updateContactUnlessObfuscatedGotCustomized($artisan, $prevObfuscated, $prevOriginal);

            $this->creatorRepository->add($artisan, true);

            return true;
        }

        return false;
    }

    private function updateContactUnlessObfuscatedGotCustomized(Artisan $artisan, string $prevObfuscated, string $prevOriginal): void
    {
        if ($artisan->getEmailAddressObfuscated() === $prevObfuscated && $artisan->getEmailAddress() !== $prevOriginal) {
            $artisan->updateEmailAddress($artisan->getEmailAddress());
        }
    }

    private function getCreatorByCreatorIdOrNew(?string $creatorId): Artisan
    {
        return null === $creatorId ? new Artisan() : $this->getCreatorOrThrow404($creatorId);
    }
}
