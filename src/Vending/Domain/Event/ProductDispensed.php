<?php

declare(strict_types=1);

namespace Vending\Domain\Event;

use Shared\Domain\DomainEvent;
use Vending\Domain\Money;
use Vending\Domain\Product;
use Vending\Domain\Selector;
use Vending\Domain\VendingMachineId;

final readonly class ProductDispensed implements DomainEvent
{
    public function __construct(
        public VendingMachineId $machineId,
        public Selector $selector,
        public Product $product,
        public Money $price,
    ) {}
}
