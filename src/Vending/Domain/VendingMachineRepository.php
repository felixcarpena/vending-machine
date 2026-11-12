<?php

declare(strict_types=1);

namespace Vending\Domain;

use Vending\Domain\Exception\VendingMachineNotFound;

interface VendingMachineRepository
{
    /** @throws VendingMachineNotFound */
    public function get(VendingMachineId $id): VendingMachine;

    public function save(VendingMachine $machine): void;
}
