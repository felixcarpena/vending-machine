<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Mother\VendingMachineMother;
use Throwable;
use Vending\Domain\AvailableChange;
use Vending\Domain\Coin;
use Vending\Domain\Coins;
use Vending\Domain\Event\CoinInserted;
use Vending\Domain\Event\CoinsReturned;
use Vending\Domain\Event\MachineServiced;
use Vending\Domain\Event\OutputTaken;
use Vending\Domain\Event\ProductDispensed;
use Vending\Domain\Exception\ChangeNotAvailable;
use Vending\Domain\Exception\EmptySlot;
use Vending\Domain\Exception\NotEnoughMoney;
use Vending\Domain\Exception\UnknownSlot;
use Vending\Domain\Money;
use Vending\Domain\Product;
use Vending\Domain\Selector;
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

    #[Test]
    public function records_each_coin_inserted(): void
    {
        $machine = VendingMachineMother::withCoinsInserted();

        $machine->insertCoin(Coin::TwentyFiveCents);

        self::assertEquals(
            [new CoinInserted(VendingMachineMother::id(), Coin::TwentyFiveCents)],
            $machine->pullEvents(),
        );
    }

    #[Test]
    public function holds_the_value_of_the_coins_inserted(): void
    {
        $machine = VendingMachineMother::withCoinsInserted();

        $machine->insertCoin(Coin::OneHundredCents);
        $machine->insertCoin(Coin::TenCents);

        self::assertEquals(Money::fromCents(110), $machine->insertedMoney());
    }

    #[Test]
    public function returns_the_coins_inserted(): void
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::TenCents, Coin::OneHundredCents);

        $machine->returnMoney();

        self::assertEquals(
            [new CoinsReturned(VendingMachineMother::id(), Coins::of(Coin::TenCents, Coin::OneHundredCents))],
            $machine->pullEvents(),
        );
    }

    #[Test]
    public function holds_no_money_once_the_money_is_returned(): void
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::TenCents);

        $machine->returnMoney();

        self::assertEquals(Money::zero(), $machine->insertedMoney());
    }

    #[Test]
    public function puts_the_returned_coins_in_the_output_tray(): void
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::TenCents, Coin::OneHundredCents);

        $machine->returnMoney();

        self::assertEquals(Coins::of(Coin::TenCents, Coin::OneHundredCents), $machine->outputTray->coins);
    }

    #[Test]
    public function empties_the_output_tray_once_it_is_taken(): void
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::TenCents);
        $machine->returnMoney();

        $machine->takeOutput();

        self::assertTrue($machine->outputTray->isEmpty());
    }

    #[Test]
    public function records_that_the_output_was_taken(): void
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::TenCents);
        $machine->returnMoney();
        $machine->pullEvents();

        $machine->takeOutput();

        self::assertEquals([new OutputTaken(VendingMachineMother::id())], $machine->pullEvents());
    }

    #[Test]
    public function records_nothing_when_the_output_tray_is_already_empty(): void
    {
        $machine = VendingMachineMother::started();

        $machine->takeOutput();

        self::assertSame([], $machine->pullEvents());
    }

    #[Test]
    public function returns_nothing_when_nothing_was_inserted(): void
    {
        $machine = VendingMachineMother::withCoinsInserted();

        $machine->returnMoney();

        self::assertSame([], $machine->pullEvents());
    }

    #[Test]
    public function returns_nothing_more_once_the_money_is_returned(): void
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::TenCents);
        $machine->returnMoney();
        $machine->pullEvents();

        $machine->returnMoney();

        self::assertSame([], $machine->pullEvents());
    }

    #[Test]
    public function hands_out_each_event_only_once(): void
    {
        $machine = VendingMachineMother::withCoinsInserted();
        $machine->insertCoin(Coin::FiveCents);
        $machine->pullEvents();

        self::assertSame([], $machine->pullEvents());
    }

    #[Test]
    public function dispenses_the_product_paid_with_the_exact_amount(): void
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::OneHundredCents);

        $machine->buy(Selector::of('JUICE'));

        self::assertEquals(
            [new ProductDispensed(VendingMachineMother::id(), Selector::of('JUICE'), Product::named('JUICE'), Money::fromCents(100))],
            $machine->pullEvents(),
        );
    }

    #[Test]
    public function takes_one_unit_of_the_product_sold(): void
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::OneHundredCents);

        $machine->buy(Selector::of('JUICE'));

        self::assertSame([10, 9, 10], self::stockOf($machine));
    }

    #[Test]
    public function puts_the_product_sold_in_the_output_tray(): void
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::OneHundredCents);

        $machine->buy(Selector::of('JUICE'));

        self::assertEquals([Product::named('JUICE')], $machine->outputTray->products);
    }

    #[Test]
    public function keeps_the_inserted_coins_as_change_once_sold(): void
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::OneHundredCents);

        $machine->buy(Selector::of('JUICE'));

        self::assertSame(1, $machine->availableChange->coins->countOf(Coin::OneHundredCents));
    }

    #[Test]
    public function holds_no_money_once_sold(): void
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::OneHundredCents);

        $machine->buy(Selector::of('JUICE'));

        self::assertEquals(Money::zero(), $machine->insertedMoney());
    }

    /** @return iterable<string, array{VendingMachine, string, class-string<Throwable>}> */
    public static function purchasesItRefuses(): iterable
    {
        $oneUnitInserted = VendingMachineMother::withCoinsInserted(Coin::OneHundredCents);
        $aQuarterInserted = VendingMachineMother::withCoinsInserted(Coin::TwentyFiveCents);

        yield 'a slot it does not have' => [$oneUnitInserted, 'COFFEE', UnknownSlot::class];
        yield 'not enough money' => [$aQuarterInserted, 'JUICE', NotEnoughMoney::class];
        yield 'an empty slot' => [self::soldOutOfJuice(), 'JUICE', EmptySlot::class];
        yield 'change it cannot pay yet' => [clone $oneUnitInserted, 'WATER', ChangeNotAvailable::class];
    }

    /** @param class-string<Throwable> $refusal */
    #[Test]
    #[DataProvider('purchasesItRefuses')]
    public function refuses_a_purchase_it_cannot_complete(
        VendingMachine $machine,
        string $selector,
        string $refusal,
    ): void {
        $this->expectException($refusal);

        $machine->buy(Selector::of($selector));
    }

    #[Test]
    public function changes_nothing_when_it_refuses_a_purchase(): void
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::TwentyFiveCents);

        self::attemptToBuy($machine, 'JUICE');

        self::assertEquals(VendingMachineMother::withCoinsInserted(Coin::TwentyFiveCents), $machine);
    }

    private static function soldOutOfJuice(): VendingMachine
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::OneHundredCents);
        $machine->service(
            StockLevels::of(['WATER' => 10, 'SODA' => 10]),
            VendingMachineMother::startingChange()->coins,
        );
        $machine->pullEvents();

        return $machine;
    }

    private static function attemptToBuy(VendingMachine $machine, string $selector): void
    {
        try {
            $machine->buy(Selector::of($selector));
        } catch (NotEnoughMoney|EmptySlot|ChangeNotAvailable|UnknownSlot) {
            return;
        }
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
