<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Controller\Utils\ButtonClickedTrait;
use App\Entity\Label;
use App\Form\Mx\LabelType;
use App\Repository\CreatorRepository;
use App\Repository\LabelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/mx/creator-labels')]
class CreatorLabelsController extends FuzzrakeAbstractController
{
    use ButtonClickedTrait;

    public function __construct(
        private readonly LabelRepository $labelRepository,
        private readonly EntityManagerInterface $entityManager,
        CreatorRepository $creatorRepository,
    ) {
        parent::__construct($creatorRepository);
    }

    /** @throws ORMException */
    #[Route(path: '/{creatorId}', name: 'rt_mx_creator_labels')]
    public function index(Request $request, string $creatorId): Response
    {
        $creator = $this->getCreatorOrThrow404($creatorId);

        $newLabel = new Label($creator->entity);
        $newLabelForm = $this->createForm(LabelType::class, $newLabel, [LabelType::OPT_DELETABLE => false]);

        if ($newLabelForm->handleRequest($request)->isSubmitted() && $newLabelForm->isValid()) {
            $this->entityManager->persist($newLabel);
            $this->entityManager->flush();

            return $this->redirectToRoute('rt_mx_creator_labels', ['creatorId' => $creatorId]);
        }

        return $this->render('mx/creator_labels/index.html.twig', [
            'creator' => $creator,
            'labels' => $this->labelRepository->findBy(['creator' => $creator->getId()]),
            'new_label_form' => $newLabelForm,
        ]);
    }

    /** @throws ORMException */
    #[Route(path: '/{creatorId}/{labelId}', name: 'rt_mx_creator_label_edit')]
    public function edit(Request $request, string $creatorId, int $labelId): Response
    {
        $creator = $this->getCreatorOrThrow404($creatorId);

        $label = $this->labelRepository->find($labelId);
        if (null === $label || !$creator->is($label->creator)) {
            throw $this->createNotFoundException("Label $labelId not found for creator $creatorId.");
        }

        $form = $this->createForm(LabelType::class, $label, [LabelType::OPT_DELETABLE => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            if (self::clicked($form, LabelType::BTN_DELETE)) {
                $this->entityManager->remove($label);
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('rt_mx_creator_labels', ['creatorId' => $creatorId]);
        }

        return $this->render('mx/creator_labels/edit.html.twig', [
            'creator' => $creator,
            'label' => $label,
            'form' => $form,
        ]);
    }
}
