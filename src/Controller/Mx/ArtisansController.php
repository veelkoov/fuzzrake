<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Controller\Traits\ButtonClickedTrait;
use App\Entity\Artisan as ArtisanE;
use App\Form\Mx\AbstractTypeWithDelete;
use App\Form\Mx\ArtisanType;
use App\Repository\ArtisanRepository;
use App\Service\EnvironmentsService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mx/artisans')]
class ArtisansController extends FuzzrakeAbstractController
{
    use ButtonClickedTrait;

    public function __construct(
        private readonly ArtisanRepository $repository,
        EnvironmentsService $environments,
    ) {
        parent::__construct($environments);
    }

    #[Route(path: '/{makerId}/edit', name: RouteName::MX_ARTISAN_EDIT, methods: ['GET', 'POST'])]
    #[Route(path: '/new', name: RouteName::MX_ARTISAN_NEW, methods: ['GET', 'POST'])]
    #[Cache(maxage: 0, public: false)]
    public function edit(Request $request, ?string $makerId): Response
    {
        $this->authorize();

        $artisan = new Artisan($this->getEntityByMakerId($makerId));

        $prevObfuscated = $artisan->getContactInfoObfuscated();
        $prevOriginal = $artisan->getContactInfoOriginal();

        $form = $this->createForm(ArtisanType::class, $artisan, [
            AbstractTypeWithDelete::OPT_DELETABLE => null !== $artisan->getId(),
        ]);
        $form->handleRequest($request);

        $artisan->assureNsfwSafety();

        if ($form->isSubmitted() && $this->success($artisan, $form, $prevObfuscated, $prevOriginal)) {
            return $this->redirectToRoute(RouteName::MAIN);
        }

        return $this->render('mx/artisans/edit.html.twig', [
            'artisan' => $artisan,
            'form'    => $form,
        ]);
    }

    private function success(Artisan $artisan, FormInterface $form, string $prevObfuscated, string $prevOriginal): bool
    {
        if (null !== $artisan->getId() && self::clicked($form, ArtisanType::BTN_DELETE)) {
            $this->repository->remove($artisan, true);

            return true;
        }

        if ($form->isValid()) {
            $this->updateContactUnlessObfuscatedGotCustomized($artisan, $prevObfuscated, $prevOriginal);

            $this->repository->add($artisan, true);

            return true;
        }

        return false;
    }

    private function updateContactUnlessObfuscatedGotCustomized(Artisan $artisan, string $prevObfuscated, string $prevOriginal): void
    {
        if ($artisan->getContactInfoObfuscated() === $prevObfuscated && $artisan->getContactInfoOriginal() !== $prevOriginal) {
            $artisan->updateContact($artisan->getContactInfoOriginal());
        }
    }

    private function getEntityByMakerId(?string $makerId): ?ArtisanE
    {
        if (null === $makerId) {
            return null;
        }

        try {
            return $this->repository->findByMakerId($makerId);
        } catch (NoResultException) {
            throw $this->createNotFoundException("Artisan with maker ID '$makerId' does not exist");
        }
    }
}
