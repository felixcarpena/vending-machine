<?php

declare(strict_types=1);

namespace Vending\Domain;

final readonly class StartingSetup
{
    private function __construct(
        private Slots $slots,
        private AvailableChange $change,
    ) {}

    public static function of(Slots $slots, AvailableChange $change): self
    {
        return new self($slots, $change);
    }

    public function startMachine(VendingMachineId $id): VendingMachine
    {
        return new VendingMachine($id, $this->change, $this->slots);
    }
}
