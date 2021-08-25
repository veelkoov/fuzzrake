<?php

declare(strict_types=1);

namespace App\Controller\Mx;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\SubmitButton;
use UnexpectedValueException;

class AbstractFormController extends AbstractController
{
    protected static function clicked(FormInterface $form, string $buttonId): bool
    {
        $button = $form->get($buttonId);

        if (!($button instanceof SubmitButton)) {
            throw new UnexpectedValueException("{$buttonId} is not a button, I feel betrayed :(");
        }

        return $button->isClicked();
    }
}
