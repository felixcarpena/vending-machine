<?php

declare(strict_types=1);

namespace Vending\Domain;

final readonly class StartingSetup
{
    private function __construct(private Slots $slots) {}

    public static function of(Slots $slots): self
    {
        return new self($slots);
    }

    public function startMachine(VendingMachineId $id): VendingMachine
    {
        return new VendingMachine($id, $this->slots);
    }
}
