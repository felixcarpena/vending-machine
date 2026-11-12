<?php

declare(strict_types=1);

namespace Vending\UI\Cli\Output;

use Vending\Domain\Coin;

final class CoinFormat
{
    public static function of(Coin $coin): string
    {
        return match ($coin) {
            Coin::FiveCents => '0.05',
            Coin::TenCents => '0.10',
            Coin::TwentyFiveCents => '0.25',
            Coin::OneHundredCents => '1',
        };
    }
}
