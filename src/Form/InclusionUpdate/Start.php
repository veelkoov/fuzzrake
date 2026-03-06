<?php

declare(strict_types=1);

namespace App\Form\InclusionUpdate;

use App\Controller\IuForm\IuFormUtils\StartData;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<StartData>
 */
final class Start extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('confirmNoPendingUpdates', ChoiceType::class, [
                'label'      => 'Have you submitted the form previously?',
                'choices'    => [
                    "No, I haven't send anything previously"                 => 'no-prior-submissions',
                    'Yes, and I can see all the changes I requested on-line' => 'all-submissions-on-line',
                    "Yes, but I don't see my requested changes"              => 'submission-pending',
                ],
                'expanded'   => true,
                'required'   => true,
            ])
            ->add('decisionOverPreviousUpdates', ChoiceType::class, [
                'label'      => 'How do you want your previous submission to be handled?',
                'choices'    => [
                    'Cancel my previous submission, I will re-apply all the changes I want now'  => 'can-be-cancelled',
                    'I want the previous submission to be processed before submitting a new one' => 'can-not-be-cancelled',
                ],
                'expanded'   => true,
                'required'   => true,
            ])
        ;
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'iu_form';
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', StartData::class);
    }
}
