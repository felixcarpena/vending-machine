<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Exception\NegativeStock;
use Vending\Domain\Money;
use Vending\Domain\Product;
use Vending\Domain\Selector;
use Vending\Domain\Slot;

final class SlotTest extends TestCase
{
    #[Test]
    public function cannot_have_a_negative_stock(): void
    {
        $this->expectException(NegativeStock::class);

        new Slot(Selector::of('WATER'), Product::named('WATER'), Money::fromCents(65), -1);
    }

    #[Test]
    public function keeps_its_selector_product_and_price_when_restocked(): void
    {
        $slot = new Slot(Selector::of('WATER'), Product::named('WATER'), Money::fromCents(65), 10);

        $restocked = $slot->withStock(3);

        self::assertEquals(
            new Slot(Selector::of('WATER'), Product::named('WATER'), Money::fromCents(65), 3),
            $restocked,
        );
    }

    #[Test]
    public function stays_unchanged_when_restocked(): void
    {
        $slot = new Slot(Selector::of('WATER'), Product::named('WATER'), Money::fromCents(65), 10);

        (void) $slot->withStock(3);

        self::assertSame(10, $slot->stock);
    }
}
