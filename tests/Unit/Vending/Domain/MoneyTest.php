<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Exception\NegativeMoney;
use Vending\Domain\Money;

final class MoneyTest extends TestCase
{
    #[Test]
    public function adds_two_amounts(): void
    {
        $sum = Money::fromCents(65)->add(Money::fromCents(35));

        self::assertEquals(Money::fromCents(100), $sum);
    }

    #[Test]
    public function zero_is_no_cents(): void
    {
        self::assertSame(0, Money::zero()->cents);
    }

    #[Test]
    public function cannot_be_negative(): void
    {
        $this->expectException(NegativeMoney::class);

        Money::fromCents(-5);
    }

    #[Test]
    public function subtracts_an_amount(): void
    {
        $rest = Money::fromCents(100)->subtract(Money::fromCents(65));

        self::assertEquals(Money::fromCents(35), $rest);
    }

    #[Test]
    public function is_less_than_a_bigger_amount(): void
    {
        self::assertTrue(Money::fromCents(25)->isLessThan(Money::fromCents(100)));
    }

    #[Test]
    public function is_not_less_than_the_same_amount(): void
    {
        self::assertFalse(Money::fromCents(100)->isLessThan(Money::fromCents(100)));
    }

    #[Test]
    public function is_a_multiple_of_an_amount_that_divides_it(): void
    {
        self::assertTrue(Money::fromCents(65)->isMultipleOf(Money::fromCents(5)));
    }

    #[Test]
    public function is_not_a_multiple_of_an_amount_that_does_not_divide_it(): void
    {
        self::assertFalse(Money::fromCents(67)->isMultipleOf(Money::fromCents(5)));
    }
}
