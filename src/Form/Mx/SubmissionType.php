<?php

declare(strict_types=1);

namespace App\Form\Mx;

use App\Entity\Submission;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubmissionType extends AbstractType
{
    final public const BTN_IMPORT = 'import';
    final public const BTN_SAVE = 'save';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('directives', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('comment', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add(self::BTN_SAVE, SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn-secondary',
                ],
            ])
            ->add(self::BTN_IMPORT, SubmitType::class, [
                'label' => 'Import',
                'attr' => [
                    'onclick' => 'return confirm("Import?");',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Submission::class,
        ]);
    }
}
