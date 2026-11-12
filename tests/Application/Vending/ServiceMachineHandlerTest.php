<?php

declare(strict_types=1);

namespace Tests\Application\Vending;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Double\RecordingEventBus;
use Tests\Mother\VendingMachineMother;
use Vending\Application\ServiceMachine\ServiceMachine;
use Vending\Application\ServiceMachine\ServiceMachineHandler;
use Vending\Domain\Coin;
use Vending\Domain\Coins;
use Vending\Domain\Event\MachineServiced;
use Vending\Domain\StockLevels;
use Vending\Infrastructure\InMemoryVendingMachineRepository;

final class ServiceMachineHandlerTest extends TestCase
{
    private InMemoryVendingMachineRepository $machines;
    private RecordingEventBus $eventBus;
    private ServiceMachineHandler $machineServicer;

    protected function setUp(): void
    {
        $this->machines = new InMemoryVendingMachineRepository(VendingMachineMother::started());
        $this->eventBus = new RecordingEventBus();
        $this->machineServicer = new ServiceMachineHandler($this->machines, $this->eventBus);
    }

    #[Test]
    public function saves_the_serviced_machine(): void
    {
        $servicing = new ServiceMachine(
            VendingMachineMother::id(),
            StockLevels::of(['WATER' => 1]),
            Coins::of(Coin::TenCents),
        );

        ($this->machineServicer)($servicing);

        $expected = VendingMachineMother::started();
        $expected->service(StockLevels::of(['WATER' => 1]), Coins::of(Coin::TenCents));
        $expected->pullEvents();
        self::assertEquals($expected, $this->machines->get(VendingMachineMother::id()));
    }

    #[Test]
    public function publishes_that_the_machine_was_serviced(): void
    {
        ($this->machineServicer)(
            new ServiceMachine(VendingMachineMother::id(), StockLevels::of(['WATER' => 1]), Coins::of()),
        );

        self::assertEquals([new MachineServiced(VendingMachineMother::id())], $this->eventBus->published);
    }
}
