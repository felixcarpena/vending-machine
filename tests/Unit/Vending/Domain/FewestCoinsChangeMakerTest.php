<?php

declare(strict_types=1);

namespace Tests\Unit\Vending\Domain;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vending\Domain\Coin;
use Vending\Domain\Coins;
use Vending\Domain\FewestCoinsChangeMaker;
use Vending\Domain\Money;

final class FewestCoinsChangeMakerTest extends TestCase
{
    private FewestCoinsChangeMaker $changeMaker;

    protected function setUp(): void
    {
        $this->changeMaker = new FewestCoinsChangeMaker();
    }

    #[Test]
    public function pays_no_coins_when_no_change_is_due(): void
    {
        $change = $this->changeMaker->changeFor(Money::zero(), Coins::of());

        self::assertEquals(Coins::of(), $change);
    }

    #[Test]
    public function pays_with_the_fewest_coins(): void
    {
        $available = Coins::of(Coin::FiveCents, Coin::FiveCents, Coin::TenCents, Coin::TenCents, Coin::TwentyFiveCents);

        $change = $this->changeMaker->changeFor(Money::fromCents(35), $available);

        self::assertEquals(Coins::of(Coin::TwentyFiveCents, Coin::TenCents), $change);
    }

    #[Test]
    public function pays_when_taking_the_largest_coin_first_would_fail(): void
    {
        $available = Coins::of(Coin::TwentyFiveCents, Coin::TenCents, Coin::TenCents, Coin::TenCents);

        $change = $this->changeMaker->changeFor(Money::fromCents(30), $available);

        self::assertEquals(Coins::of(Coin::TenCents, Coin::TenCents, Coin::TenCents), $change);
    }

    #[Test]
    public function never_pays_with_one_unit_coins(): void
    {
        self::assertNull($this->changeMaker->changeFor(Money::fromCents(100), Coins::of(Coin::OneHundredCents)));
    }

    #[Test]
    public function pays_nothing_for_an_amount_its_coins_cannot_make(): void
    {
        self::assertNull($this->changeMaker->changeFor(Money::fromCents(10), Coins::of(Coin::TwentyFiveCents)));
    }

    #[Test]
    public function can_always_pay_no_change(): void
    {
        self::assertTrue($this->changeMaker->payableUpTo(Money::fromCents(50), Coins::of())->includes(Money::zero()));
    }

    #[Test]
    public function can_pay_only_as_many_coins_as_it_holds(): void
    {
        $payable = $this->changeMaker->payableUpTo(Money::fromCents(50), Coins::of(Coin::TwentyFiveCents));

        self::assertSame(
            [true, false],
            [$payable->includes(Money::fromCents(25)), $payable->includes(Money::fromCents(50))],
        );
    }

    #[Test]
    public function can_pay_the_amounts_its_coins_add_up_to(): void
    {
        $available = Coins::of(Coin::TwentyFiveCents, Coin::TenCents, Coin::TenCents, Coin::TenCents);

        $payable = $this->changeMaker->payableUpTo(Money::fromCents(60), $available);

        self::assertSame(
            [true, true, true, false, false],
            array_map(
                static fn(int $cents): bool => $payable->includes(Money::fromCents($cents)),
                [30, 35, 55, 5, 60],
            ),
        );
    }

    #[Test]
    public function cannot_pay_with_one_unit_coins(): void
    {
        $payable = $this->changeMaker->payableUpTo(Money::fromCents(100), Coins::of(Coin::OneHundredCents));

        self::assertFalse($payable->includes(Money::fromCents(100)));
    }

    #[Test]
    public function cannot_pay_above_the_limit_it_was_asked_for(): void
    {
        $payable = $this->changeMaker->payableUpTo(Money::fromCents(20), Coins::of(Coin::TwentyFiveCents));

        self::assertFalse($payable->includes(Money::fromCents(25)));
    }
}
