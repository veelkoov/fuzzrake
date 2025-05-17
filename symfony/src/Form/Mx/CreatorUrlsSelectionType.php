<?php

declare(strict_types=1);

namespace App\Form\Mx;

use App\Utils\Enforce;
use App\Utils\Mx\CreatorUrlsSelectionData;
use App\Utils\Mx\GroupedUrls;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreatorUrlsSelectionType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $urls = Enforce::objectOf($options['urls'], GroupedUrls::class);

        foreach ($urls->urls as $url) {
            $builder->add($url->getId(), CheckboxType::class, [
                'required' => false,
                'label' => $url->getLabel(),
                'getter' => static fn (CreatorUrlsSelectionData $choices, FormInterface $form): bool => $choices->get($form->getName()),
                'setter' => function (CreatorUrlsSelectionData $choices, bool $selected, FormInterface $form): void {
                    $choices->set($form->getName(), $selected);
                },
            ]);
        }

        $builder
            ->add('review', SubmitType::class, [
                'label' => 'Review removal',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('data_class', CreatorUrlsSelectionData::class)
            ->setRequired('urls')
            ->setAllowedTypes('urls', GroupedUrls::class)
        ;
    }
}
