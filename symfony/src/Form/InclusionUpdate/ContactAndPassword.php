<?php

declare(strict_types=1);

namespace App\Form\InclusionUpdate;

use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Fields\Validation;
use App\Form\RouterDependentTrait;
use App\Form\Transformers\ContactPermitTransformer;
use App\ValueObject\Routing\RouteName;
use App\ValueObject\Texts;
use Override;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactAndPassword extends BaseForm
{
    use RouterDependentTrait;

    final public const string FLD_CHANGE_PASSWORD = 'changePassword';
    final public const string FLD_CONTACT_ALLOWED = 'contactAllowed';
    final public const string FLD_VERIFICATION_ACKNOWLEDGEMENT = 'verificationAcknowledgement';
    final public const string FLD_PASSWORD = 'password';
    final public const string BTN_BACK = 'back';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $router = self::getRouter($options);
        $contactPath = htmlspecialchars($router->generate(RouteName::CONTACT));

        $builder
            ->add(self::FLD_CONTACT_ALLOWED, ChoiceType::class, [
                'label'      => 'When is contact allowed?',
                'required'   => true,
                'choices'    => ContactPermit::getChoices(false),
                'expanded'   => true,
            ])
            ->add('contactInfoObfuscated', TextType::class, [
                'label'     => 'Your e-mail address',
                'help'      => 'If you are updating your data, and you see asterisks here, but the e-mail address looks OK, and you don\'t want to change it - just leave it as it is. <span class="badge bg-warning text-dark">PRIVATE</span> Your address will never be shared with anyone without your permission.',
                'help_html' => true,
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
            ->add(self::FLD_VERIFICATION_ACKNOWLEDGEMENT, CheckboxType::class, [
                'label'      => 'I acknowledge that I am required to <a href="'.$contactPath.'" target="_blank">contact the maintainer</a> to confirm the submission. I realize that not doing so will result in the submission being rejected.',
                'required'   => false,
                'mapped'     => false,
                'label_html' => true,
            ])
            ->add(self::BTN_BACK, SubmitType::class, [
                'label' => 'Back',
                'attr'  => [
                    'class'          => 'btn btn-outline btn-outline-secondary',
                    'formnovalidate' => 'formnovalidate',
                ],
            ])
        ;

        $builder->get(self::FLD_CONTACT_ALLOWED)->addModelTransformer(new ContactPermitTransformer());
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        self::configureRouterOption($resolver);

        $resolver->setDefaults([
            'validation_groups' => ['Default', Validation::GRP_CONTACT_AND_PASSWORD],
            'error_mapping'     => [
                'privateData.password' => 'password',
            ],
        ]);
    }
}
