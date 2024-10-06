<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Transformers\NullToEmptyStringTransformer;
use App\ValueObject\Feedback;
use App\ValueObject\Routing\RouteName;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackType extends AbstractType
{
    use RouterDependentTrait;

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $router = self::getRouter($options);
        $contactPageUrl = htmlspecialchars($router->generate(RouteName::CONTACT));

        $builder
            ->add('noContactBack', CheckboxType::class, [
                'label'     => 'I acknowledge and accept the fact, that I will NOT be contacted back.',
                'help'      => "If you need a response, please contact me using any means listed on <a href=\"$contactPageUrl\">this page</a>.",
                'help_html' => true,
            ])
            ->add('maker', TextType::class, [
                'label'      => 'Maker (if applicable)',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('subject', ChoiceType::class, [
                'label'        => 'What would you like to give feedback about?',
                'choices'      => Feedback::OPTIONS,
                'choice_label' => fn ($item) => $item,
                'expanded'     => true,
            ])
            ->add('details', TextareaType::class, [
                'label'      => 'Please provide any necessary details',
                'empty_data' => '',
            ])
        ;

        $builder->get('subject')->addModelTransformer(new NullToEmptyStringTransformer());
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        self::configureRouterOption($resolver);

        $resolver->setDefaults([
            'data_class' => Feedback::class,
        ]);
    }
}
