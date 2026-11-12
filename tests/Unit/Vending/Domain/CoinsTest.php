<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Coin;
use Vending\Domain\Coins;

final class CoinsTest extends TestCase
{
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
}
