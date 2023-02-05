<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('node_modules')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony'                => true,
        'binary_operator_spaces'  => false,
        'global_namespace_import' => true, // Added to @Symfony, lots of changes, consider after learning "why"
        'ordered_imports'         => [
            'sort_algorithm' => 'alpha',
            'imports_order'  => ['const', 'class', 'function'],
        ],
    ])
    ->setFinder($finder)
;
