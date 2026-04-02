<?php

declare(strict_types=1);

namespace App\Form\Submission;

use App\Entity\DiscussionComment;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<DiscussionComment>
 */
class CommentType extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
            ])
        ;
    }
}
