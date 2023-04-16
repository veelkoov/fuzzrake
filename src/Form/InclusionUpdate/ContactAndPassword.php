<?php

declare(strict_types=1);

namespace App\Form\InclusionUpdate;

use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Validation;
use App\Form\Transformers\ContactPermitTransformer;
use App\ValueObject\Texts;
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

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('contactAllowed', ChoiceType::class, [
                'label'      => 'When is contact allowed?',
                'required'   => true,
                'choices'    => ContactPermit::getChoices(false),
                'expanded'   => true,
            ])
            ->add('contactInfoObfuscated', TextType::class, [
                'label'     => 'Your e-mail address',
                'help'      => 'If you are updating your data, and you see asterisks here, but the e-mail address looks OK, and you don\'t want to change it - just leave it as it is. <span class="badge bg-warning text-dark">PRIVATE</span> Your address will never be shared with anyone without your permission.',
                'help_html' => true,
                'attr'      => [
                    'placeholder' => 'E-MAIL: e-mail@address',
                ],
                'required'   => true,
                'empty_data' => '',
            ])
            ->add(self::FLD_PASSWORD, PasswordType::class, [
                'label'      => Texts::UPDATES_PASSWORD,
                'help'       => '8 or more characters. <span class="badge bg-warning text-dark">PRIVATE</span> Your password will be kept in a secure way and never shared.', // grep-password-length
                'help_html'  => true,
                'required'   => true,
                'empty_data' => '',
                'attr'       => [
                    'autocomplete' => 'section-iuform current-password',
                ],
            ])
            ->add(self::FLD_CHANGE_PASSWORD, CheckboxType::class, [
                'label'     => Texts::WANT_TO_CHANGE_PASSWORD,
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

        $builder->get('contactAllowed')->addModelTransformer(new ContactPermitTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
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
