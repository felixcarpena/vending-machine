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
}
