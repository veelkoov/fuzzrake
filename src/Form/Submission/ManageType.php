<?php

declare(strict_types=1);

namespace App\Form\Submission;

use App\Data\Submission\Status;
use App\Entity\Submission;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ManageType extends AbstractType
{
    public const string BTN_IMPORT = 'import';
    public const string BTN_SAVE = 'save';
    public const string BTN_SAVE_AND_CLOSE = 'save_and_close';
    public const string FLD_DIRECTIVES = 'directives';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(self::FLD_DIRECTIVES, TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('comment', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('status', EnumType::class, [
                'class' => Status::class,
                'choice_label' => 'label',
            ])
            ->add(self::BTN_SAVE, SubmitType::class, [
                'label' => 'Update',
                'attr' => [
                    'class' => 'btn-secondary',
                ],
            ])
            ->add(self::BTN_SAVE_AND_CLOSE, SubmitType::class, [
                'label' => '&close',
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

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Submission::class,
        ]);
    }
}
