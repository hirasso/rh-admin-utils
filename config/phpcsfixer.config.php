<?php

/**
 * PHP CS Fixer Config
 * @see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/config.rst
 */

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

return (new Config())
    ->setRules([
        '@PSR12' => true,
        '@PHP83Migration' => true,
        'no_unused_imports' => true,
        // @see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/issues/7906
        'single_space_after_construct' => true,
    ])
    ->setFinder(
        Finder::create()
            ->in(['src', 'config'])
            ->in(['.'])->depth('== 0')
    );
