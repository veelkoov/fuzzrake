<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

trait ButtonClickedTrait
{
    private function clicked(FormInterface $form, string $buttonId): bool
    {
        if (!($form instanceof Form)) {
            return false;
        }

        $button = $form->getClickedButton();

        if (null === $button || 0 !== $form->getErrors(false)->count()) {
            return false;
        }

        return $buttonId === $button->getName();
    }
}
