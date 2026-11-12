<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Profile;
use Behat\Config\Suite;
use Tests\Behat\VendingMachineContext;

function acceptanceSuite(string $tags): Suite
{
    return new Suite('acceptance')
        ->withPaths('%paths.base%/features')
        ->withContexts(VendingMachineContext::class)
        ->withFilter(new TagFilter($tags));
}

return new Config()
    ->withProfile(new Profile('default')->withSuite(acceptanceSuite('~@wip')))
    ->withProfile(new Profile('wip')->withSuite(acceptanceSuite('@wip')));
