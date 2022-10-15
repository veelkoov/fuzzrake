<?php

declare(strict_types=1);

namespace App\Form;

use InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

trait RouterDependentTrait
{
    public static function configureRouterOption(OptionsResolver $resolver): void
    {
        $resolver
            ->define('router')
            ->allowedTypes(RouterInterface::class)
            ->required();
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function getRouter(array $options): RouterInterface
    {
        $result = $options['router'] ?? null;

        if (!$result instanceof RouterInterface) {
            throw new InvalidArgumentException('Expected a router instance provided in options'); // @codeCoverageIgnore
        }

        return $result;
    }
}
