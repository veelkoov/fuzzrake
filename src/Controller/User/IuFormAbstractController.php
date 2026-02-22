<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\Traits\CreatorByCreatorIdTrait;
use App\Controller\User\IuFormUtils\IuSubject;
use App\Data\Definitions\Fields\SecureValues;
use App\Repository\CreatorRepository;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class IuFormAbstractController extends AbstractController
{
    use CreatorByCreatorIdTrait;

    protected const string NEW_CREATOR_ID_PLACEHOLDER = '(new)';

    public function __construct(
        protected readonly CreatorRepository $creatorRepository,
        protected readonly LoggerInterface $logger,
    ) {
    }

    protected function getSubject(?string $creatorId): IuSubject
    {
        $creator = null === $creatorId ? new Creator() : $this->getCreatorByCreatorIdOrThrow404($creatorId);

        $subject = new IuSubject($creatorId, clone $creator);
        SecureValues::forIuForm($subject->creator);

        return $subject;
    }
}
