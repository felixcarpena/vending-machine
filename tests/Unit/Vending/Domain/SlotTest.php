<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Exception\EmptySlot;
use Vending\Domain\Exception\InvalidPrice;
use Vending\Domain\Exception\NegativeStock;
use Vending\Domain\Money;
use Vending\Domain\Product;
use Vending\Domain\Selector;
use Vending\Domain\Slot;

final class SlotTest extends TestCase
{
    /** @return array<string, array{int}> */
    public static function unsellablePrices(): array
    {
        return [
            'free' => [0],
            'not payable with the accepted coins' => [67],
        ];
    }

    #[Test]
    #[DataProvider('unsellablePrices')]
    public function cannot_have_a_price_it_could_never_sell_at(int $cents): void
    {
        $this->expectException(InvalidPrice::class);

        new Slot(Selector::of('WATER'), Product::named('WATER'), Money::fromCents($cents), 10);
    }

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

    #[Test]
    public function has_one_unit_less_once_one_is_taken(): void
    {
        $slot = new Slot(Selector::of('WATER'), Product::named('WATER'), Money::fromCents(65), 10);

        self::assertSame(9, $slot->withOneUnitLess()->stock);
    }

    #[Test]
    public function cannot_give_a_unit_when_empty(): void
    {
        $slot = new Slot(Selector::of('WATER'), Product::named('WATER'), Money::fromCents(65), 0);

        $this->expectException(EmptySlot::class);

        (void) $slot->withOneUnitLess();
    }
}
