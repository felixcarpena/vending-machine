<?php

declare(strict_types=1);

namespace Vending\Application\ReturnMoney;

use Shared\Application\Bus\EventBus;
use Vending\Domain\VendingMachineRepository;

final readonly class ReturnMoneyHandler
{
    public function __construct(
        private VendingMachineRepository $machines,
        private EventBus $eventBus,
    ) {}

    public function __invoke(ReturnMoney $command): void
    {
        $machine = $this->machines->get($command->machineId);
        $machine->returnMoney();
        $events = $machine->pullEvents();
        $this->machines->save($machine);

        $this->eventBus->publish(...$events);
    }
}
