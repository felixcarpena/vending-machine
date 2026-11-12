<?php

declare(strict_types=1);

namespace Tests\Application\Vending;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Double\RecordingEventBus;
use Tests\Mother\VendingMachineMother;
use Vending\Application\ReturnMoney\ReturnMoney;
use Vending\Application\ReturnMoney\ReturnMoneyHandler;
use Vending\Domain\Coin;
use Vending\Domain\Coins;
use Vending\Domain\Event\CoinsReturned;
use Vending\Domain\Money;
use Vending\Infrastructure\InMemoryVendingMachineRepository;

final class ReturnMoneyHandlerTest extends TestCase
{
    private InMemoryVendingMachineRepository $machines;
    private RecordingEventBus $eventBus;
    private ReturnMoneyHandler $moneyReturner;

    protected function setUp(): void
    {
        $this->machines = new InMemoryVendingMachineRepository(
            VendingMachineMother::withCoinsInserted(Coin::TenCents, Coin::TenCents),
        );
        $this->eventBus = new RecordingEventBus();
        $this->moneyReturner = new ReturnMoneyHandler($this->machines, $this->eventBus);
    }

    #[Test]
    public function leaves_no_money_inserted_in_the_machine(): void
    {
        ($this->moneyReturner)(new ReturnMoney(VendingMachineMother::id()));

        self::assertEquals(Money::zero(), $this->machines->get(VendingMachineMother::id())->insertedMoney());
    }

    #[Test]
    public function publishes_the_coins_returned(): void
    {
        ($this->moneyReturner)(new ReturnMoney(VendingMachineMother::id()));

        self::assertEquals(
            [new CoinsReturned(VendingMachineMother::id(), Coins::of(Coin::TenCents, Coin::TenCents))],
            $this->eventBus->published,
        );
    }
}
