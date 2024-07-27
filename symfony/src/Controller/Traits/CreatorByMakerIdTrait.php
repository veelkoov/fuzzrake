<?php

namespace App\Controller\Traits;

use App\Utils\Artisan\SmartAccessDecorator as Creator;
use Doctrine\ORM\UnexpectedResultException;

trait CreatorByMakerIdTrait
{
    private function getCreatorByMakerIdOrThrow404(string $makerId): Creator
    {
        try {
            return Creator::wrap($this->creatorRepository->findByMakerId($makerId));
        } catch (UnexpectedResultException) {
            throw $this->createNotFoundException('Failed to find a creator with the given maker ID');
        }
    }
}
