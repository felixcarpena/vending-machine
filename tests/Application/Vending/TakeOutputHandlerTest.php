<?php

declare(strict_types=1);

namespace Tests\Application\Vending;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Double\RecordingEventBus;
use Tests\Mother\VendingMachineMother;
use Vending\Application\TakeOutput\TakeOutput;
use Vending\Application\TakeOutput\TakeOutputHandler;
use Vending\Domain\Coin;
use Vending\Domain\Event\OutputTaken;
use Vending\Domain\VendingMachine;
use Vending\Infrastructure\InMemoryVendingMachineRepository;

final class TakeOutputHandlerTest extends TestCase
{
    private InMemoryVendingMachineRepository $machines;
    private RecordingEventBus $eventBus;
    private TakeOutputHandler $outputTaker;

    protected function setUp(): void
    {
        $this->machines = new InMemoryVendingMachineRepository(self::withCoinsInTheTray());
        $this->eventBus = new RecordingEventBus();
        $this->outputTaker = new TakeOutputHandler($this->machines, $this->eventBus);
    }

    #[Test]
    public function leaves_the_output_tray_empty(): void
    {
        ($this->outputTaker)(new TakeOutput(VendingMachineMother::id()));

        self::assertTrue($this->machines->get(VendingMachineMother::id())->outputTray->isEmpty());
    }

    #[Test]
    public function publishes_that_the_output_was_taken(): void
    {
        ($this->outputTaker)(new TakeOutput(VendingMachineMother::id()));

        self::assertEquals([new OutputTaken(VendingMachineMother::id())], $this->eventBus->published);
    }

    private static function withCoinsInTheTray(): VendingMachine
    {
        $machine = VendingMachineMother::withCoinsInserted(Coin::TenCents);
        $machine->returnMoney();
        $machine->pullEvents();

        return $machine;
    }
}
