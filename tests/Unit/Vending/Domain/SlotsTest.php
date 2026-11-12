<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Exception\UnknownSlot;
use Vending\Domain\Money;
use Vending\Domain\Product;
use Vending\Domain\Selector;
use Vending\Domain\Slot;
use Vending\Domain\Slots;
use Vending\Domain\StockLevels;

final class SlotsTest extends TestCase
{
    #[Test]
    public function keep_the_slots_in_the_order_they_were_given(): void
    {
        $water = new Slot(Selector::of('WATER'), Product::named('WATER'), Money::fromCents(65), 10);
        $juice = new Slot(Selector::of('JUICE'), Product::named('JUICE'), Money::fromCents(100), 3);

        self::assertSame([$water, $juice], Slots::of($water, $juice)->all());
    }

    #[Test]
    public function let_two_slots_hold_the_same_product(): void
    {
        $water = new Slot(Selector::of('A1'), Product::named('WATER'), Money::fromCents(65), 10);
        $moreWater = new Slot(Selector::of('A2'), Product::named('WATER'), Money::fromCents(70), 4);

        self::assertSame([$water, $moreWater], Slots::of($water, $moreWater)->all());
    }

    #[Test]
    public function restock_every_slot_and_empty_the_ones_not_mentioned(): void
    {
        $slots = self::waterAnd(new Slot(Selector::of('JUICE'), Product::named('JUICE'), Money::fromCents(100), 3));

        $restocked = $slots->restockedTo(StockLevels::of(['JUICE' => 7]));

        self::assertSame([0, 7], self::stockOf($restocked));
    }

    #[Test]
    public function refuse_to_restock_a_slot_they_do_not_have(): void
    {
        $this->expectException(UnknownSlot::class);

        (void) self::waterAnd()->restockedTo(StockLevels::of(['COFFEE' => 1]));
    }

    #[Test]
    public function find_a_slot_by_its_selector(): void
    {
        $juice = new Slot(Selector::of('JUICE'), Product::named('JUICE'), Money::fromCents(100), 3);

        self::assertSame($juice, self::waterAnd($juice)->find(Selector::of('JUICE')));
    }

    #[Test]
    public function do_not_find_a_slot_they_do_not_have(): void
    {
        $this->expectException(UnknownSlot::class);

        self::waterAnd()->find(Selector::of('COFFEE'));
    }

    #[Test]
    public function take_one_unit_from_one_slot_only(): void
    {
        $slots = self::waterAnd(new Slot(Selector::of('JUICE'), Product::named('JUICE'), Money::fromCents(100), 3));

        $afterSale = $slots->withOneUnitLessOf(Selector::of('JUICE'));

        self::assertSame([10, 2], self::stockOf($afterSale));
    }

    private static function waterAnd(Slot ...$others): Slots
    {
        $water = new Slot(Selector::of('WATER'), Product::named('WATER'), Money::fromCents(65), 10);

        return Slots::of($water, ...$others);
    }

    /** @return list<int> */
    private static function stockOf(Slots $slots): array
    {
        return array_map(static fn(Slot $slot): int => $slot->stock, $slots->all());
    }
}
