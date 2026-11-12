<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Money;
use Vending\Domain\Product;
use Vending\Domain\Selector;
use Vending\Domain\Slot;
use Vending\Domain\Slots;

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
}
