<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Controller\Traits\ButtonClickedTrait;
use App\Entity\Artisan as ArtisanE;
use App\Form\Mx\ArtisanType;
use App\Service\EnvironmentsService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mx/artisans')]
class ArtisansController extends AbstractController
{
    use ButtonClickedTrait;

    public function __construct(
        private readonly EnvironmentsService $environments,
        private readonly EntityManagerInterface $manager,
    ) {
    }

    #[Route(path: '/{id}/edit', name: RouteName::MX_ARTISAN_EDIT, methods: ['GET', 'POST'])]
    #[Route(path: '/new', name: RouteName::MX_ARTISAN_NEW, methods: ['GET', 'POST'])]
    #[Cache(maxage: 0, public: false)]
    public function edit(Request $request, ?ArtisanE $entity): Response
    {
        $artisan = new Artisan($entity);

        if (!$this->environments->isDevOrTest()) {
            throw $this->createAccessDeniedException();
        }

        $prevObfuscated = $artisan->getContactInfoObfuscated();
        $prevOriginal = $artisan->getContactInfoOriginal();

        $form = $this->createForm(ArtisanType::class, $artisan);
        $form->handleRequest($request);

        $artisan->assureNsfwSafety();

        if ($form->isSubmitted() && $this->success($artisan, $form, $prevObfuscated, $prevOriginal)) {
            $this->manager->flush();

            return $this->redirectToRoute(RouteName::MAIN);
        }

        return $this->renderForm('mx/artisans/edit.html.twig', [
            'artisan' => $artisan,
            'form'    => $form,
        ]);
    }

    private function success(Artisan $artisan, FormInterface $form, string $prevObfuscated, string $prevOriginal): bool
    {
        if (null !== $artisan->getId() && self::clicked($form, ArtisanType::BTN_DELETE)) {
            $this->manager->remove($artisan);

            return true;
        }

        if ($form->isValid()) {
            $this->updateContactUnlessObfuscatedGotCustomized($artisan, $prevObfuscated, $prevOriginal);

            $this->manager->persist($artisan);

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
}
