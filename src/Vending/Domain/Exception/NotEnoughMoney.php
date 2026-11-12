<?php

declare(strict_types=1);

namespace Vending\Domain\Exception;

use Shared\Domain\Refusal;
use Vending\Domain\Money;

final class NotEnoughMoney extends Refusal
{
    private function __construct(
        public readonly Money $price,
        public readonly Money $inserted,
    ) {
        parent::__construct(sprintf('%d cents inserted for a price of %d cents.', $inserted->cents, $price->cents));
    }

    public static function for(Money $price, Money $inserted): self
    {
        return new self($price, $inserted);
    }
}
