<?php

declare(strict_types=1);

namespace App\Controller\IuForm;

use App\Form\InclusionUpdate\BaseForm;
use App\Form\InclusionUpdate\Data;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\ValueObject\Routing\RouteName;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class IuFormDataController extends AbstractIuFormController
{
    /**
     * @throws NotFoundHttpException
     */
    #[Route(path: '/iu_form/data/{makerId}', name: RouteName::IU_FORM_DATA)]
    #[Cache(maxage: 0, public: false)]
    public function iuFormData(Request $request, ?string $makerId = null): Response
    {
        $state = $this->prepareState($makerId, $request);

        $form = $this->handleForm($request, $state, Data::class, [
            Data::OPT_PHOTOS_COPYRIGHT_OK => !$state->isNew() && '' !== $state->artisan->getPhotoUrls(),
            Data::OPT_ROUTER              => $this->router,
        ]);
        $this->validatePhotosCopyright($form, $state->artisan);

        if (self::clicked($form, BaseForm::BTN_RESET)) {
            $state->reset();
        }

        if (null !== ($redirection = $this->redirectToUnfinishedStep(RouteName::IU_FORM_DATA, $state))) {
            return $redirection;
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $state->save();

            $state->markDataDone();

            return $this->redirectToStep(RouteName::IU_FORM_CONTACT_AND_PASSWORD, $state);
        }

        return $this->renderForm('iu_form/data.html.twig', [
            'form'               => $form,
            'errors'             => $form->getErrors(true),
            'noindex'            => true,
            'submitted'          => $form->isSubmitted(),
            'is_update'          => !$state->isNew(),
            'session_start_time' => $state->getStarted(),
            'big_error_message'  => $this->getRestoreFailedMessage($state),
        ]);
    }

    private function validatePhotosCopyright(FormInterface $form, Artisan $artisan): void
    {
        $field = $form->get(Data::FLD_PHOTOS_COPYRIGHT);

        if ('' !== $artisan->getPhotoUrls() && 'OK' !== ($field->getData()[0] ?? null)) {
            $field->addError(new FormError('You must not use any photos without permission from the photographer.'));
        }
    }
}
