<?php

declare(strict_types=1);

namespace Vending\Domain;

use Vending\Domain\Exception\NegativeStock;

final readonly class Slot
{
    public function __construct(
        public Selector $selector,
        public Product $product,
        public Money $price,
        public int $stock,
    ) {
        if ($stock < 0) {
            throw NegativeStock::of($selector, $stock);
        }
    }
}
