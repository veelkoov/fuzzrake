<?php

declare(strict_types=1);

namespace App\Form;

use App\Feedback\Feedback;
use App\ValueObject\Routing\RouteName;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackType extends AbstractType
{
    use RouterDependentTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $router = self::getRouter($options);
        $infoPageUrl = htmlspecialchars($router->generate(RouteName::INFO, ['_fragment' => 'contact']));

        $builder
            ->add('noContactBack', CheckboxType::class, [
                'label'     => 'I acknowledge and accept the fact, that I will NOT be contacted back.',
                'help'      => "If you need a response, please contact me using any means listed on <a href=\"$infoPageUrl\">this page</a>.",
                'help_html' => true,
                'required'  => false, // DEBUG
            ])
            ->add('maker', TextType::class, [
                'label'    => 'Maker (if applicable)',
                'required' => false,
            ])
            ->add('subject', ChoiceType::class, [
                'label'   => 'What would you like to give feedback about?',
                'choices' => [
                    "Maker's commissions info (open/closed) is inaccurate",
                    "Maker's website/social account is no longer working",
                    "Other maker's information is (partially) outdated",
                    'Other information on this website needs attention (not related to a particular maker)',
                    'Suggest an improvement to this website',
                    'Report a technical problem/bug with this website',
                    'Other',
                ],
                'choice_label' => fn ($item) => $item,
                'required'     => false, // DEBUG
                'expanded'     => true,
            ])
            ->add('details', TextareaType::class, [
                'label' => 'Please provide any necessary details',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Send',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        self::configureRouterOption($resolver);

        $resolver->setDefaults([
            'data_class' => Feedback::class,
        ]);
    }
}
