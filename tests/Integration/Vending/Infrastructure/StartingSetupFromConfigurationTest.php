<?php

declare(strict_types=1);

namespace Tests\Integration\Vending\Infrastructure;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Mother\VendingMachineMother;
use Vending\Infrastructure\StartingSetupFromConfiguration;

final class StartingSetupFromConfigurationTest extends TestCase
{
    #[Test]
    public function reads_each_slot_and_the_starting_change(): void
    {
        $startingSetup = StartingSetupFromConfiguration::read([
            'WATER' => ['product' => 'WATER', 'price' => 65, 'stock' => 10],
            'JUICE' => ['product' => 'JUICE', 'price' => 100, 'stock' => 10],
            'SODA' => ['product' => 'SODA', 'price' => 150, 'stock' => 10],
        ], [5 => 10, 10 => 10, 25 => 10]);

        self::assertEquals(VendingMachineMother::startingSetup(), $startingSetup);
    }
}
