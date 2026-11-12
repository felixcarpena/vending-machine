<?php

declare(strict_types=1);

namespace Vending\Infrastructure;

use Vending\Domain\Exception\VendingMachineNotFound;
use Vending\Domain\VendingMachine;
use Vending\Domain\VendingMachineId;
use Vending\Domain\VendingMachineRepository;

final class InMemoryVendingMachineRepository implements VendingMachineRepository
{
    /** @var array<string, VendingMachine> */
    private array $machines = [];

    public function __construct(VendingMachine ...$machines)
    {
        foreach ($machines as $machine) {
            $this->save($machine);
        }
    }

    public function get(VendingMachineId $id): VendingMachine
    {
        $machine = $this->machines[$id->value] ?? throw VendingMachineNotFound::withId($id);

        return clone $machine;
    }

    public function save(VendingMachine $machine): void
    {
        $this->machines[$machine->id->value] = clone $machine;
    }
}
