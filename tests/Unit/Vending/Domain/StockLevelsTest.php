<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Exception\NegativeStock;
use Vending\Domain\Selector;
use Vending\Domain\StockLevels;

final class StockLevelsTest extends TestCase
{
    #[Test]
    public function give_the_units_of_a_slot(): void
    {
        $stock = StockLevels::of(['WATER' => 5]);

        self::assertSame(5, $stock->unitsOf(Selector::of('WATER')));
    }

    #[Test]
    public function give_no_units_of_a_slot_they_do_not_mention(): void
    {
        $stock = StockLevels::of(['WATER' => 5]);

        self::assertSame(0, $stock->unitsOf(Selector::of('SODA')));
    }

    #[Test]
    public function read_each_selector_as_the_machine_writes_it(): void
    {
        $stock = StockLevels::of([' water ' => 5]);

        self::assertSame(5, $stock->unitsOf(Selector::of('WATER')));
    }

    #[Test]
    public function cannot_hold_a_negative_number_of_units(): void
    {
        $this->expectException(NegativeStock::class);

        StockLevels::of(['WATER' => -1]);
    }
}
