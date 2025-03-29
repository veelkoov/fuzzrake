<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\Controller\IuForm\Utils\IuSubject;
use App\Controller\Traits\CreatorByCreatorIdTrait;
use App\Data\Definitions\Fields\SecureValues;
use App\Repository\ArtisanRepository as CreatorRepository;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class AbstractIuFormController extends AbstractController
{
    use CreatorByCreatorIdTrait;

    protected const string NEW_CREATOR_ID_PLACEHOLDER = '(new)';

    public function __construct(
        protected readonly CreatorRepository $creatorRepository,
    ) {
    }

    protected function markCaptchaDone(SessionInterface $session): void
    {
        $session->set('iu_form_captcha_done', true);
    }

    protected function isCaptchaDone(SessionInterface $session): bool
    {
        $result = $session->get('iu_form_captcha_done', false);

        return is_bool($result) ? $result : false;
    }

    protected function getSubject(?string $makerId): IuSubject
    {
        $creator = null === $makerId ? new Creator() : $this->getCreatorByCreatorIdOrThrow404($makerId);

        $state = new IuSubject($makerId, $creator);
        SecureValues::forIuForm($state->creator);

        return $state;
    }
}
