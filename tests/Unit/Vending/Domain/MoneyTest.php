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
    public function cannot_be_negative(): void
    {
        $this->expectException(NegativeMoney::class);

        Money::fromCents(-5);
    }
}
