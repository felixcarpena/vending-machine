<?php

declare(strict_types=1);

namespace Vending\Application\GetMachineContents;

use Vending\Domain\Money;

final readonly class SlotContents
{
    public function __construct(
        public string $selector,
        public string $product,
        public Money $price,
        public int $stock,
    ) {}
}
