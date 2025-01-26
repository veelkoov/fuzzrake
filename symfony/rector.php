<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\CodeQuality\Rector\MethodCall\LiteralGetToRequestClassConstantRector;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Symfony62\Rector\MethodCall\SimplifyFormRenderingRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_84,

        SymfonySetList::SYMFONY_60,
        SymfonySetList::SYMFONY_61,
        SymfonySetList::SYMFONY_62,
        SymfonySetList::SYMFONY_63,
        SymfonySetList::SYMFONY_64,
        SymfonySetList::SYMFONY_70,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,

        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
        DoctrineSetList::DOCTRINE_BUNDLE_210,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        DoctrineSetList::DOCTRINE_COLLECTION_22,
        DoctrineSetList::DOCTRINE_COMMON_20,
        DoctrineSetList::DOCTRINE_DBAL_211,
        DoctrineSetList::DOCTRINE_DBAL_30,
        DoctrineSetList::DOCTRINE_DBAL_40,
        DoctrineSetList::DOCTRINE_ORM_213,
        DoctrineSetList::DOCTRINE_ORM_214,
        DoctrineSetList::DOCTRINE_ORM_25,
        DoctrineSetList::DOCTRINE_ORM_29,
        DoctrineSetList::GEDMO_ANNOTATIONS_TO_ATTRIBUTES,
        DoctrineSetList::MONGODB__ANNOTATIONS_TO_ATTRIBUTES,
        DoctrineSetList::YAML_TO_ANNOTATIONS,
        DoctrineSetList::TYPED_COLLECTIONS,

        PHPUnitSetList::PHPUNIT_90,
        // PHPUnitSetList::PHPUNIT_CODE_QUALITY, // Lots of changes. TODO
    ]);

    $rectorConfig->parallel();

    $rectorConfig->skip([
        ReadOnlyClassRector::class, // Let me decide when

        LiteralGetToRequestClassConstantRector::class, // SYMFONY_CODE_QUALITY. Low value, lots of changes. TODO
        SimplifyFormRenderingRector::class, // SYMFONY_62. TODO
    ]);
};
