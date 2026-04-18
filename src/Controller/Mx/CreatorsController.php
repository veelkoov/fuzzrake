<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Controller\Utils\ButtonClickedTrait;
use App\Form\Mx\AbstractTypeWithDelete;
use App\Form\Mx\CreatorType;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/mx/creators')]
class CreatorsController extends FuzzrakeAbstractController
{
    use ButtonClickedTrait;

    #[Route(path: '/{creatorId}/edit', name: 'rt_mx_creator_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $creatorId): Response
    {
        $creator = $this->getCreatorOrThrow404($creatorId);

        $form = $this->createForm(CreatorType::class, $creator, [
            AbstractTypeWithDelete::OPT_DELETABLE => true,
        ]);
        $form->handleRequest($request);

        $creator->assureNsfwSafety();

        if ($form->isSubmitted() && $this->success($creator, $form)) {
            return $this->redirectToRoute('rt_main', ['_fragment' => $creator->getLastCreatorId()]);
        }

        return $this->render('mx/creators/edit.html.twig', [
            'creator' => $creator,
            'form'    => $form,
        ]);
    }

    /**
     * @param FormInterface<Creator> $form
     */
    private function success(Creator $creator, FormInterface $form): bool
    {
        if (self::clicked($form, AbstractTypeWithDelete::BTN_DELETE)) {
            $this->creatorRepository->remove($creator, true);

            return true;
        }

        if ($form->isValid()) {
            $this->creatorRepository->add($creator, true);

            return true;
        }

        return false;
    }
}
