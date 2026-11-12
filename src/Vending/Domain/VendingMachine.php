<?php

declare(strict_types=1);

namespace Vending\Domain;

final class VendingMachine
{
    public function __construct(
        public readonly VendingMachineId $id,
        public readonly AvailableChange $availableChange,
        public readonly Slots $slots,
    ) {}
}
