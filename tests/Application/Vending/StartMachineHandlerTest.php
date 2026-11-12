<?php

declare(strict_types=1);

namespace Tests\Application\Vending;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Mother\VendingMachineMother;
use Vending\Application\StartMachine\StartMachine;
use Vending\Application\StartMachine\StartMachineHandler;
use Vending\Infrastructure\InMemoryVendingMachineRepository;

final class StartMachineHandlerTest extends TestCase
{
    #[Test]
    public function saves_a_new_machine_with_the_given_id(): void
    {
        $machines = new InMemoryVendingMachineRepository();
        $machineStarter = new StartMachineHandler($machines);

        $machineStarter(new StartMachine(VendingMachineMother::id()));

        self::assertEquals(VendingMachineMother::started(), $machines->get(VendingMachineMother::id()));
    }
}
