<?php

declare(strict_types=1);

namespace Tests\Integration\Vending\Infrastructure;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Mother\VendingMachineMother;
use Vending\Domain\Coin;
use Vending\Domain\Exception\VendingMachineNotFound;
use Vending\Domain\Money;
use Vending\Domain\VendingMachineId;
use Vending\Infrastructure\InMemoryVendingMachineRepository;

final class InMemoryVendingMachineRepositoryTest extends TestCase
{
    #[Test]
    public function finds_a_machine_by_its_id(): void
    {
        $machine = VendingMachineMother::started();
        $machines = new InMemoryVendingMachineRepository(
            VendingMachineMother::startedAs(VendingMachineId::of('0193b4e2-9f00-7a00-9b00-000000000002')),
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
    public function keeps_a_change_only_once_the_machine_is_saved(): void
    {
        $machines = new InMemoryVendingMachineRepository(VendingMachineMother::started());

        $machines->get(VendingMachineMother::id())->insertCoin(Coin::TenCents);

        self::assertEquals(Money::zero(), $machines->get(VendingMachineMother::id())->insertedMoney());
    }

    #[Test]
    public function keeps_what_was_saved_when_the_saved_machine_changes_afterwards(): void
    {
        $machines = new InMemoryVendingMachineRepository();
        $machine = VendingMachineMother::started();
        $machines->save($machine);

        $machine->insertCoin(Coin::TenCents);

        self::assertEquals(Money::zero(), $machines->get(VendingMachineMother::id())->insertedMoney());
    }

    #[Test]
    public function fails_for_a_machine_it_does_not_hold(): void
    {
        $machines = new InMemoryVendingMachineRepository();

        $this->expectException(VendingMachineNotFound::class);

        $machines->get(VendingMachineMother::id());
    }
}
