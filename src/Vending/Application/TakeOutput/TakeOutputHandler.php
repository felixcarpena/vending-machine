<?php

declare(strict_types=1);

namespace Vending\Application\TakeOutput;

use Shared\Application\Bus\EventBus;
use Vending\Domain\VendingMachineRepository;

final readonly class TakeOutputHandler
{
    public function __construct(
        private VendingMachineRepository $machines,
        private EventBus $eventBus,
    ) {}

    public function __invoke(TakeOutput $command): void
    {
        $machine = $this->machines->get($command->machineId);
        $machine->takeOutput();
        $events = $machine->pullEvents();
        $this->machines->save($machine);

        $this->eventBus->publish(...$events);
    }
}
