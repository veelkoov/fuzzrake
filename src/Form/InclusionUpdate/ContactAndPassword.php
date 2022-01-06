<?php

declare(strict_types=1);

namespace App\Form\InclusionUpdate;

use App\DataDefinitions\ContactPermit;
use App\DataDefinitions\Fields\Validation;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactAndPassword extends BaseForm
{
    final public const FLD_CHANGE_PASSWORD = 'changePassword';
    final public const FLD_PASSWORD = 'password';
    final public const BTN_BACK = 'back';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

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
            ->add(self::FLD_PASSWORD, PasswordType::class, [
                'label'      => 'Updates password',
                'help'       => '8 or more characters. <span class="badge bg-warning text-dark">PRIVATE</span> Your password will be kept in a secure way and never shared.', // grep-password-length
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
            ->add(self::BTN_BACK, SubmitType::class, [
                'label' => 'Back',
                'attr'  => [
                    'class'          => 'btn btn-outline btn-outline-secondary',
                    'formnovalidate' => 'formnovalidate',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'validation_groups' => ['Default', Validation::GRP_CONTACT_AND_PASSWORD],
            'error_mapping'     => [
                'privateData.password' => 'password',
            ],
        ]);
    }
}
