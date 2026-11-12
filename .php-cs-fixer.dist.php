<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return new Config()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        'declare_strict_types' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['imports_order' => ['class', 'function', 'const'], 'sort_algorithm' => 'alpha'],
        'php_unit_method_casing' => ['case' => 'snake_case'],
    ])
    ->setFinder(
        new Finder()
            ->in(['src', 'tests'])
            ->append(['bin/vending-machine', 'behat.php', '.php-cs-fixer.dist.php']),
    );
