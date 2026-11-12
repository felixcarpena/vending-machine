<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Mother\VendingMachineMother;
use Vending\Domain\Money;
use Vending\Domain\Product;
use Vending\Domain\Selector;
use Vending\Domain\Slot;
use Vending\Domain\Slots;
use Vending\Domain\StartingSetup;
use Vending\Domain\VendingMachine;

final class StartingSetupTest extends TestCase
{
    #[Test]
    public function starts_a_machine_with_its_slots(): void
    {
        $slots = Slots::of(new Slot(Selector::of('WATER'), Product::named('WATER'), Money::fromCents(65), 10));

        $machine = StartingSetup::of($slots)->startMachine(VendingMachineMother::id());

        self::assertEquals(new VendingMachine(VendingMachineMother::id(), $slots), $machine);
    }
}
