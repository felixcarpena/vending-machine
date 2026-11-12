<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Mother\VendingMachineMother;
use Vending\Domain\AvailableChange;
use Vending\Domain\Coin;
use Vending\Domain\Coins;
use Vending\Domain\Event\MachineServiced;
use Vending\Domain\Exception\UnknownSlot;
use Vending\Domain\Slot;
use Vending\Domain\StockLevels;
use Vending\Domain\VendingMachine;

final class VendingMachineTest extends TestCase
{
    #[Test]
    public function replaces_the_stock_of_each_slot_when_serviced(): void
    {
        $machine = VendingMachineMother::started();

        $machine->service(StockLevels::of(['WATER' => 5, 'JUICE' => 3, 'SODA' => 1]), Coins::of());

        self::assertSame([5, 3, 1], self::stockOf($machine));
    }

    #[Test]
    public function empties_the_slots_a_service_does_not_mention(): void
    {
        $machine = VendingMachineMother::started();

        $machine->service(StockLevels::of(['WATER' => 4]), Coins::of());

        self::assertSame([4, 0, 0], self::stockOf($machine));
    }

    #[Test]
    public function replaces_the_available_change_when_serviced(): void
    {
        $machine = VendingMachineMother::started();

        $machine->service(StockLevels::of([]), Coins::of(Coin::TwentyFiveCents, Coin::TwentyFiveCents));

        self::assertEquals(
            AvailableChange::of(Coins::of(Coin::TwentyFiveCents, Coin::TwentyFiveCents)),
            $machine->availableChange,
        );
    }

    #[Test]
    public function records_that_it_was_serviced(): void
    {
        $machine = VendingMachineMother::started();

        $machine->service(StockLevels::of(['WATER' => 1]), Coins::of());

        self::assertEquals([new MachineServiced(VendingMachineMother::id())], $machine->pullEvents());
    }

    #[Test]
    public function refuses_to_service_a_slot_it_does_not_have(): void
    {
        $machine = VendingMachineMother::started();

        $this->expectException(UnknownSlot::class);

        $machine->service(StockLevels::of(['WATER' => 1, 'COFFEE' => 3]), Coins::of());
    }

    #[Test]
    public function changes_nothing_when_it_refuses_a_service(): void
    {
        $machine = VendingMachineMother::started();

        self::attemptToService($machine, StockLevels::of(['WATER' => 1, 'COFFEE' => 3]));

        self::assertEquals(VendingMachineMother::started(), $machine);
    }

    private static function attemptToService(VendingMachine $machine, StockLevels $stock): void
    {
        try {
            $machine->service($stock, Coins::of());
        } catch (UnknownSlot) {
            return;
        }
    }

    /** @return list<int> */
    private static function stockOf(VendingMachine $machine): array
    {
        return array_map(static fn(Slot $slot): int => $slot->stock, $machine->slots->all());
    }
}
