<?php

declare(strict_types=1);

namespace Vending\Application\GetOutputTray;

use Vending\Domain\VendingMachineRepository;

final readonly class GetOutputTrayHandler
{
    public function __construct(private VendingMachineRepository $machines) {}

    public function __invoke(GetOutputTray $query): OutputTrayContents
    {
        $tray = $this->machines->get($query->machineId)->outputTray;

        return new OutputTrayContents($tray->coins);
    }
}
