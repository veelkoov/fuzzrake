<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        SetList::SYMFONY_50,
        SetList::SYMFONY_50_TYPES,
        SetList::PHP_70,
        SetList::PHP_71,
        SetList::PHP_72,
        SetList::PHP_73,
        SetList::PHP_74,
    ]);

    $parameters->set(Option::EXCLUDE_RECTORS, [
        AddLiteralSeparatorToNumberRector::class,
        CountOnNullRector::class, // TODO: Reconsider
    ]);

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::PATHS, [__DIR__.'/src', __DIR__.'/tests']);

    $parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, __DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml');

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__.'/vendor/autoload.php',
        __DIR__.'/bin/.phpunit/phpunit/vendor/autoload.php',
    ]);
};
