<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->notPath([
        'config/bundles.php',
        'config/reference.php',
    ])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@PHP8x4Migration' => true,
        'declare_strict_types' => true,
        // Yoda style for <, <=, >, >= too (e.g. "0 < count($items)"), not only for == and ===
        'yoda_style' => ['equal' => true, 'identical' => true, 'less_and_greater' => true],
    ])
    ->setFinder($finder)
;
