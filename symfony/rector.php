<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;

return RectorConfig::configure()
    ->withPaths([__DIR__.'/migrations', __DIR__.'/src', __DIR__.'/tests'])
    ->withPhpSets()
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true,
    )
    ->withSkip([
        NullToStrictStringFuncCallArgRector::class, // False-positives; allow PHPStan to take care of those
        ReadOnlyClassRector::class, // Prefer to put readonly over data-primarily classes
    ])
;
