<?php

declare(strict_types=1);

namespace Vending\Application\StartMachine;

use Vending\Domain\StartingSetup;
use Vending\Domain\VendingMachineRepository;

final readonly class StartMachineHandler
{
    public function __construct(
        private StartingSetup $startingSetup,
        private VendingMachineRepository $machines,
    ) {}

    public function __invoke(StartMachine $command): void
    {
        $this->machines->save($this->startingSetup->startMachine($command->machineId));
    }
}
