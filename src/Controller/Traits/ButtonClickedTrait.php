<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

trait ButtonClickedTrait
{
    private function clicked(FormInterface $form, string $buttonId): bool
    {
        if (!($form instanceof Form)) {
            return false;
        }

        $button = $form->getClickedButton();

        if (null === $button || $button->getName() !== $buttonId) {
            return false;
        }

        foreach ($form->getErrors(true) as $error) {
            if ($error->getCause()  instanceof CsrfToken) {
                return false;
            }
        }

        return true;
    }
}
