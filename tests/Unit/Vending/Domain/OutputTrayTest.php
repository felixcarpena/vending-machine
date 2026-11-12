<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Coin;
use Vending\Domain\Coins;
use Vending\Domain\OutputTray;

final class OutputTrayTest extends TestCase
{
    #[Test]
    public function starts_empty(): void
    {
        self::assertTrue(OutputTray::empty()->isEmpty());
    }

    #[Test]
    public function keeps_every_coin_that_comes_out(): void
    {
        $tray = OutputTray::empty()->withCoins(Coins::of(Coin::TenCents))->withCoins(Coins::of(Coin::FiveCents));

        self::assertEquals(Coins::of(Coin::TenCents, Coin::FiveCents), $tray->coins);
    }

    #[Test]
    public function stays_unchanged_when_coins_come_out(): void
    {
        $tray = OutputTray::empty();

        (void) $tray->withCoins(Coins::of(Coin::TenCents));

        self::assertTrue($tray->isEmpty());
    }
}
