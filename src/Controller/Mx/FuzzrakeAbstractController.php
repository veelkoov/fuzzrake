<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Cache;

#[Cache(noStore: true)]
abstract class FuzzrakeAbstractController extends AbstractController
{
    public function __construct(
        protected readonly CreatorRepository $creatorRepository,
    ) {
    }

    protected function getCreatorOrThrow404(string $creatorId): Creator
    {
        try {
            return Creator::wrap($this->creatorRepository->findByCreatorId($creatorId));
        } catch (NoResultException) {
            throw $this->createNotFoundException("Creator with creator ID '$creatorId' does not exist");
        }
    }
}
