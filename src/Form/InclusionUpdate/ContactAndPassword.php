<?php

declare(strict_types=1);

namespace App\Form\InclusionUpdate;

use App\DataDefinitions\ContactPermit;
use App\DataDefinitions\Fields\Validation;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactAndPassword extends AbstractType
{
    final public const FLD_CHANGE_PASSWORD = 'changePassword';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contactAllowed', ChoiceType::class, [
                'label'      => 'When is contact allowed?',
                'required'   => true,
                'choices'    => ContactPermit::getValueKeyMap(),
                'empty_data' => ContactPermit::NO,
                'expanded'   => true,
            ])
            ->add('contactInfoObfuscated', TextType::class, [
                'label'     => 'How can I contact you',
                'help'      => 'Please provide your e-mail address. No other possibilities, sorry! If you are updating your data and you see asterisks here, but the e-mail address looks OK, and you don\'t want to change it - just leave it as it is. <span class="badge bg-warning text-dark">PRIVATE</span> Your address will never be shared with anyone without your permission.',
                'help_html' => true,
                'attr'      => [
                    'placeholder' => 'E-MAIL: e-mail@address',
                ],
                'required'   => true,
                'empty_data' => '',
            ])
            ->add('password', PasswordType::class, [
                'label'      => 'Updates password',
                'help'       => 'Please choose some kind of password, which will be later used to make sure it was you, who\'s posting updates. Use at least 8 characters (the more, the merrier). <strong>Please do not use any password you use anywhere else.</strong> <span class="badge bg-warning text-dark">PRIVATE</span> Your password will be kept in a secure way and never shared.', // grep-password-length
                'help_html'  => true,
                'required'   => true,
                'empty_data' => '',
                'attr'       => [
                    'autocomplete' => 'section-iuform current-password',
                ],
            ])
            ->add(self::FLD_CHANGE_PASSWORD, CheckboxType::class, [
                'label'     => 'I want to change my password / I forgot my password',
                'required'  => false,
                'mapped'    => false,
            ])
            ->add('notes', TextareaType::class, [
                'label'      => 'Anything else? ("notes")',
                'help'       => '<strong>WARNING!</strong> This is information 1) will <strong>NOT</strong> be visible on getfursu.it, yet it 2) <strong>WILL</strong> however be public. Treat this as place for comments for getfursu.it maintainer or some additional information which might be added to the website in the future.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'iu_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => Artisan::class,
            'validation_groups' => ['Default', Validation::GRP_CONTACT_AND_PASSWORD],
            'error_mapping'     => [
                'privateData.password' => 'password',
            ],
        ]);
    }
}
