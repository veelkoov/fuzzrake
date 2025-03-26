<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\Controller\IuForm\Utils\IuSubject;
use App\Controller\Traits\ButtonClickedTrait;
use App\Controller\Traits\CreatorByCreatorIdTrait;
use App\Data\Definitions\Fields\SecureValues;
use App\Form\InclusionUpdate\BaseForm;
use App\Repository\ArtisanRepository as CreatorRepository;
use App\Utils\Artisan\SmartAccessDecorator as Creator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class AbstractIuFormController extends AbstractController
{
    use ButtonClickedTrait;
    use CreatorByCreatorIdTrait;

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

    /**
     * @param class-string<BaseForm> $type
     * @param array<string, mixed>   $options
     */
    protected function handleForm(Request $request, IuSubject $state, string $type, array $options): FormInterface // @phpstan-ignore missingType.generics
    {
        return $this
            ->createForm($type, $state->creator, $options)
            ->handleRequest($request);
    }
}
