<?php

declare(strict_types=1);

namespace App\Form\Mx;

use App\Utils\Mx\CreatorUrlsRemovalData;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<CreatorUrlsRemovalData>
 */
class CreatorUrlsRemovalType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hide', CheckboxType::class, [
                'label'    => 'Hide the creator',
                'required' => false,
            ])
            ->add('sendEmail', CheckboxType::class, [
                'label'    => 'Send email',
                'required' => false,
                'disabled' => true !== $options['is_contact_allowed'],
            ])
            ->add('confirm', SubmitType::class, [
                'label' => 'Confirm',
                'attr'  => [
                    'class'   => 'btn btn-danger',
                    'onclick' => 'return confirm("Are you sure?")',
                ],
            ]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('data_class', CreatorUrlsRemovalData::class)
            ->setRequired('is_contact_allowed')
            ->setAllowedTypes('is_contact_allowed', 'bool')
        ;
    }
}
