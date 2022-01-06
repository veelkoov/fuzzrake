<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Controller\Traits\ButtonClickedTrait;
use App\Entity\Artisan as ArtisanE;
use App\Form\ArtisanType;
use App\Service\EnvironmentsService;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StrUtils;
use App\ValueObject\Routing\RouteName;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mx/artisans')]
class ArtisansController extends AbstractController
{
    use ButtonClickedTrait;

    #[Route(path: '/{id}/edit', name: RouteName::MX_ARTISAN_EDIT, methods: ['GET', 'POST'])]
    #[Route(path: '/new', name: RouteName::MX_ARTISAN_NEW, methods: ['GET', 'POST'])]
    #[Cache(maxage: 0, public: false)]
    public function edit(Request $request, ?ArtisanE $entity, EnvironmentsService $environments, EntityManagerInterface $manager): Response
    {
        if (!$environments->isDevOrTest()) {
            throw $this->createAccessDeniedException();
        }

        $artisan = new Artisan($entity ??= new ArtisanE());

        $form = $this->createForm(ArtisanType::class, $artisan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null !== $artisan->getId() && self::clicked($form, ArtisanType::BTN_DELETE)) {
                $manager->remove($entity);
            } else {
                $artisan->updateContact($artisan->getContactInfoOriginal());
                StrUtils::fixNewlines($artisan);

                $manager->persist($entity);
            }

            $manager->flush();

            return $this->redirectToRoute(RouteName::MAIN);
        }

        return $this->renderForm('mx/artisans/edit.html.twig', [
            'artisan' => $artisan,
            'form'    => $form,
        ]);
    }
}
