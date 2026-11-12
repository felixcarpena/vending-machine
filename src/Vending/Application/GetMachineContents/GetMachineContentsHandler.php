<?php

declare(strict_types=1);

namespace Vending\Application\GetMachineContents;

use Vending\Domain\Slot;
use Vending\Domain\VendingMachineRepository;

final readonly class GetMachineContentsHandler
{
    public function __construct(private VendingMachineRepository $machines) {}

    public function __invoke(GetMachineContents $query): MachineContents
    {
        $machine = $this->machines->get($query->machineId);

        return new MachineContents(array_map(
            static fn(Slot $slot): SlotContents => new SlotContents(
                $slot->selector->value,
                $slot->product->name,
                $slot->price,
                $slot->stock,
            ),
            $machine->slots->all(),
        ));
    }
}
