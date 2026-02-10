<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Controller\Traits\ButtonClickedTrait;
use App\Form\Mx\AbstractTypeWithDelete;
use App\Form\Mx\CreatorType;
use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\ValueObject\Routing\RouteName;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/mx/creators')]
class CreatorsController extends FuzzrakeAbstractController
{
    use ButtonClickedTrait;

    public function __construct(
        CreatorRepository $creatorRepository,
    ) {
        parent::__construct($creatorRepository);
    }

    #[Route(path: '/{creatorId}/edit', name: RouteName::MX_CREATOR_EDIT, methods: ['GET', 'POST'])]
    #[Route(path: '/new', name: RouteName::MX_CREATOR_NEW, methods: ['GET', 'POST'])]
    public function edit(Request $request, ?string $creatorId): Response
    {
        $creator = $this->getCreatorByCreatorIdOrNew($creatorId);

        $form = $this->createForm(CreatorType::class, $creator, [
            AbstractTypeWithDelete::OPT_DELETABLE => null !== $creator->getId(),
        ]);
        $form->handleRequest($request);

        $creator->assureNsfwSafety();

        if ($form->isSubmitted() && $this->success($creator, $form)) {
            return $this->redirectToRoute(RouteName::MAIN, ['_fragment' => $creator->getLastCreatorId()]);
        }

        return $this->render('mx/creators/edit.html.twig', [
            'creator' => $creator,
            'form'    => $form,
        ]);
    }

    private function success(Creator $creator, FormInterface $form): bool
    {
        if (null !== $creator->getId() && self::clicked($form, CreatorType::BTN_DELETE)) {
            $this->creatorRepository->remove($creator, true);

            return true;
        }

        if ($form->isValid()) {
            $this->creatorRepository->add($creator, true);

            return true;
        }

        return false;
    }

    private function getCreatorByCreatorIdOrNew(?string $creatorId): Creator
    {
        return null === $creatorId ? new Creator() : $this->getCreatorOrThrow404($creatorId);
    }
}
