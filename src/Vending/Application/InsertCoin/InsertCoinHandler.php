<?php

declare(strict_types=1);

namespace Vending\Application\InsertCoin;

use Shared\Application\Bus\EventBus;
use Vending\Domain\VendingMachineRepository;

final readonly class InsertCoinHandler
{
    public function __construct(
        private VendingMachineRepository $machines,
        private EventBus $eventBus,
    ) {}

    public function __invoke(InsertCoin $command): void
    {
        $machine = $this->machines->get($command->machineId);
        $machine->insertCoin($command->coin);
        $events = $machine->pullEvents();
        $this->machines->save($machine);

        $this->eventBus->publish(...$events);
    }
}
