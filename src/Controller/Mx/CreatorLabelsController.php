<?php

declare(strict_types=1);

namespace App\Controller\Mx;

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
    public function __construct(
        private readonly LabelRepository $labelRepository,
        private readonly EntityManagerInterface $entityManager,
        CreatorRepository $creatorRepository,
    ) {
        parent::__construct($creatorRepository);
    }

    /**
     * @throws ORMException
     */
    #[Route(path: '/{creatorId}', name: 'rt_mx_creator_labels')]
    public function index(Request $request, string $creatorId): Response
    {
        $creator = $this->getCreatorOrThrow404($creatorId);

        $newLabel = new Label($creator->entity);
        $newLabelForm = $this->createForm(LabelType::class, $newLabel);

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
}
