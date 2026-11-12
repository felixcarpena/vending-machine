<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Coin;
use Vending\Domain\Coins;
use Vending\Domain\Money;

final class CoinsTest extends TestCase
{
    #[Test]
    public function are_worth_nothing_when_there_are_none(): void
    {
        self::assertEquals(Money::zero(), Coins::of()->total());
    }

    #[Test]
    public function are_empty_when_there_are_none(): void
    {
        self::assertTrue(Coins::of()->isEmpty());
    }

    #[Test]
    public function are_not_empty_once_there_is_a_coin(): void
    {
        self::assertFalse(Coins::of(Coin::FiveCents)->isEmpty());
    }

    #[Test]
    public function are_worth_the_sum_of_every_coin(): void
    {
        $coins = Coins::of(Coin::TenCents, Coin::TenCents, Coin::OneHundredCents);

        self::assertEquals(Money::fromCents(120), $coins->total());
    }

    #[Test]
    public function keep_the_coins_already_there_when_one_is_added(): void
    {
        $coins = Coins::of(Coin::TenCents)->add(Coin::TwentyFiveCents);

        self::assertEquals(Coins::of(Coin::TenCents, Coin::TwentyFiveCents), $coins);
    }

    #[Test]
    public function count_how_many_of_a_coin_there_are(): void
    {
        $coins = Coins::of(Coin::TenCents, Coin::OneHundredCents, Coin::TenCents);

        self::assertSame(2, $coins->countOf(Coin::TenCents));
    }

    #[Test]
    public function count_none_of_a_coin_that_is_not_there(): void
    {
        $coins = Coins::of(Coin::TenCents);

        self::assertSame(0, $coins->countOf(Coin::FiveCents));
    }

    #[Test]
    public function list_the_largest_coins_first(): void
    {
        $coins = Coins::of(Coin::FiveCents, Coin::OneHundredCents, Coin::TwentyFiveCents);

        self::assertSame([Coin::OneHundredCents, Coin::TwentyFiveCents, Coin::FiveCents], $coins->largestFirst());
    }

    #[Test]
    public function stay_unchanged_when_a_coin_is_added(): void
    {
        $coins = Coins::of(Coin::TenCents);

        (void) $coins->add(Coin::FiveCents);

        self::assertEquals(Coins::of(Coin::TenCents), $coins);
    }

    #[Test]
    public function put_two_sets_of_coins_together(): void
    {
        $coins = Coins::of(Coin::TenCents)->plus(Coins::of(Coin::FiveCents, Coin::TenCents));

        self::assertSame(2, $coins->countOf(Coin::TenCents));
    }
}
