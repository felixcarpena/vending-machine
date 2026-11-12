<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Coin;
use Vending\Domain\Money;

final class CoinTest extends TestCase
{
    /** @return iterable<string, array{Coin, Money}> */
    public static function faceValues(): iterable
    {
        yield '0.05' => [Coin::FiveCents, Money::fromCents(5)];
        yield '0.10' => [Coin::TenCents, Money::fromCents(10)];
        yield '0.25' => [Coin::TwentyFiveCents, Money::fromCents(25)];
        yield '1.00' => [Coin::OneHundredCents, Money::fromCents(100)];
    }

    #[Test]
    #[DataProvider('faceValues')]
    public function is_worth_its_face_value(Coin $coin, Money $faceValue): void
    {
        self::assertEquals($faceValue, $coin->value());
    }

    #[Test]
    public function every_amount_the_coins_make_is_a_multiple_of_five_cents(): void
    {
        self::assertEquals(Money::fromCents(5), Coin::greatestCommonDivisor());
    }

    #[Test]
    public function exists_only_for_the_accepted_coins(): void
    {
        self::assertSame(
            [Coin::FiveCents, Coin::TenCents, Coin::TwentyFiveCents, Coin::OneHundredCents],
            Coin::cases(),
        );
    }
}
