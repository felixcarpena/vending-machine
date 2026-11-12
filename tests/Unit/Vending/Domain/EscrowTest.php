<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Coin;
use Vending\Domain\Coins;
use Vending\Domain\Escrow;
use Vending\Domain\Money;

final class EscrowTest extends TestCase
{
    #[Test]
    public function holds_no_money_when_empty(): void
    {
        self::assertEquals(Money::zero(), Escrow::empty()->total());
    }

    #[Test]
    public function is_empty_when_nothing_is_inserted(): void
    {
        self::assertTrue(Escrow::empty()->isEmpty());
    }

    #[Test]
    public function is_not_empty_once_a_coin_is_inserted(): void
    {
        self::assertFalse(Escrow::empty()->insert(Coin::TenCents)->isEmpty());
    }

    #[Test]
    public function holds_the_exact_coins_inserted(): void
    {
        $escrow = Escrow::empty()->insert(Coin::OneHundredCents)->insert(Coin::TwentyFiveCents);

        self::assertEquals(Coins::of(Coin::OneHundredCents, Coin::TwentyFiveCents), $escrow->coins);
    }

    #[Test]
    public function holds_the_value_of_the_coins_inserted(): void
    {
        $escrow = Escrow::empty()->insert(Coin::OneHundredCents)->insert(Coin::TwentyFiveCents);

        self::assertEquals(Money::fromCents(125), $escrow->total());
    }

    #[Test]
    public function stays_unchanged_when_a_coin_is_inserted(): void
    {
        $escrow = Escrow::empty();

        (void) $escrow->insert(Coin::TenCents);

        self::assertEquals(Escrow::empty(), $escrow);
    }

    #[Test]
    public function lists_the_ways_to_cover_a_price_cheapest_first(): void
    {
        $escrow = Escrow::empty()->insert(Coin::OneHundredCents)->insert(Coin::TenCents);

        $payments = $escrow->paymentsFor(Money::fromCents(100));

        self::assertEquals(
            [Coins::of(Coin::OneHundredCents), Coins::of(Coin::TenCents, Coin::OneHundredCents)],
            $payments,
        );
    }

    #[Test]
    public function offers_no_way_to_cover_a_price_above_what_was_inserted(): void
    {
        $escrow = Escrow::empty()->insert(Coin::TwentyFiveCents);

        self::assertSame([], $escrow->paymentsFor(Money::fromCents(65)));
    }
}
