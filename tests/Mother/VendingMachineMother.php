<?php

declare(strict_types=1);

namespace Tests\Mother;

use Vending\Domain\AvailableChange;
use Vending\Domain\Coin;
use Vending\Domain\Coins;
use Vending\Domain\Money;
use Vending\Domain\Product;
use Vending\Domain\Selector;
use Vending\Domain\Slot;
use Vending\Domain\Slots;
use Vending\Domain\StartingSetup;
use Vending\Domain\VendingMachine;
use Vending\Domain\VendingMachineId;

final class VendingMachineMother
{
    public static function id(): VendingMachineId
    {
        return VendingMachineId::of('0193b4e2-7c2a-7d3f-8a1b-2c3d4e5f6a7b');
    }

    public static function started(): VendingMachine
    {
        return self::startedAs(self::id());
    }

    public static function startedAs(VendingMachineId $id): VendingMachine
    {
        return new VendingMachine($id, self::startingChange(), self::startingSlots());
    }

    public static function startingSetup(): StartingSetup
    {
        return StartingSetup::of(self::startingSlots(), self::startingChange());
    }

    public static function startingChange(): AvailableChange
    {
        return AvailableChange::of(Coins::of(
            ...array_fill(0, 10, Coin::FiveCents),
            ...array_fill(0, 10, Coin::TenCents),
            ...array_fill(0, 10, Coin::TwentyFiveCents),
        ));
    }

    private static function startingSlots(): Slots
    {
        return Slots::of(
            new Slot(Selector::of('WATER'), Product::named('WATER'), Money::fromCents(65), 10),
            new Slot(Selector::of('JUICE'), Product::named('JUICE'), Money::fromCents(100), 10),
            new Slot(Selector::of('SODA'), Product::named('SODA'), Money::fromCents(150), 10),
        );
    }
}
