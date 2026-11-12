<?php

declare(strict_types=1);

namespace Vending\Application\StartMachine;

use Vending\Domain\VendingMachine;
use Vending\Domain\VendingMachineRepository;

final readonly class StartMachineHandler
{
    public function __construct(private VendingMachineRepository $machines) {}

    public function __invoke(StartMachine $command): void
    {
        $this->machines->save(new VendingMachine($command->machineId));
    }
}
