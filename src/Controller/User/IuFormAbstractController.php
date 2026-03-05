<?php

declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\Traits\CreatorByCreatorIdTrait;
use App\Repository\CreatorRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class IuFormAbstractController extends AbstractController // TODO: Eliminate
{
    use CreatorByCreatorIdTrait;

    protected const string NEW_CREATOR_ID_PLACEHOLDER = '(new)';

    public function __construct(
        protected readonly CreatorRepository $creatorRepository,
        protected readonly LoggerInterface $logger,
    ) {
    }
}
