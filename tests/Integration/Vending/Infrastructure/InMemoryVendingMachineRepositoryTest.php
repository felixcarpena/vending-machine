<?php

declare(strict_types=1);

namespace Tests\Integration\Vending\Infrastructure;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Mother\VendingMachineMother;
use Vending\Domain\Exception\VendingMachineNotFound;
use Vending\Domain\VendingMachine;
use Vending\Domain\VendingMachineId;
use Vending\Infrastructure\InMemoryVendingMachineRepository;

final class InMemoryVendingMachineRepositoryTest extends TestCase
{
    #[Test]
    public function finds_a_machine_by_its_id(): void
    {
        $machine = VendingMachineMother::started();
        $machines = new InMemoryVendingMachineRepository(
            new VendingMachine(VendingMachineId::of('0193b4e2-9f00-7a00-9b00-000000000002')),
            $machine,
        );

        self::assertEquals($machine, $machines->get(VendingMachineMother::id()));
    }

    #[Test]
    public function finds_a_machine_once_it_is_saved(): void
    {
        $machines = new InMemoryVendingMachineRepository();

        $machines->save(VendingMachineMother::started());

        self::assertEquals(VendingMachineMother::started(), $machines->get(VendingMachineMother::id()));
    }

    #[Test]
    public function fails_for_a_machine_it_does_not_hold(): void
    {
        $machines = new InMemoryVendingMachineRepository();

        $this->expectException(VendingMachineNotFound::class);

        $machines->get(VendingMachineMother::id());
    }
}
