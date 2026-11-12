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

    public static function greatestCommonDivisor(): Money
    {
        return Money::fromCents(array_reduce(
            self::cases(),
            static fn(int $divisor, self $coin): int => self::gcd($divisor, $coin->value),
            0,
        ));
    }

    private static function gcd(int $one, int $other): int
    {
        return $other === 0 ? $one : self::gcd($other, $one % $other);
    }
}
