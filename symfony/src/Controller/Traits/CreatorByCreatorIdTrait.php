<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use App\Utils\Artisan\SmartAccessDecorator as Creator;
use Doctrine\ORM\UnexpectedResultException;

trait CreatorByCreatorIdTrait
{
    private function getCreatorByCreatorIdOrThrow404(string $creatorId): Creator
    {
        try {
            return Creator::wrap($this->creatorRepository->findByMakerId($creatorId));
        } catch (UnexpectedResultException) {
            throw $this->createNotFoundException('Failed to find a creator with the given creator ID');
        }
    }
}
