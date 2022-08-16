<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony'               => true,
        'binary_operator_spaces' => [
            'operators' => ['=>' => 'align_single_space'],
        ],
        'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['const', 'class', 'function']],
    ])
    ->setFinder($finder)
;
