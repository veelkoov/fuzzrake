<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\Controller\IuForm\Utils\IuSubject;
use App\Controller\Traits\CreatorByCreatorIdTrait;
use App\Data\Definitions\Fields\SecureValues;
use App\Repository\ArtisanRepository as CreatorRepository;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractIuFormController extends AbstractController
{
    use CreatorByCreatorIdTrait;

    protected const string NEW_CREATOR_ID_PLACEHOLDER = '(new)';

    public function __construct(
        protected readonly CreatorRepository $creatorRepository,
    ) {
    }

    protected function getSubject(?string $makerId): IuSubject
    {
        $creator = null === $makerId ? new Creator() : $this->getCreatorByCreatorIdOrThrow404($makerId);

        $subject = new IuSubject($makerId, $creator);
        SecureValues::forIuForm($subject->creator);

        return $subject;
    }
}
