<?php

declare(strict_types=1);

namespace App\Form\InclusionUpdate;

use App\Controller\IuForm\Utils\StartData;
use App\Utils\Enforce;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<StartData>
 */
class Start extends AbstractType
{
    final public const string OPT_STUDIO_NAME = 'studio_name';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('confirmNoPendingUpdates', ChoiceType::class, [
                'label'      => 'Have you submitted the form previously?',
                'choices'    => [
                    "No, I haven't send anything previously"                 => 'no-prior-submissions',
                    'Yes, and I can see all the changes I requested on-line' => 'all-submissions-on-line',
                    "Yes, but I don't see my requested changes"              => 'submission-pending',
                ],
                'expanded'   => true,
                'required'   => true,
            ])
            ->add('decisionOverPreviousUpdates', ChoiceType::class, [
                'label'      => 'How do you want your previous submission to be handled?',
                'choices'    => [
                    'Cancel my previous submission, I will re-apply all the changes I want now'  => 'can-be-cancelled',
                    'I want the previous submission to be processed before submitting a new one' => 'can-not-be-cancelled',
                ],
                'expanded'   => true,
                'required'   => true,
            ])
            ->add('confirmYouAreTheCreator', ChoiceType::class, [
                'label'      => 'Are you the maker (studio member)?',
                'choices'    => [
                    'I am the maker'                                  => 'i-am-the-creator',
                    "I'm a friend or a customer or other/non-related" => 'not-the-creator',
                ],
                'expanded'   => true,
                'required'   => true,
            ])
        ;

        if (null === $options[self::OPT_STUDIO_NAME]) {
            $builder
                ->add('confirmAddingANewOne', ChoiceType::class, [
                    'label'   => 'You are about to request adding a new studio/maker. Is this right?',
                    'choices' => [
                        'Yes'                                 => 'yes',
                        'No, I want to update a maker/studio' => 'no',
                    ],
                    'expanded' => true,
                    'required' => true,
                ])
                ->add('ensureStudioIsNotThereAlready', ChoiceType::class, [
                    'label'      => 'You could already be on the list even if you haven\'t ever sent any form. Please navigate to the main page using the <i class="fa-solid fa-filter-circle-xmark"></i> link above. Check your old names as well.',
                    'label_html' => true,
                    'choices'    => [
                        "Checked after clicking the link above - I'm not there" => 'is-new-studio',
                        "I've found my old name/studio"                         => 'found-old-studio',
                    ],
                    'expanded'  => true,
                    'required'  => true,
                ])
            ;
        } else {
            $studioName = htmlspecialchars(Enforce::string($options[self::OPT_STUDIO_NAME]));

            $builder
                ->add('confirmUpdatingTheRightOne', ChoiceType::class, [
                    'label'      => "Is this the studio/maker you want to update: <em>$studioName</em>?",
                    'label_html' => true,
                    'choices'    => [
                        'Yes'                                          => 'correct',
                        'No, that is not the one I want to update'     => 'update-other-one',
                        'No, instead I want to add a new maker/studio' => 'add-new-instead',
                    ],
                    'expanded' => true,
                    'required' => true,
                ])
            ;
        }
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'iu_form';
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', StartData::class);

        $resolver
            ->define(self::OPT_STUDIO_NAME)
            ->allowedTypes('string', 'null')
            ->required()
        ;
    }
}
