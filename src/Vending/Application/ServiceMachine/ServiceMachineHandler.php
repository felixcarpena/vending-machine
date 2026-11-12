<?php

declare(strict_types=1);

namespace Vending\Application\ServiceMachine;

use Shared\Application\Bus\EventBus;
use Vending\Domain\VendingMachineRepository;

final readonly class ServiceMachineHandler
{
    public function __construct(
        private VendingMachineRepository $machines,
        private EventBus $eventBus,
    ) {}

    public function __invoke(ServiceMachine $command): void
    {
        $machine = $this->machines->get($command->machineId);
        $machine->service($command->stock, $command->change);
        $events = $machine->pullEvents();
        $this->machines->save($machine);

        $this->eventBus->publish(...$events);
    }
}
