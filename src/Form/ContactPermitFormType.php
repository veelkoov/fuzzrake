<?php

declare(strict_types=1);

namespace App\Form;

use App\Data\Definitions\ContactPermit;
use App\Entity\User;
use App\Form\Transformers\ContactPermitTransformer;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<User>
 */
final class ContactPermitFormType extends AbstractType
{
    public const string FLD_CONTACT_PERMIT = 'contactPermit';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(self::FLD_CONTACT_PERMIT, ChoiceType::class, [
                'label'    => 'When do you agree to receive emails?',
                'required' => true,
                'choices'  => ContactPermit::getFormChoices(false),
                'expanded' => true,
            ])
        ;

        $builder->get(self::FLD_CONTACT_PERMIT)->addModelTransformer(new ContactPermitTransformer());
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'contact_form';
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
