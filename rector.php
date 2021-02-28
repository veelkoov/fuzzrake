<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        SetList::SYMFONY_50,
        SetList::SYMFONY_50_TYPES,
        SetList::SYMFONY_CODE_QUALITY,
        SetList::PHP_70,
        SetList::PHP_71,
        SetList::PHP_72,
        SetList::PHP_73,
        SetList::PHP_74,
        SetList::PHP_80,
        SetList::PHPUNIT_70,
        SetList::PHPUNIT_75,
        SetList::PHPUNIT_80,
    ]);

    $parameters->set(Option::SKIP, [
        AddLiteralSeparatorToNumberRector::class,
        CountOnNullRector::class, // TODO: Reconsider
        ClassPropertyAssignToConstructorPromotionRector::class, // TODO: Remove when Doctrine annotations are supported in constructor parameters
    ]);

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::PATHS, [__DIR__.'/src', __DIR__.'/tests']);

    $parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, __DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml');

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__.'/vendor/autoload.php',
        __DIR__.'/bin/.phpunit/phpunit/vendor/autoload.php',
    ]);
};
