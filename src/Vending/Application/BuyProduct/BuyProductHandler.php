<?php

declare(strict_types=1);

namespace Vending\Application\BuyProduct;

use Shared\Application\Bus\EventBus;
use Vending\Domain\VendingMachineRepository;

final readonly class BuyProductHandler
{
    public function __construct(
        private VendingMachineRepository $machines,
        private EventBus $eventBus,
    ) {}

    public function __invoke(BuyProduct $command): void
    {
        $machine = $this->machines->get($command->machineId);
        $machine->buy($command->selector);
        $events = $machine->pullEvents();
        $this->machines->save($machine);

        $this->eventBus->publish(...$events);
    }
}
