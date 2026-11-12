<?php

declare(strict_types=1);

namespace Vending\Domain;

enum Coin: int
{
    case FiveCents = 5;
    case TenCents = 10;
    case TwentyFiveCents = 25;
    case OneHundredCents = 100;

    public function value(): Money
    {
        return Money::fromCents($this->value);
    }
}
