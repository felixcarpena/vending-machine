<?php

declare(strict_types=1);

namespace Vending\Domain;

use Vending\Domain\Exception\EmptySlot;
use Vending\Domain\Exception\InvalidPrice;
use Vending\Domain\Exception\NegativeStock;

final readonly class Slot
{
    public function __construct(
        public Selector $selector,
        public Product $product,
        public Money $price,
        public int $stock,
    ) {
        $step = Coin::greatestCommonDivisor();
        if (!Money::zero()->isLessThan($price) || !$price->isMultipleOf($step)) {
            throw InvalidPrice::of($selector, $price, $step);
        }
        if ($stock < 0) {
            throw NegativeStock::of($selector, $stock);
        }
    }

    #[\NoDiscard]
    public function withStock(int $stock): self
    {
        return new self($this->selector, $this->product, $this->price, $stock);
    }

    #[\NoDiscard]
    public function withOneUnitLess(): self
    {
        if ($this->stock === 0) {
            throw EmptySlot::of($this->selector);
        }

        return $this->withStock($this->stock - 1);
    }
}
