<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(SetList::PHP_70);
    $containerConfigurator->import(SetList::PHP_71);
    $containerConfigurator->import(SetList::PHP_72);
    $containerConfigurator->import(SetList::PHP_73);
    $containerConfigurator->import(SetList::PHP_74);
    $containerConfigurator->import(SetList::PHP_80);
    $containerConfigurator->import(SetList::PHP_81);
    $containerConfigurator->import(SymfonySetList::SYMFONY_50);
    $containerConfigurator->import(SymfonySetList::SYMFONY_52);
    $containerConfigurator->import(SymfonySetList::SYMFONY_CODE_QUALITY);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_60);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_70);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_75);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_80);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_90);
    $containerConfigurator->import(PHPUnitSetList::PHPUNIT_91);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SKIP, [
        AddLiteralSeparatorToNumberRector::class,
        CountOnNullRector::class, // TODO: Reconsider
        ClassPropertyAssignToConstructorPromotionRector::class, // TODO: Remove when Doctrine annotations are supported in constructor parameters
        AddDoesNotPerformAssertionToNonAssertingTestRector::class, // Not an issue in our case; we don't have "unsafe" tests
        JsonThrowOnErrorRector::class, // Using our own utility class, using named arguments
    ]);

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::PATHS, [__DIR__.'/src', __DIR__.'/tests']);

    $parameters->set(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, __DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml');

    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__.'/vendor/autoload.php',
        __DIR__.'/vendor/bin/.phpunit/phpunit/vendor/autoload.php',
    ]);
};
