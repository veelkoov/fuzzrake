<?php

declare(strict_types=1);

namespace App\Form\Submission;

use App\Entity\Post;
use App\Utils\Enforce;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Post>
 */
class PostType extends AbstractType
{
    public const string OPT_PREFIX = 'OPT_PREFIX';

    private ?string $blockPrefix = null;

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class)
        ;

        $this->blockPrefix = Enforce::string($options[self::OPT_PREFIX]);
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Post::class,
            ])
            ->define(self::OPT_PREFIX)
            ->allowedTypes('string');
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return $this->blockPrefix ?? parent::getBlockPrefix();
    }
}
