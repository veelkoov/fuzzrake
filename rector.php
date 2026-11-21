<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;

return RectorConfig::configure()
    ->withPaths([__DIR__.'/global', __DIR__.'/migrations', __DIR__.'/src', __DIR__.'/tests'])
    ->withPhpSets()
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true,
    )
    ->withSkip([
        ClassPropertyAssignToConstructorPromotionRector::class, // Leave my Doctrine entities alone
        ReadOnlyClassRector::class, // Prefer to put readonly over data-primarily classes
    ])
;
